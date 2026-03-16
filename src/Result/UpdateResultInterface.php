<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

interface UpdateResultInterface extends StatementResultInterface
{
    public function getStatusCode(): int;

    public function getBody(): string;
}
