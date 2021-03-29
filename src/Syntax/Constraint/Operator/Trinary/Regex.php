<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Regex implements TrinaryOperatorInterface
{
    protected TermInterface $leftExpression;

    protected TermInterface $middleExpression;

    protected TermInterface|null $rightExpression = null;

    /**
     * @see https://www.w3.org/TR/xpath-functions/#func-matches.
     */
    public function __construct(PlainLiteral $string, PlainLiteral $pattern, PlainLiteral $flags = null)
    {
        $this->leftExpression = $string;
        $this->middleExpression = $pattern;
        $this->rightExpression = $flags;
    }

    public function serialize(): string
    {
        return $this->flags === null ?
            sprintf('REGEX(%s,%s)', $this->leftExpression, $this->middleExpression) :
            sprintf('REGEX(%s,%s,%s)', $this->leftExpression, $this->middleExpression, $this->rightExpression);
    }

    /**
     * Getters.
     */

    public function getLeftExpression(): TermInterface
    {
        return $this->leftExpression;
    }

    public function getMiddleExpression(): TermInterface
    {
        return $this->middleExpression;
    }

    public function getRightExpression(): TermInterface
    {
        return $this->rightExpression;
    }
}
