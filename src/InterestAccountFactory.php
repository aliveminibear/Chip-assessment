<?php

declare(strict_types=1);

namespace ChipAssessment;

use ChipAssessment\Http\StatsApiClient;
use ChipAssessment\Http\StatsApiClientInterface;
use ChipAssessment\Repository\InMemoryInterestAccountRepository;
use ChipAssessment\Repository\InMemoryTransactionRepository;
use ChipAssessment\Repository\InterestAccountRepositoryInterface;
use ChipAssessment\Repository\TransactionRepositoryInterface;
use ChipAssessment\Service\InterestAccountService;
use ChipAssessment\Service\InterestCalculationService;
use GuzzleHttp\Client;

final class InterestAccountFactory
{
    public static function create(
        ?StatsApiClientInterface $statsApiClient = null,
        ?InterestAccountRepositoryInterface $accountRepository = null,
        ?TransactionRepositoryInterface $transactionRepository = null
    ): InterestAccountService {
        $accountRepository = $accountRepository ?? new InMemoryInterestAccountRepository();
        $transactionRepository = $transactionRepository ?? new InMemoryTransactionRepository();
        $statsApiClient = $statsApiClient ?? new StatsApiClient(new Client());
        
        $interestCalculationService = new InterestCalculationService($transactionRepository);

        return new InterestAccountService(
            $accountRepository,
            $transactionRepository,
            $statsApiClient,
            $interestCalculationService
        );
    }
}