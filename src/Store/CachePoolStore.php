<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Store;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final class CachePoolStore implements StoreInterface
{
    public function __construct(
        #[Target('cache.ohdear')]
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function save(string $identifier, StoreItem $result): void
    {
        $this->cache->save(
            $this->cache->getItem($identifier)->set($result)
        );
    }

    public function fetchLastResult(string $identifier): ?StoreItem
    {
        $cachedItem = $this->cache->getItem($identifier);
        if (false === $cachedItem->isHit()) {
            return null;
        }

        return $cachedItem->get();
    }
}
