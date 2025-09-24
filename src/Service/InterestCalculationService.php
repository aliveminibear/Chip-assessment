<?php

declare(strict_types=1);

namespace ChipAssessment\Service;

use ChipAssessment\Model\InterestAccount;
use ChipAssessment\Model\Transaction;
use ChipAssessment\Repository\TransactionRepositoryInterface;
use ChipAssessment\ValueObject\Money;
use DateTimeImmutable;

final class InterestCalculationService
{
    private const MINIMUM_PAYOUT_PENNIES = 1;
    
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function calculateAndApplyInterest(InterestAccount $account, DateTimeImmutable $currentDate): ?Transaction
    {
        if (!$account->shouldCalculateInterest($currentDate)) {
            return null;
        }

        $interestAmount = $this->calculateInterest($account);
        $totalInterest = $account->getAccumulatedInterest()->add($interestAmount);

        if ($totalInterest->getAmountInPennies() >= self::MINIMUM_PAYOUT_PENNIES) {

            $payoutAmount = $account->payoutAccumulatedInterest();
            $account->addInterest($interestAmount);
            $account->updateLastInterestCalculation($currentDate);

            $transaction = new Transaction(
                $this->generateTransactionId(),
                $account->getUserId(),
                $totalInterest,
                Transaction::TYPE_INTEREST,
                $currentDate,
                'Interest payment'
            );

            $this->transactionRepository->save($transaction);
            return $transaction;
        } else {
                
            $account->accumulateInterest($interestAmount);
            $account->updateLastInterestCalculation($currentDate);
            return null;
        }
    }

    private function calculateInterest(InterestAccount $account): Money
    {
        $balance = $account->getBalance();
        $threeDayRate = $account->getInterestRate()->getThreeDayRate() / 100; // Convert percentage to decimal
        
        return $balance->multiply($threeDayRate);
    }

    private function generateTransactionId(): string
    {
        return uniqid('txn_', true);
    }
}