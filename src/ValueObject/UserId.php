<?php

declare(strict_types=1);

namespace ChipAssessment\ValueObject;

use ChipAssessment\Exception\InvalidUserIdException;

final class UserId
{
    private string $value;

    public function __construct(string $value)
    {
        if (!$this->isValidUuid($value)) {
            throw new InvalidUserIdException("Invalid UUID format: {$value}");
        }
        
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function isValidUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }
}