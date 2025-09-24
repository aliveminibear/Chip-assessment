<?php

declare(strict_types=1);

namespace ChipAssessment\Tests\Unit\Service;

use ChipAssessment\Model\InterestAccount;
use ChipAssessment\Model\Transaction;
use ChipAssessment\Repository\InMemoryTransactionRepository;
use ChipAssessment\Service\InterestCalculationService;
use ChipAssessment\ValueObject\InterestRate;
use ChipAssessment\ValueObject\Money;
use ChipAssessment\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class InterestCalculationServiceTest extends TestCase
{
    private InterestCalculationService $service;
    private InMemoryTransactionRepository $transactionRepository;

    protected function setUp(): void
    {
        $this->transactionRepository = new InMemoryTransactionRepository();
        $this->service = new InterestCalculationService($this->transactionRepository);
    }

    public function testCalculateInterestWhenNotDue(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $interestRate = new InterestRate(1.02);
        $createdAt = new DateTimeImmutable('2023-01-01');
        
        $account = new InterestAccount($userId, $interestRate, $createdAt);
        $account->deposit(new Money(100000)); 

        $currentDate = new DateTimeImmutable('2023-01-03');
        
        $transaction = $this->service->calculateAndApplyInterest($account, $currentDate);

        $this->assertNull($transaction);
        $this->assertEquals(100000, $account->getBalance()->getAmountInPennies());
    }

    public function testCalculateInterestWhenDueAndAboveThreshold(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $interestRate = new InterestRate(1.02);
        $createdAt = new DateTimeImmutable('2023-01-01');
        
        $account = new InterestAccount($userId, $interestRate, $createdAt);
        $account->deposit(new Money(1000000)); 

        $currentDate = new DateTimeImmutable('2023-01-04');
        
        $transaction = $this->service->calculateAndApplyInterest($account, $currentDate);

        $this->assertNotNull($transaction);
        $this->assertEquals(Transaction::TYPE_INTEREST, $transaction->getType());
        $this->assertTrue($transaction->getUserId()->equals($userId));
        
        $expectedInterest = 84;
        $this->assertEquals($expectedInterest, $transaction->getAmount()->getAmountInPennies());
        
        $this->assertEquals(1000000 + $expectedInterest, $account->getBalance()->getAmountInPennies());
    }

    public function testCalculateInterestWhenDueButBelowThreshold(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $interestRate = new InterestRate(0.5);
        $createdAt = new DateTimeImmutable('2023-01-01');
        
        $account = new InterestAccount($userId, $interestRate, $createdAt);
        $account->deposit(new Money(100)); // £1

        $currentDate = new DateTimeImmutable('2023-01-04');
        
        $transaction = $this->service->calculateAndApplyInterest($account, $currentDate);

        $this->assertNull($transaction);
        
        $this->assertEquals(0, $account->getAccumulatedInterest()->getAmountInPennies());
        $this->assertEquals(100, $account->getBalance()->getAmountInPennies());
    }

    public function testAccumulatedInterestEventuallyPaidOut(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $interestRate = new InterestRate(0.5);
        $createdAt = new DateTimeImmutable('2023-01-01');
        
        $account = new InterestAccount($userId, $interestRate, $createdAt);
        $account->deposit(new Money(50000)); 

        $currentDate1 = new DateTimeImmutable('2023-01-04');
        $transaction1 = $this->service->calculateAndApplyInterest($account, $currentDate1);
        
        $this->assertNotNull($transaction1);
        $this->assertEquals(2, $transaction1->getAmount()->getAmountInPennies());
        
        $currentDate2 = new DateTimeImmutable('2023-01-07');
        $transaction2 = $this->service->calculateAndApplyInterest($account, $currentDate2);
        
        $this->assertNotNull($transaction2);
        $this->assertEquals(2, $transaction2->getAmount()->getAmountInPennies());
        
        $account->deposit(new Money(450000)); 
        
        $currentDate3 = new DateTimeImmutable('2023-01-10');
        $transaction3 = $this->service->calculateAndApplyInterest($account, $currentDate3);
        
        $this->assertNotNull($transaction3);
        $this->assertEquals(21, $transaction3->getAmount()->getAmountInPennies());
    }
}