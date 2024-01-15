<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Pimcore\Model\Tool\SettingsStore;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use stdClass;
use Symfony\Component\HttpKernel\KernelInterface;

final class GtmTagCheck implements CheckInterface
{
    protected stdClass $sites;

    public function __construct(
        protected KernelInterface $kernel
    ) {
        $store = SettingsStore::get('reports', 'pimcore');
        $data = json_decode($store->getData());
        $this->sites = $data->tagmanager->sites;
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

        return $this->hasSitesWithoutGtmTag()
            ? $result->failed('The GoogleTagManager is not setup for all Sites`')
            : $result->shortSummary('Active Tags: '.implode(',', $this->listGtmTags()))->ok();
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
}
