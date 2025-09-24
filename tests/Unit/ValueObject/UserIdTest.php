<?php

declare(strict_types=1);

namespace ChipAssessment\Tests\Unit\ValueObject;

use ChipAssessment\Exception\InvalidUserIdException;
use ChipAssessment\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function testValidUuidIsAccepted(): void
    {
        $validUuid = '88224979-406e-4e32-9458-55836e4e1f95';
        $userId = new UserId($validUuid);
        
        $this->assertEquals($validUuid, $userId->getValue());
        $this->assertEquals($validUuid, (string) $userId);
    }

    public function testInvalidUuidThrowsException(): void
    {
        $this->expectException(InvalidUserIdException::class);
        $this->expectExceptionMessage('Invalid UUID format: invalid-uuid');
        
        new UserId('invalid-uuid');
    }

    public function testEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidUserIdException::class);
        
        new UserId('');
    }

    public function testEqualsReturnsTrueForSameUuid(): void
    {
        $uuid = '88224979-406e-4e32-9458-55836e4e1f95';
        $userId1 = new UserId($uuid);
        $userId2 = new UserId($uuid);
        
        $this->assertTrue($userId1->equals($userId2));
    }

    public function testEqualsReturnsFalseForDifferentUuid(): void
    {
        $userId1 = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
        $userId2 = new UserId('12345678-1234-4123-8123-123456789012');
        
        $this->assertFalse($userId1->equals($userId2));
    }
}