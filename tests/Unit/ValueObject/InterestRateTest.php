<?php

declare(strict_types=1);

namespace ChipAssessment\Tests\Unit\ValueObject;

use ChipAssessment\ValueObject\InterestRate;
use PHPUnit\Framework\TestCase;

final class InterestRateTest extends TestCase
{
    public function testCreateInterestRate(): void
    {
        $rate = new InterestRate(1.5);
        
        $this->assertEquals(1.5, $rate->getAnnualRate());
    }

    public function testDailyRate(): void
    {
        $rate = new InterestRate(3.65);
        
        $this->assertEquals(0.01, $rate->getDailyRate());
    }

    public function testThreeDayRate(): void
    {
        $rate = new InterestRate(3.65);
        
        $this->assertEquals(0.03, $rate->getThreeDayRate());
    }

    public function testFromIncomeWithNullIncome(): void
    {
        $rate = InterestRate::fromIncome(null);
        
        $this->assertEquals(0.5, $rate->getAnnualRate());
    }

    public function testFromIncomeWithLowIncome(): void
    {
        $rate = InterestRate::fromIncome(400000); 
        
        $this->assertEquals(0.93, $rate->getAnnualRate());
    }

    public function testFromIncomeWithHighIncome(): void
    {
        $rate = InterestRate::fromIncome(600000);
        
        $this->assertEquals(1.02, $rate->getAnnualRate());
    }

    public function testFromIncomeWithExactBoundary(): void
    {
        $rate = InterestRate::fromIncome(500000); 
        
        $this->assertEquals(1.02, $rate->getAnnualRate());
    }

    public function testEquals(): void
    {
        $rate1 = new InterestRate(1.5);
        $rate2 = new InterestRate(1.5);
        $rate3 = new InterestRate(2.0);
        
        $this->assertTrue($rate1->equals($rate2));
        $this->assertFalse($rate1->equals($rate3));
    }
}