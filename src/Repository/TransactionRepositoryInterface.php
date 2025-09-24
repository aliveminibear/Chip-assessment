<?php

declare(strict_types=1);

namespace ChipAssessment\Repository;    

use ChipAssessment\Model\Transaction;
use ChipAssessment\ValueObject\UserId;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;
    
    public function findByUserId(UserId $userId): array;
    
    public function findAll(): array;
}   