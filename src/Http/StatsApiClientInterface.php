<?php

declare(strict_types=1);

namespace ChipAssessment\Http;

use ChipAssessment\ValueObject\UserId;

interface StatsApiClientInterface
{
    public function getUserIncome(UserId $userId): ?int;
}