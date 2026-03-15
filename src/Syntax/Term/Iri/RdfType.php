<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Iri;

class RdfType extends AbstractIri
{
    public function getRawValue(): string
    {
        return 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
    }

    public function serialize(): string
    {
        return 'a';
    }
}
