<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle;

use bitbirddev\OhDearBundle\DependencyInjection\Compiler\CheckPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function dirname;

final class OhDearBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    protected function getComposerPackageName(): string
    {
        // getVersion() will use this name to read the version from
        // PackageVersions and return a normalized value
        return 'bitbirddev/oh-dear-bundle';
    }

    public function getNiceName(): string
    {
        return 'OhDear Bundle';
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CheckPass());
    }
}
