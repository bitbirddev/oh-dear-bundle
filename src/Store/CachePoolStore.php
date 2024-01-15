<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Store;

use Psr\Cache\CacheItemPoolInterface;

final class CachePoolStore implements ResultStore
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function save(string $identifier, StoredResult $result): void
    {
        $this->cache->save(
            $this->cache->getItem($identifier)->set($result)
        );
    }

    public function fetchLastResult(string $identifier): ?StoredResult
    {
        $cachedItem = $this->cache->getItem($identifier);
        if (false === $cachedItem->isHit()) {
            return null;
        }

        return $cachedItem->get();
    }
}
