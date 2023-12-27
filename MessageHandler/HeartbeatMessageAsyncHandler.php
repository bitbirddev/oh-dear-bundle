<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\MessageHandler;

use bitbirddev\OhDearBundle\Message\HeartbeatMessageAsync;
use Carbon\Carbon;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler()]
final class HeartbeatMessageAsyncHandler
{
    public function __construct(protected CacheInterface $cache)
    {
    }

    public function __invoke(HeartbeatMessageAsync $message): void
    {
        $key = 'ohdear-app-health-heartbeat-async';
        $heartbeatDate = $this->cache->get(key: $key, beta: INF, callback: static function (ItemInterface $item) {
            return Carbon::now('Europe/Berlin');
        });
    }
}
