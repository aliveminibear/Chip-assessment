<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use ChipAssessment\Http\StatsApiClientInterface;
use ChipAssessment\InterestAccountFactory;
use ChipAssessment\ValueObject\UserId;
use ChipAssessment\ValueObject\Money;
use DateTimeImmutable;

class MockStatsApiClient implements StatsApiClientInterface
{
    public function getUserIncome(UserId $userId): ?int
    {
        $userIdString = $userId->getValue();
        
        if (str_contains($userIdString, '88224979')) {
            return 600000; 
        } elseif (str_contains($userIdString, '12345678')) {
            return 400000; 
        }
        
        return null;
    }
}

echo "=== Interest Account Library ===\n\n";

$mockStatsClient = new MockStatsApiClient();
$service = InterestAccountFactory::create($mockStatsClient);

$highIncomeUserId = new UserId('88224979-406e-4e32-9458-55836e4e1f95');
$lowIncomeUserId = new UserId('12345678-1234-4123-8123-123456789012');

try {
    echo "1. Opening account for high income user...\n";
    $highIncomeAccount = $service->openAccount($highIncomeUserId);
    echo "    Account opened with {$highIncomeAccount->getInterestRate()->getAnnualRate()}% annual rate\n\n";

    echo "2. Making deposits...\n";
    $deposit1 = $service->deposit($highIncomeUserId, new Money(100000));
    echo "    Deposited £{$deposit1->getAmount()}\n";
    
    $deposit2 = $service->deposit($highIncomeUserId, new Money(50000)); 
    echo "    Deposited £{$deposit2->getAmount()}\n";
    
    $account = $service->getAccount($highIncomeUserId);
    echo "    Current balance: £{$account->getBalance()}\n\n";

    echo "3. Calculating interest after 3 days...\n";
    $futureDate = new DateTimeImmutable('+3 days');
    $interestTransaction = $service->calculateInterest($highIncomeUserId, $futureDate);
    
    if ($interestTransaction) {
        echo "    Interest paid: £{$interestTransaction->getAmount()}\n";
        $updatedAccount = $service->getAccount($highIncomeUserId);
        echo "    New balance: £{$updatedAccount->getBalance()}\n\n";
    } else {
        echo "    No interest payment (below threshold)\n\n";
    }

    echo "4. Account statement:\n";
    $statement = $service->getAccountStatement($highIncomeUserId);
    foreach ($statement as $transaction) {
        echo sprintf(
            "    %s | %8s | £%8s | %s\n",
            $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            strtoupper($transaction->getType()),
            $transaction->getAmount(),
            $transaction->getDescription()
        );
    }
    echo "\n";

    echo "5. Opening account for low income user...\n";
    $lowIncomeAccount = $service->openAccount($lowIncomeUserId);
    echo "     Account opened with {$lowIncomeAccount->getInterestRate()->getAnnualRate()}% annual rate\n\n";

    echo "6. Making deposit...\n";
    $service->deposit($lowIncomeUserId, new Money(200000)); // £2000
    echo "     Deposited £2000.00\n";
    
    $lowAccount = $service->getAccount($lowIncomeUserId);
    echo "     Current balance: £{$lowAccount->getBalance()}\n\n";

    echo "7. Calculating interest for low income user...\n";
    $lowInterestTransaction = $service->calculateInterest($lowIncomeUserId, $futureDate);
    
    if ($lowInterestTransaction) {
        echo "     Interest paid: £{$lowInterestTransaction->getAmount()}\n";
    } else {
        echo "     No interest payment (below threshold or not due)\n";
    }

    echo "\n8. Calculating interest for all accounts...\n";
    $allInterestTransactions = $service->calculateInterestForAllAccounts($futureDate);
    echo "     Processed " . count($allInterestTransactions) . " interest payments\n";

    echo "\n===  completed successfully! ===\n";

} catch (Exception $e) {
    echo "  Error: {$e->getMessage()}\n";
    echo "   Class: " . get_class($e) . "\n";
    if ($e->getPrevious()) {
        echo "   Previous: {$e->getPrevious()->getMessage()}\n";
    }
}