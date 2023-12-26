<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle;

use bitbirddev\OhDearBundle\DependencyInjection\Compiler\CheckPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OhDearBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CheckPass());
    }
}
