<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use Pimcore\Model\Tool\SettingsStore;
use stdClass;
use Symfony\Component\HttpKernel\KernelInterface;

final class GtmTagCheck implements CheckInterface
{
    protected ?stdClass $sites = null;
    protected bool $warnWhenGtmTagIsMissing = true;

    public function __construct(
        protected KernelInterface $kernel
    ) {
        $store = SettingsStore::get('reports', 'pimcore');
        $data = json_decode($store->getData());
        if ($data->tagmanager->sites instanceof stdClass) {
            $this->sites = $data->tagmanager->sites;
        }
    }

    public function identify(): string
    {
        return 'Google Tag Manager';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'GoogleTagManager',
        );

        if (!$this->hasGtmBundleInstalled()) {
            return $result->skipped('The GoogleTagManager Bundle is not installed');
        }

        if (!$this->sites) {
            return $result->failed('The GoogleTagManager is not set up for any Sites');
        }

        $tags = count($this->listGtmTags()) ? implode(', ', $this->listGtmTags()) : 'none';

        if ($this->warnWhenGtmTagIsMissing) {
            return $this->hasSitesWithoutGtmTag()
                ? $result->failed('The GoogleTagManager is not set up for all Sites')
                : $result->shortSummary('Tags: '.$tags)->ok();
        }

        return $result->shortSummary('Tags: '.$tags)->ok();
    }

    protected function hasGtmBundleInstalled(): bool
    {
        return class_exists(\Pimcore\Bundle\GoogleMarketingBundle\PimcoreGoogleMarketingBundle::class);
    }

    protected function listGtmTags(): array
    {
        $containerIds = [];

        foreach ($this->sites as $site) {
            $containerIds[] = $site->containerId;
        }

        return $containerIds;
    }

    protected function hasSitesWithoutGtmTag(): bool
    {
        foreach ($this->sites as $site) {
            if (empty($site->containerId)) {
                return true;
            }
        }

        return false;
    }

    public function dontWarnWhenTagIsMissing(): self
    {
        $this->warnWhenGtmTagIsMissing = false;

        return $this;
    }
}
