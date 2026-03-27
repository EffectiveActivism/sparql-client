<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Result\StatementResultInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\AddStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ClearStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\CopyStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\CreateStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DescribeStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DropStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\LoadStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\MoveStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface SparQlClientInterface
{
    public function execute(StatementInterface $statement): StatementResultInterface;

    public function addGraph(AbstractIri $sourceGraph, AbstractIri $destinationGraph): AddStatementInterface;

    public function ask(): AskStatementInterface;

    public function clearGraph(AbstractIri $graph): ClearStatementInterface;

    public function construct(array $triples): ConstructStatementInterface;

    public function copyGraph(AbstractIri $sourceGraph, AbstractIri $destinationGraph): CopyStatementInterface;

    public function createGraph(AbstractIri $graph): CreateStatementInterface;

    public function delete(array $triples): DeleteStatementInterface;

    public function describe(array $resources): DescribeStatementInterface;

    public function dropGraph(AbstractIri $graph): DropStatementInterface;

    public function insert(array $triples): InsertStatementInterface;

    public function load(AbstractIri $source): LoadStatementInterface;

    public function moveGraph(AbstractIri $sourceGraph, AbstractIri $destinationGraph): MoveStatementInterface;

    public function replace(array $triples): ReplaceStatementInterface;

    public function select(array $variables): SelectStatementInterface;
}
