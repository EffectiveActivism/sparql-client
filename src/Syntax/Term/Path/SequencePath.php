<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class SequencePath extends AbstractBinaryPath implements TermInterface
{
    public function serialize(): string
    {
        $serializedValue1 = $this->term1 instanceof AbstractPath ? sprintf('(%s)', $this->term1->serialize()) : $this->term1->serialize();
        $serializedValue2 = $this->term2 instanceof AbstractPath ? sprintf('(%s)', $this->term2->serialize()) : $this->term2->serialize();
        return sprintf('%s / %s', $serializedValue1, $serializedValue2);
    }
}
