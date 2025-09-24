<?php

declare(strict_types=1);

namespace ChipAssessment\ValueObject;

use ChipAssessment\Exception\InvalidAmountException;

final class Money
{
    private int $amountInPennies;

    public function __construct(int $amountInPennies)
    {
        if ($amountInPennies < 0) {
            throw new InvalidAmountException("Amount cannot be negative: {$amountInPennies}");
        }
        
        $this->amountInPennies = $amountInPennies;
    }

    public static function fromPounds(float $pounds): self
    {
        return new self((int) round($pounds * 100));
    }

    public function getAmountInPennies(): int
    {
        return $this->amountInPennies;
    }

    public function getAmountInPounds(): float
    {
        return $this->amountInPennies / 100;
    }

    public function add(Money $other): Money
    {
        return new Money($this->amountInPennies + $other->amountInPennies);
    }

    public function subtract(Money $other): Money
    {
        $result = $this->amountInPennies - $other->amountInPennies;
        if ($result < 0) {
            throw new InvalidAmountException("Insufficient funds");
        }
        return new Money($result);
    }

    public function multiply(float $multiplier): Money
    {
        return new Money((int) round($this->amountInPennies * $multiplier));
    }

    public function isGreaterThanOrEqual(Money $other): bool
    {
        return $this->amountInPennies >= $other->amountInPennies;
    }

    public function isLessThan(Money $other): bool
    {
        return $this->amountInPennies < $other->amountInPennies;
    }

    public function equals(Money $other): bool
    {
        return $this->amountInPennies === $other->amountInPennies;
    }

    public function __toString(): string
    {
        return number_format($this->getAmountInPounds(), 2);
    }
}