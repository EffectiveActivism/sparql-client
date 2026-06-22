<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

/**
 * Marks a term as a TriplesNode, i.e. an RDF Collection or a blank node
 * property list.
 *
 * @see https://www.w3.org/TR/sparql11-query/#rTriplesNode
 */
interface TriplesNodeInterface extends TermInterface
{
}
