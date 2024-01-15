<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use JsonException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class MeilisearchCheck implements CheckInterface
{
    protected ?string $url = null;
    protected ?int $timeout = 1;

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

    public function runCheck(): CheckResult
    {
        $result = CheckResult::make(name: $this->identify(), label: 'Meilisearch');

        if (empty($this->url)) {
            return $result->skipped('The Meilisearch URL is not set.');
        }

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
                ->shortSummary('Unreachable')
                ->failed("Could not reach {$this->url}.");
        } catch (JsonException $exception) {
            return $result
                ->shortSummary('Response not valid JSON')
                ->failed('Could not convert response to JSON');
        }

        if (!$response->getContent()) {
            return $result
                ->shortSummary('Did not respond')
                ->failed("Did not get a response from {$this->url}.");
        }

        if (!isset($content['status'])) {
            return $result
                ->shortSummary('Invalid response')
                ->failed('The response did not contain a `status` key.');
        }

        $status = $content['status'];

        if ('available' !== $status) {
            return $result
                ->shortSummary(ucfirst($status))
                ->failed("The health check returned a status `{$status}`.");
        }

        return $result
            ->ok()
            ->shortSummary($status);
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
