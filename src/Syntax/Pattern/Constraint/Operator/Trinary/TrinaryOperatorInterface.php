<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface TrinaryOperatorInterface extends OperatorInterface
{
    /**
     * Getters.
     */

    public function getLeftExpression(): OperatorInterface|TermInterface;

    public function getMiddleExpression(): OperatorInterface|TermInterface;

    public function getRightExpression(): OperatorInterface|TermInterface|null;
}
