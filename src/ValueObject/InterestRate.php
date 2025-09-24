<?php

declare(strict_types=1);

namespace ChipAssessment\ValueObject;

final class InterestRate
{
    private float $annualRate;

    public function __construct(float $annualRate)
    {
        $this->annualRate = $annualRate;
    }

    public function getAnnualRate(): float
    {
        return $this->annualRate;
    }

    public function getDailyRate(): float
    {
        return $this->annualRate / 365;
    }

    public function getThreeDayRate(): float
    {
        return $this->getDailyRate() * 3;
    }

    public static function fromIncome(?int $monthlyIncomeInPennies): self
    {
        if ($monthlyIncomeInPennies === null) {
            return new self(0.5); 
        }

        $monthlyIncomeInPounds = $monthlyIncomeInPennies / 100;

        if ($monthlyIncomeInPounds < 5000) {
            return new self(0.93); 
        }

        return new self(1.02); 
    }

    public function equals(InterestRate $other): bool
    {
        return abs($this->annualRate - $other->annualRate) < 0.001;
    }
}