<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use OhDear\HealthCheckResults\CheckResult;
use Spatie\Regex\Regex;
use Symfony\Component\Process\Process;

class DiskUsageCheck implements CheckInterface
{
    protected int $warningThreshold = 70;

    protected int $errorThreshold = 90;

    protected ?string $filesystemName = null;

    public function identify(): string
    {
        return 'disk-usage-checker';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function filesystemName(string $filesystemName): self
    {
        $this->filesystemName = $filesystemName;

        return $this;
    }

    public function warnWhenUsedSpaceIsAbovePercentage(int $percentage): self
    {
        $this->warningThreshold = $percentage;

        return $this;
    }

    public function failWhenUsedSpaceIsAbovePercentage(int $percentage): self
    {
        $this->errorThreshold = $percentage;

        return $this;
    }

    public function runCheck(): CheckResult
    {
        $diskSpaceUsedPercentage = $this->getDiskUsagePercentage();

        $result = CheckResult::make(name: 'Disk-Usage', label: 'Disk-Usage')
            ->meta(['disk_space_used_percentage' => $diskSpaceUsedPercentage])
            ->shortSummary($diskSpaceUsedPercentage.'%');

        if ($diskSpaceUsedPercentage > $this->errorThreshold) {
            return $result->status(CheckResult::STATUS_FAILED)->notificationMessage("The disk is almost full ({$diskSpaceUsedPercentage}% used).");
        }

        if ($diskSpaceUsedPercentage > $this->warningThreshold) {
            return $result->status(CheckResult::STATUS_WARNING)->notificationMessage("The disk is almost full ({$diskSpaceUsedPercentage}% used).");
        }

        return $result->status(CheckResult::STATUS_OK);
    }

    protected function getDiskUsagePercentage(): int
    {
        $process = Process::fromShellCommandline('df -P '.($this->filesystemName ?: '.'));

        $process->run();

        $output = $process->getOutput();

        return (int) Regex::match('/(\d*)%/', $output)->group(1);
    }
}
