<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\BlankNode;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use InvalidArgumentException;

/**
 * @see https://www.w3.org/TR/sparql11-query/#QSynVariables
 */
class BlankNode extends AbstractTerm implements TermInterface
{
    protected string $label;

    public function __construct(string $label)
    {
        if (preg_match(sprintf('/%s/u', Constant::PN_LOCAL), $label) <= 0) {
            throw new InvalidArgumentException(sprintf('Label "%s" is not a valid blank label', $label));
        }
        $this->label = $label;
    }

    public function serialize(): string
    {
        return sprintf('_:%s', $this->label);
    }

    public function getRawValue(): string
    {
        return $this->label;
    }
}
