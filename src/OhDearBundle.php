<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle;

use bitbirddev\OhDearBundle\DependencyInjection\Compiler\CheckPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use function dirname;

final class OhDearBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CheckPass());
    }
}
