<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
use Ramsey\Uuid\Uuid;

trait CacheTrait
{
    protected function getKey(string $value): string
    {
        return Uuid::uuid5(Constant::NAMESPACE_CACHE, $value)->toString();
    }
}
