<?php

declare(strict_types=1);

namespace ChipAssessment\Http;

use ChipAssessment\Exception\StatsApiException;
use ChipAssessment\ValueObject\UserId;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

final class StatsApiClient implements StatsApiClientInterface
{
    private Client $httpClient;
    private string $baseUrl;

    public function __construct(Client $httpClient, string $baseUrl = 'https://stats.dev.chip.test')
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getUserIncome(UserId $userId): ?int
    {
        try {
            $response = $this->httpClient->get(
                $this->baseUrl . '/users/' . $userId->getValue(),
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 10,
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw new StatsApiException(
                    "Stats API returned non-200 status: {$response->getStatusCode()}"
                );
            }

            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new StatsApiException('Invalid JSON response from Stats API');
            }

            if (!isset($data['income'])) {
                throw new StatsApiException('Missing income field in Stats API response');
            }

            return $data['income'];

        } catch (RequestException $e) {
            throw new StatsApiException(
                "Failed to fetch user income from Stats API: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (GuzzleException $e) {
            throw new StatsApiException(
                "HTTP client error: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }
}