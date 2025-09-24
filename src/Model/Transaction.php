<?php

declare(strict_types=1);

namespace ChipAssessment\Model;

use ChipAssessment\ValueObject\Money;
use ChipAssessment\ValueObject\UserId;
use DateTimeImmutable;

final class Transaction
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_INTEREST = 'interest';

    private string $id;
    private UserId $userId;
    private Money $amount;
    private string $type;
    private DateTimeImmutable $createdAt;
    private string $description;

    public function __construct(
        string $id,
        UserId $userId,
        Money $amount,
        string $type,
        DateTimeImmutable $createdAt,
        string $description = ''
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->amount = $amount;
        $this->type = $type;
        $this->createdAt = $createdAt;
        $this->description = $description;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isDeposit(): bool
    {
        return $this->type === self::TYPE_DEPOSIT;
    }

    public function isInterest(): bool
    {
        return $this->type === self::TYPE_INTEREST;
    }
}