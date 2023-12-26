<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\MessageHandler;

use bitbirddev\OhDearBundle\Message\HeartbeatMessageSync;
use Carbon\Carbon;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler()]
final class HeartbeatMessageSyncHandler
{
    public function __construct(protected CacheInterface $cache)
    {
    }

    public function __invoke(HeartbeatMessageSync $message): void
    {
        $key = 'ohdear-app-health-heartbeat-sync';
        $heartbeatDate = $this->cache->get(key: $key, beta: INF, callback: static function (ItemInterface $item) {
            $item->expiresAfter(310);

            return Carbon::now('Europe/Berlin');
        });
    }
}
