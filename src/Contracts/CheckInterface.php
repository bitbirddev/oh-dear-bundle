<?php

namespace bitbirddev\OhDearBundle\Contracts;

use OhDear\HealthCheckResults\CheckResult;

interface CheckInterface
{
    public function runCheck(): CheckResult;

    public function identify(): string;

    /**
     * How often should this check be run in seconds.
     */
    public function frequency(): int;
}
