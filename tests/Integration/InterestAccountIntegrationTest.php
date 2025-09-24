<?php

declare(strict_types=1);

namespace ChipAssessment\Tests\Integration;

use ChipAssessment\Http\StatsApiClientInterface;
use ChipAssessment\InterestAccountFactory;
use ChipAssessment\Model\Transaction;
use ChipAssessment\ValueObject\Money;
use ChipAssessment\ValueObject\UserId;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

final class InterestAccountIntegrationTest extends TestCase
{
    private StatsApiClientInterface $statsApiClient;

    protected function setUp(): void
    {
        $this->statsApiClient = Mockery::mock(StatsApiClientInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCompleteInterestAccountWorkflow(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(600000); 

        $service = InterestAccountFactory::create($this->statsApiClient);

        $account = $service->openAccount($userId);
        $this->assertEquals(1.02, $account->getInterestRate()->getAnnualRate());
        $this->assertEquals(0, $account->getBalance()->getAmountInPennies());

        $deposit1 = $service->deposit($userId, new Money(100000)); // £1000
        $deposit2 = $service->deposit($userId, new Money(50000));  // £500

        $this->assertEquals(Transaction::TYPE_DEPOSIT, $deposit1->getType());
        $this->assertEquals(Transaction::TYPE_DEPOSIT, $deposit2->getType());

        $updatedAccount = $service->getAccount($userId);
        $this->assertEquals(150000, $updatedAccount->getBalance()->getAmountInPennies()); // £1500

        $futureDate = new DateTimeImmutable('+3 days');
        $interestTransaction = $service->calculateInterest($userId, $futureDate);

        $this->assertNotNull($interestTransaction);
        $this->assertEquals(Transaction::TYPE_INTEREST, $interestTransaction->getType());
        
        $expectedInterest = 13;
        $this->assertEquals($expectedInterest, $interestTransaction->getAmount()->getAmountInPennies());

        $finalAccount = $service->getAccount($userId);
        $this->assertEquals(150000 + $expectedInterest, $finalAccount->getBalance()->getAmountInPennies());

        $statement = $service->getAccountStatement($userId);
        $this->assertCount(3, $statement); 

        $this->assertEquals(Transaction::TYPE_INTEREST, $statement[0]->getType());
        $this->assertEquals(Transaction::TYPE_DEPOSIT, $statement[1]->getType());
        $this->assertEquals(Transaction::TYPE_DEPOSIT, $statement[2]->getType());
    }

    public function testInterestAccumulationAndPayout(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(300000); 

        $service = InterestAccountFactory::create($this->statsApiClient);

        $service->openAccount($userId);
        $service->deposit($userId, new Money(1000)); 

        $date1 = new DateTimeImmutable('+3 days');
        $interest1 = $service->calculateInterest($userId, $date1);
        $this->assertNull($interest1); 

        $date2 = new DateTimeImmutable('+6 days');
        $interest2 = $service->calculateInterest($userId, $date2);
        $this->assertNull($interest2);

        $service->deposit($userId, new Money(500000)); 

        $date3 = new DateTimeImmutable('+9 days');
        $interest3 = $service->calculateInterest($userId, $date3);
        
        $this->assertNotNull($interest3);
        $this->assertGreaterThanOrEqual(1, $interest3->getAmount()->getAmountInPennies());
    }

    public function testMultipleAccountsInterestCalculation(): void
    {
        $userId1 = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $userId2 = new UserId('12345678-1234-4123-8123-123456789012');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId1)
            ->once()
            ->andReturn(600000);
            
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId2)
            ->once()
            ->andReturn(400000);

        $service = InterestAccountFactory::create($this->statsApiClient);

        $service->openAccount($userId1);
        $service->openAccount($userId2);

        $service->deposit($userId1, new Money(200000)); 
        $service->deposit($userId2, new Money(300000)); 

        $futureDate = new DateTimeImmutable('+3 days');
        $interestTransactions = $service->calculateInterestForAllAccounts($futureDate);

        $this->assertCount(2, $interestTransactions);
        
        foreach ($interestTransactions as $transaction) {
            $this->assertEquals(Transaction::TYPE_INTEREST, $transaction->getType());
            $this->assertGreaterThan(0, $transaction->getAmount()->getAmountInPennies());
        }
    }
}