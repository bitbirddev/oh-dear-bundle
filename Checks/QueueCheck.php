<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use Carbon\Carbon;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Contracts\Cache\CacheInterface;

final class QueueCheck implements CheckInterface
{
    public function __construct(
        protected CacheInterface $cache,
        protected int $heartbeatMaxAgeInMinutes = 10,
    ) {
    }

    public function identify(): string
    {
        return 'Queue';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = new CheckResult(
            name: $this->identify(),
            label: 'Queue',
            shortSummary: 'running',
        );

        $lastBeat = $this->hearsHeartbeat();

        if ($lastBeat instanceof Carbon) {
            $minutesAgo = $lastBeat->diffInMinutes() + 1;

            if ($minutesAgo > $this->heartbeatMaxAgeInMinutes) {
                return $result
                    ->status(CheckResult::STATUS_FAILED)
                    ->shortSummary('error')
                    ->notificationMessage("The last run of the queue was more than {$minutesAgo} minutes ago.")
                    ->meta(['last_heartbeat' => $lastBeat]);
            }

            return $result
                ->status(CheckResult::STATUS_OK)
                ->shortSummary('Last Heartbeat: '.$lastBeat->diffForHumans())
                ->meta(['last_heartbeat' => $lastBeat, 'maxAgeInMinutes' => $this->heartbeatMaxAgeInMinutes]);
        }

        return $result->status(CheckResult::STATUS_FAILED)->shortSummary('error')->notificationMessage('CacheKey is not a Carbon instance.');
    }

    protected function hearsHeartbeat(): bool|Carbon
    {
        $key = 'ohdear-app-health-heartbeat-async';
        $item = $this->cache->getItem(key: $key);

        if ($item->isHit()) {
            return $item->get();
        }

        return false;
    }
}
