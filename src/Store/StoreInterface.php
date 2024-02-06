<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Store;

interface StoreInterface
{
    public function save(string $identifier, StoreItem $result): void;

    public function fetchLastResult(string $identifier): ?StoreItem;
}
