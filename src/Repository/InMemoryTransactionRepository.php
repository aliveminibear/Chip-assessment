<?php

declare(strict_types=1);

namespace ChipAssessment\Repository;

use ChipAssessment\Model\Transaction;
use ChipAssessment\ValueObject\UserId;

final class InMemoryTransactionRepository implements TransactionRepositoryInterface
{
    private array $transactions = [];

    public function save(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    public function findByUserId(UserId $userId): array
    {
        return array_filter(
            $this->transactions,
            fn(Transaction $transaction) => $transaction->getUserId()->equals($userId)
        );
    }

    public function findAll(): array
    {
        return $this->transactions;
    }
}