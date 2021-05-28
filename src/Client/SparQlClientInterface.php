<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use Symfony\Component\HttpFoundation\File\File;

interface SparQlClientInterface
{
    public function execute(StatementInterface $statement, bool $toTriples = false): array|bool;

    public function ask(): AskStatementInterface;

    public function construct(array $triples): ConstructStatementInterface;

    public function delete(TripleInterface $triple): DeleteStatementInterface;

    public function insert(TripleInterface $triple): InsertStatementInterface;

    public function replace(TripleInterface $triple): ReplaceStatementInterface;

    public function select(array $variables): SelectStatementInterface;

    public function upload(File $file, string $contentType = 'application/x-turtle'): bool;

    /**
     * Getters.
     */

    public function getNamespaces(): array;

    /**
     * Setters.
     */

    public function setExtraNamespaces(array $extraNamespaces): SparQlClientInterface;
}
