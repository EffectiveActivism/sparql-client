<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class InversePath extends AbstractUnaryPath implements TermInterface
{
    public function serialize(): string
    {
        $serializedValue = $this->term->serialize();
        return $this->term instanceof AbstractPath ? sprintf('^(%s)', $serializedValue) : sprintf('^%s', $serializedValue);
    }
}
