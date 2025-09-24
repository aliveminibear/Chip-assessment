<?php

declare(strict_types=1);

namespace ChipAssessment\Repository;

use ChipAssessment\Model\InterestAccount;
use ChipAssessment\ValueObject\UserId;

interface InterestAccountRepositoryInterface
{
    public function save(InterestAccount $account): void;
    
    public function findByUserId(UserId $userId): ?InterestAccount;
    
    public function existsByUserId(UserId $userId): bool;
    
    public function findAll(): array;
}