<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Not extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    public function __construct(OperatorInterface|TermInterface $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        // Wrap operator operands in parentheses so precedence is preserved,
        // e.g. "! (?a = ?b)" rather than the mis-parsed "! ?a = ?b".
        $serializedExpression = $this->expression instanceof OperatorInterface
            ? sprintf('(%s)', $this->expression->serialize())
            : $this->expression->serialize();
        return sprintf('! %s', $serializedExpression);
    }
}
