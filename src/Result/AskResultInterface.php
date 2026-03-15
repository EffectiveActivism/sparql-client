<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

interface AskResultInterface extends StatementResultInterface
{
    public function getAnswer(): bool;
}
