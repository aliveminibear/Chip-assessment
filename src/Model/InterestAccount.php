<?php

declare(strict_types=1);

namespace ChipAssessment\Model;

use ChipAssessment\ValueObject\InterestRate;
use ChipAssessment\ValueObject\Money;
use ChipAssessment\ValueObject\UserId;
use DateTimeImmutable;

final class InterestAccount
{
    private UserId $userId;
    private Money $balance;
    private InterestRate $interestRate;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $lastInterestCalculation;
    private Money $accumulatedInterest;

    public function __construct(
        UserId $userId,
        InterestRate $interestRate,
        DateTimeImmutable $createdAt
    ) {
        $this->userId = $userId;
        $this->balance = new Money(0);
        $this->interestRate = $interestRate;
        $this->createdAt = $createdAt;
        $this->lastInterestCalculation = $createdAt;
        $this->accumulatedInterest = new Money(0);
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function getInterestRate(): InterestRate
    {
        return $this->interestRate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastInterestCalculation(): DateTimeImmutable
    {
        return $this->lastInterestCalculation;
    }

    public function getAccumulatedInterest(): Money
    {
        return $this->accumulatedInterest;
    }

    public function deposit(Money $amount): void
    {
        $this->balance = $this->balance->add($amount);
    }

    public function addInterest(Money $interest): void
    {
        $this->balance = $this->balance->add($interest);
    }

    public function accumulateInterest(Money $interest): void
    {
        $this->accumulatedInterest = $this->accumulatedInterest->add($interest);
    }

    public function payoutAccumulatedInterest(): Money
    {
        $payout = $this->accumulatedInterest;
        $this->accumulatedInterest = new Money(0);
        $this->balance = $this->balance->add($payout);
        return $payout;
    }

    public function updateLastInterestCalculation(DateTimeImmutable $date): void
    {
        $this->lastInterestCalculation = $date;
    }

    public function shouldCalculateInterest(DateTimeImmutable $currentDate): bool
    {
        $daysSinceLastCalculation = $currentDate->diff($this->lastInterestCalculation)->days;
        return $daysSinceLastCalculation >= 3;
    }
}