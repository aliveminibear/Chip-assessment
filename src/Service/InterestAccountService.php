<?php

declare(strict_types=1);

namespace ChipAssessment\Service;

use ChipAssessment\Exception\AccountAlreadyExistsException;
use ChipAssessment\Exception\AccountNotFoundException;
use ChipAssessment\Http\StatsApiClientInterface;
use ChipAssessment\Model\InterestAccount;
use ChipAssessment\Model\Transaction;
use ChipAssessment\Repository\InterestAccountRepositoryInterface;
use ChipAssessment\Repository\TransactionRepositoryInterface;
use ChipAssessment\ValueObject\InterestRate;
use ChipAssessment\ValueObject\Money;
use ChipAssessment\ValueObject\UserId;
use DateTimeImmutable;

final class InterestAccountService
{
    private InterestAccountRepositoryInterface $accountRepository;
    private TransactionRepositoryInterface $transactionRepository;
    private StatsApiClientInterface $statsApiClient;
    private InterestCalculationService $interestCalculationService;

    public function __construct(
        InterestAccountRepositoryInterface $accountRepository,
        TransactionRepositoryInterface $transactionRepository,
        StatsApiClientInterface $statsApiClient,
        InterestCalculationService $interestCalculationService
    ) {
        $this->accountRepository = $accountRepository;
        $this->transactionRepository = $transactionRepository;
        $this->statsApiClient = $statsApiClient;
        $this->interestCalculationService = $interestCalculationService;
    }

    public function openAccount(UserId $userId): InterestAccount
    {
        if ($this->accountRepository->existsByUserId($userId)) {
            throw new AccountAlreadyExistsException(
                "User {$userId->getValue()} already has an active interest account"
            );
        }

        $userIncome = $this->statsApiClient->getUserIncome($userId);
        $interestRate = InterestRate::fromIncome($userIncome);
        
        $account = new InterestAccount(
            $userId,
            $interestRate,
            new DateTimeImmutable()
        );

        $this->accountRepository->save($account);

        return $account;
    }

    public function deposit(UserId $userId, Money $amount): Transaction
    {
        $account = $this->getAccountByUserId($userId);
        
        $account->deposit($amount);
        $this->accountRepository->save($account);

        $transaction = new Transaction(
            $this->generateTransactionId(),
            $userId,
            $amount,
            Transaction::TYPE_DEPOSIT,
            new DateTimeImmutable(),
            'Deposit'
        );

        $this->transactionRepository->save($transaction);

        return $transaction;
    }

    public function calculateInterest(UserId $userId, ?DateTimeImmutable $currentDate = null): ?Transaction
    {
        $account = $this->getAccountByUserId($userId);
        $currentDate = $currentDate ?? new DateTimeImmutable();

        $interestTransaction = $this->interestCalculationService->calculateAndApplyInterest($account, $currentDate);
        
        if ($interestTransaction !== null) {
            $this->accountRepository->save($account);
        }

        return $interestTransaction;
    }

    public function calculateInterestForAllAccounts(?DateTimeImmutable $currentDate = null): array
    {
        $currentDate = $currentDate ?? new DateTimeImmutable();
        $interestTransactions = [];

        foreach ($this->accountRepository->findAll() as $account) {
            $interestTransaction = $this->interestCalculationService->calculateAndApplyInterest($account, $currentDate);
            
            if ($interestTransaction !== null) {
                $this->accountRepository->save($account);
                $interestTransactions[] = $interestTransaction;
            }
        }

        return $interestTransactions;
    }

    public function getAccountStatement(UserId $userId): array
    {
        $this->getAccountByUserId($userId); 
        
        $transactions = $this->transactionRepository->findByUserId($userId);
        
            
        usort($transactions, function (Transaction $a, Transaction $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        return $transactions;
    }

    public function getAccount(UserId $userId): InterestAccount
    {
        return $this->getAccountByUserId($userId);
    }

    private function getAccountByUserId(UserId $userId): InterestAccount
    {
        $account = $this->accountRepository->findByUserId($userId);
        
        if ($account === null) {
            throw new AccountNotFoundException(
                "No interest account found for user {$userId->getValue()}"
            );
        }

        return $account;
    }

    private function generateTransactionId(): string
    {
        return uniqid('txn_', true);
    }
}