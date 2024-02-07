<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Contracts;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['oh_dear.health_check_provider'])]
interface HealthCheckProviderInterface
{
    public function getHealthChecks(): array;
}
