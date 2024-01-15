<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle;

use OhDear\HealthCheckResults\CheckResult as SpatieCheckResult;

final class CheckResult extends SpatieCheckResult
{
    /**
     * @param array<int, mixed> $meta
     */
    public static function make(
        string $name,
        string $label = '',
        string $notificationMessage = '',
        string $shortSummary = '',
        string $status = '',
        array $meta = [],
    ): self {
        return new self(...func_get_args());
    }

    public function ok(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = SpatieCheckResult::STATUS_OK;

        return $this;
    }

    public function warning(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = SpatieCheckResult::STATUS_WARNING;

        return $this;
    }

    public function failed(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = SpatieCheckResult::STATUS_FAILED;

        return $this;
    }

    public function crashed(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = SpatieCheckResult::STATUS_CRASHED;

        return $this;
    }

    public function skipped(string $message = ''): self
    {
        $this->notificationMessage = $message;

        $this->status = SpatieCheckResult::STATUS_SKIPPED;

        return $this;
    }

    /** @param  array<string, mixed>  $meta */
    public function meta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
