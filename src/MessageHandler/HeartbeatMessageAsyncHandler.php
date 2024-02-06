<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\MessageHandler;

use bitbirddev\OhDearBundle\Message\HeartbeatMessageAsync;
use Carbon\Carbon;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
final class HeartbeatMessageAsyncHandler
{
    public function __construct(
        #[Target('cache.ohdear')]
        protected CacheItemPoolInterface $cache
    ) {
    }

    public function __invoke(HeartbeatMessageAsync $message): void
    {
        $item = $this->cache->getItem('ohdear-app-health-heartbeat-async')->set(Carbon::now('Europe/Berlin'));
        $this->cache->save($item);
    }
}
