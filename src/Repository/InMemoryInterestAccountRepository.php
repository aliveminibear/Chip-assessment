<?php

declare(strict_types=1);

namespace ChipAssessment\Repository;

use ChipAssessment\Model\InterestAccount;
use ChipAssessment\ValueObject\UserId;

final class InMemoryInterestAccountRepository implements InterestAccountRepositoryInterface
{
    private array $accounts = [];

    public function save(InterestAccount $account): void
    {
        $this->accounts[$account->getUserId()->getValue()] = $account;
    }

    public function findByUserId(UserId $userId): ?InterestAccount
    {
        return $this->accounts[$userId->getValue()] ?? null;
    }

    public function existsByUserId(UserId $userId): bool
    {
        return isset($this->accounts[$userId->getValue()]);
    }

    public function findAll(): array
    {
        return array_values($this->accounts);
    }
}