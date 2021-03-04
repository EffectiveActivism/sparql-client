<?php declare(strict_types = 1);

namespace EffectiveActivism\SparQlClient;

use EffectiveActivism\SparQlClient\DependencyInjection\SparQlClientExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EffectiveActivismSparQlClientBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SparQlClientExtension();
    }
}
