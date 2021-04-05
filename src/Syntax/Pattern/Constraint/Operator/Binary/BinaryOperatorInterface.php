<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface BinaryOperatorInterface extends OperatorInterface
{
    public function getLeftExpression(): TermInterface;

    public function getRightExpression(): TermInterface;
}