<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use Carbon\Carbon;
use OhDear\HealthCheckResults\CheckResult;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class SchedulerCheck implements CheckInterface
{
    public function __construct(
        protected CacheInterface $cache
    ) {
    }

    public function identify(): string
    {
        return 'Scheduler';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = new CheckResult(
            name: $this->identify(),
            label: 'Scheduler',
            shortSummary: 'running',
        );

        $lastBeat = $this->hearsHeartbeat();
        ray($lastBeat);

        if ($lastBeat) {
            if ($lastBeat instanceof Carbon) {
                $result->meta(['last_beat' => $lastBeat]);
            }
        }

        return $lastBeat
            ? $result->status(CheckResult::STATUS_OK)
            : $result->status(CheckResult::STATUS_FAILED)->shortSummary('error')->notificationMessage('not running');

        return $result;
    }

    protected function hearsHeartbeat(): null|Carbon
    {
        $key = 'ohdear-app-health-heartbeat-sync';

        return $this->cache->get(key: $key, callback: static function (ItemInterface $item) {
            $item->expiresAfter(1);

            return false;
        });
    }
}
