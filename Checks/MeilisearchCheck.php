<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use JsonException;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MeilisearchCheck implements CheckInterface
{
    public function __construct(
        protected HttpClientInterface $client,
        protected string $url,
        protected ?int $timeout = 1,
    ) {
    }

    public function identify(): string
    {
        return 'Meilisearch';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = new CheckResult(name: $this->identify(), label: 'Meilisearch');

        try {
            $httpClient = HttpClient::create();
            $response = $httpClient->request('GET', $this->url, [
                'timeout' => $this->timeout,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(false);
        } catch (TransportExceptionInterface $exception) {
            return $result
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Unreachable')
                ->notificationMessage("Could not reach {$this->url}.");
        } catch (JsonException $exception) {
            return $result
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Response not valid JSON')
                ->notificationMessage('Could not convert response to JSON');
        }

        if (!$response->getContent()) {
            return $result
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Did not respond')
                ->notificationMessage("Did not get a response from {$this->url}.");
        }

        if (!isset($content['status'])) {
            return $result
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Invalid response')
                ->notificationMessage('The response did not contain a `status` key.');
        }

        $status = $content['status'];

        if ('available' !== $status) {
            return $result
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary(ucfirst($status))
                ->notificationMessage("The health check returned a status `{$status}`.");
        }

        return $result
            ->status(CheckResult::STATUS_OK)
            ->shortSummary($status);
    }
}
