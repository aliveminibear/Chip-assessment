<?php

declare(strict_types=1);

namespace ChipAssessment\Tests\Unit\ValueObject;

use ChipAssessment\Exception\InvalidAmountException;
use ChipAssessment\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testCreateFromPennies(): void
    {
        $money = new Money(1500);
        
        $this->assertEquals(1500, $money->getAmountInPennies());
        $this->assertEquals(15.0, $money->getAmountInPounds());
    }

    public function testCreateFromPounds(): void
    {
        $money = Money::fromPounds(15.50);
        
        $this->assertEquals(1550, $money->getAmountInPennies());
        $this->assertEquals(15.50, $money->getAmountInPounds());
    }

    public function testNegativeAmountThrowsException(): void
    {
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Amount cannot be negative: -100');
        
        new Money(-100);
    }

    public function testAddition(): void
    {
        $money1 = new Money(1000);
        $money2 = new Money(500);
        
        $result = $money1->add($money2);
        
        $this->assertEquals(1500, $result->getAmountInPennies());
    }

    public function testSubtraction(): void
    {
        $money1 = new Money(1000);
        $money2 = new Money(300);
        
        $result = $money1->subtract($money2);
        
        $this->assertEquals(700, $result->getAmountInPennies());
    }

    public function testSubtractionWithInsufficientFundsThrowsException(): void
    {
        $money1 = new Money(100);
        $money2 = new Money(200);
        
        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Insufficient funds');
        
        $money1->subtract($money2);
    }

    public function testMultiplication(): void
    {
        $money = new Money(1000);
        
        $result = $money->multiply(1.5);
        
        $this->assertEquals(1500, $result->getAmountInPennies());
    }

    public function testComparisons(): void
    {
        $money1 = new Money(1000);
        $money2 = new Money(500);
        $money3 = new Money(1000);
        
        $this->assertTrue($money1->isGreaterThanOrEqual($money2));
        $this->assertTrue($money1->isGreaterThanOrEqual($money3));
        $this->assertFalse($money2->isGreaterThanOrEqual($money1));
        
        $this->assertTrue($money2->isLessThan($money1));
        $this->assertFalse($money1->isLessThan($money2));
        $this->assertFalse($money1->isLessThan($money3));
        
        $this->assertTrue($money1->equals($money3));
        $this->assertFalse($money1->equals($money2));
    }

    public function testToString(): void
    {
        $money = new Money(1550);
        
        $this->assertEquals('15.50', (string) $money);
    }
}