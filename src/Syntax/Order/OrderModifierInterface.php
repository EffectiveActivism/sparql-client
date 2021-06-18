<?php

namespace EffectiveActivism\SparQlClient\Syntax\Order;

interface OrderModifierInterface
{
    public function serialize(): string;
}
