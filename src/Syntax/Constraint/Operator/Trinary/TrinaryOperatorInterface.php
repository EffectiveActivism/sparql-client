<?php

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface TrinaryOperatorInterface extends OperatorInterface
{
    /**
     * Getters.
     */

    public function getLeftExpression(): TermInterface;

    public function getMiddleExpression(): TermInterface;

    public function getRightExpression(): TermInterface;
}
