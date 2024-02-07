<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Contracts;

use OhDear\HealthCheckResults\CheckResult;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['oh_dear.health_check'])]
interface CheckInterface
{
    public function runCheck(): CheckResult;

    public function identify(): string;

    /**
     * How often should this check be run in seconds.
     */
    public function frequency(): int;
}
