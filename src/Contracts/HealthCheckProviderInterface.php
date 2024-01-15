<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Contracts;

interface HealthCheckProviderInterface
{
    public function getHealthChecks(): array;
}
