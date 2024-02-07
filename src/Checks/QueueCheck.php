<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use Carbon\Carbon;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final class QueueCheck implements CheckInterface
{
    protected int $heartbeatMaxAgeInMinutes = 10;

    public function __construct(
        #[Target('cache.ohdear')]
        protected CacheItemPoolInterface $cache,
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
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Queue',
            shortSummary: 'running',
        );

        $lastBeat = $this->hearsHeartbeat();

        if ($lastBeat instanceof Carbon) {
            $minutesAgo = $lastBeat->diffInMinutes() + 1;

            if ($minutesAgo > $this->heartbeatMaxAgeInMinutes) {
                return $result
                    ->shortSummary('error')
                    ->failed("The last run of the queue was more than {$minutesAgo} minutes ago.")
                    ->meta(['last_heartbeat' => $lastBeat]);
            }

            return $result
                ->ok()
                ->shortSummary('Last Heartbeat: '.$lastBeat->diffForHumans())
                ->meta(['last_heartbeat' => $lastBeat, 'maxAgeInMinutes' => $this->heartbeatMaxAgeInMinutes]);
        }

        return $result->failed('CacheKey is not a Carbon instance.')->shortSummary('error');
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

    public function expectHeartbeatWithin(int $minutes): self
    {
        $this->heartbeatMaxAgeInMinutes = $minutes;

        return $this;
    }
}
