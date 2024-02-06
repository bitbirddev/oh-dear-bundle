<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use Carbon\Carbon;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final class SchedulerCheck implements CheckInterface
{
    protected int $heartbeatMaxAgeInMinutes = 10;

    public function __construct(
        #[Target('cache.ohdear')]
        protected CacheItemPoolInterface $cache,
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
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Scheduler',
            shortSummary: 'running',
        );

        $lastBeat = $this->hearsHeartbeat();

        if ($lastBeat instanceof Carbon) {
            $minutesAgo = $lastBeat->diffInMinutes() + 1;

            if ($minutesAgo > $this->heartbeatMaxAgeInMinutes) {
                return $result
                    ->shortSummary('error')
                    ->failed("The last run of the schedule was more than {$minutesAgo} minutes ago.")
                    ->meta(['last_heartbeat' => $lastBeat]);
            }

            return $result
                ->ok()
                ->shortSummary('Last Heartbeat: '.$lastBeat->diffForHumans())
                ->meta(['last_heartbeat' => $lastBeat, 'maxAgeInMinutes' => $this->heartbeatMaxAgeInMinutes]);
        }

        return $result->status(CheckResult::STATUS_FAILED)->shortSummary('error')->notificationMessage('CacheKey is not a Carbon instance.');
    }

    protected function hearsHeartbeat(): bool|Carbon
    {
        $key = 'ohdear-app-health-heartbeat-sync';
        $item = $this->cache->getItem(key: $key);

        if ($item->isHit()) {
            return $item->get();
        }

        return false;
    }

    public function expectHeartbeatWithin(int $minutes): self
    {
        $this->heartbeatMaxAgeInMinutes = $minutes;

        return $this;
    }
}
