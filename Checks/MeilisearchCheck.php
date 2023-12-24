<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use JsonException;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MeilisearchCheck implements CheckInterface
{
    protected int $timeout = 1;

    protected string $url = 'https://search.fit-4-future.de/health';

    public function __construct(
        protected HttpClientInterface $client
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

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function runCheck(): CheckResult
    {
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
            return (new CheckResult($this->identify()))
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Unreachable')
                ->notificationMessage("Could not reach {$this->url}.");
        } catch(JsonException $exception) {
            return (new CheckResult($this->identify()))
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Response not valid JSON')
                ->notificationMessage('Could not convert response to JSON');
        }

        if (! $response->getContent()) {
            return (new CheckResult($this->identify()))
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Did not respond')
                ->notificationMessage("Did not get a response from {$this->url}.");
        }

        if (! isset($content['status'])) {
            return (new CheckResult($this->identify()))
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary('Invalid response')
                ->notificationMessage('The response did not contain a `status` key.');
        }

        $status = $content['status'];

        if ($status !== 'available') {
            return (new CheckResult($this->identify()))
                ->status(CheckResult::STATUS_FAILED)
                ->shortSummary(ucfirst($status))
                ->notificationMessage("The health check returned a status `{$status}`.");
        }

        return (new CheckResult($this->identify()))
                ->status(CheckResult::STATUS_OK)
            ->shortSummary(ucfirst($status));
    }
}
