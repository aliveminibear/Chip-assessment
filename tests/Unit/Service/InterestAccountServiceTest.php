<?php

declare(strict_types=1);

namespace ChipAssessment\Tests\Unit\Service;

use ChipAssessment\Exception\AccountAlreadyExistsException;
use ChipAssessment\Exception\AccountNotFoundException;
use ChipAssessment\Http\StatsApiClientInterface;
use ChipAssessment\Model\InterestAccount;
use ChipAssessment\Model\Transaction;
use ChipAssessment\Repository\InMemoryInterestAccountRepository;
use ChipAssessment\Repository\InMemoryTransactionRepository;
use ChipAssessment\Service\InterestAccountService;
use ChipAssessment\Service\InterestCalculationService;
use ChipAssessment\ValueObject\Money;
use ChipAssessment\ValueObject\UserId;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

final class InterestAccountServiceTest extends TestCase
{
    private InterestAccountService $service;
    private InMemoryInterestAccountRepository $accountRepository;
    private InMemoryTransactionRepository $transactionRepository;
    private StatsApiClientInterface $statsApiClient;

    protected function setUp(): void
    {
        $this->accountRepository = new InMemoryInterestAccountRepository();
        $this->transactionRepository = new InMemoryTransactionRepository();
        $this->statsApiClient = Mockery::mock(StatsApiClientInterface::class);
        
        $interestCalculationService = new InterestCalculationService($this->transactionRepository);
        
        $this->service = new InterestAccountService(
            $this->accountRepository,
            $this->transactionRepository,
            $this->statsApiClient,
            $interestCalculationService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testOpenAccountWithHighIncome(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(600000);

        $account = $this->service->openAccount($userId);

        $this->assertTrue($account->getUserId()->equals($userId));
        $this->assertEquals(1.02, $account->getInterestRate()->getAnnualRate());
        $this->assertEquals(0, $account->getBalance()->getAmountInPennies());
    }

    public function testOpenAccountWithLowIncome(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(400000); 

        $account = $this->service->openAccount($userId);

        $this->assertEquals(0.93, $account->getInterestRate()->getAnnualRate());
    }

    public function testOpenAccountWithUnknownIncome(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(null);

        $account = $this->service->openAccount($userId);

        $this->assertEquals(0.5, $account->getInterestRate()->getAnnualRate());
    }

    public function testOpenAccountThrowsExceptionWhenAccountAlreadyExists(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        
        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(500000);

        $this->service->openAccount($userId);

        $this->expectException(AccountAlreadyExistsException::class);
        $this->expectExceptionMessage('User 88224979-406e-4e32-9458-55836e4e1f95 already has an active interest account');

        $this->service->openAccount($userId);
    }

    public function testDeposit(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $amount = new Money(10000);

        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(500000);

        $this->service->openAccount($userId);
        $transaction = $this->service->deposit($userId, $amount);

        $this->assertEquals(Transaction::TYPE_DEPOSIT, $transaction->getType());
        $this->assertEquals(10000, $transaction->getAmount()->getAmountInPennies());
        $this->assertTrue($transaction->getUserId()->equals($userId));

        $account = $this->service->getAccount($userId);
        $this->assertEquals(10000, $account->getBalance()->getAmountInPennies());
    }

    public function testDepositThrowsExceptionWhenAccountNotFound(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $amount = new Money(10000);

        $this->expectException(AccountNotFoundException::class);
        $this->expectExceptionMessage('No interest account found for user 88224979-406e-4e32-9458-55836e4e1f95');

        $this->service->deposit($userId, $amount);
    }

    public function testGetAccountStatement(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');

        $this->statsApiClient
            ->shouldReceive('getUserIncome')
            ->with($userId)
            ->once()
            ->andReturn(500000);

        $this->service->openAccount($userId);
        $this->service->deposit($userId, new Money(10000));
        $this->service->deposit($userId, new Money(5000));

        $statement = $this->service->getAccountStatement($userId);

        $this->assertCount(2, $statement);
        $this->assertEquals(Transaction::TYPE_DEPOSIT, $statement[0]->getType());
        $this->assertEquals(Transaction::TYPE_DEPOSIT, $statement[1]->getType());
        
        $this->assertGreaterThanOrEqual(
            $statement[1]->getCreatedAt(),
            $statement[0]->getCreatedAt()
        );
    }

    public function testGetAccountStatementThrowsExceptionWhenAccountNotFound(): void
    {
        $userId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');

        $this->expectException(AccountNotFoundException::class);
        $this->expectExceptionMessage('No interest account found for user 88224979-406e-4e32-9458-55836e4e1f95');

        $this->service->getAccountStatement($userId);
    }
}