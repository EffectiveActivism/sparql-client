<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient\Tests\Environment;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TestBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new TestExtension();
    }
}
