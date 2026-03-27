<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\VariadicOperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class FunctionCall implements VariadicOperatorInterface
{
    /** @var array<OperatorInterface|TermInterface> */
    protected array $arguments;

    public function __construct(protected AbstractIri $functionIri, OperatorInterface|TermInterface ...$arguments)
    {
        $this->arguments = $arguments;
    }

    public function serialize(): string
    {
        $parts = array_map(fn(OperatorInterface|TermInterface $arg) => $arg->serialize(), $this->arguments);
        return sprintf('%s(%s)', $this->functionIri->serialize(), implode(', ', $parts));
    }

    public function getExpressions(): array
    {
        return array_merge([$this->functionIri], $this->arguments);
    }

    public function getFunctionIri(): AbstractIri
    {
        return $this->functionIri;
    }

    /**
     * @return array<OperatorInterface|TermInterface>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
