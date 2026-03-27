<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class FunctionCall implements OperatorInterface
{
    /** @var array<OperatorInterface|TermInterface> */
    protected array $arguments;

    /**
     * @throws SparQlException
     */
    public function __construct(protected AbstractIri $functionIri, OperatorInterface|TermInterface ...$arguments)
    {
        $this->arguments = $arguments;
    }

    public function serialize(): string
    {
        $parts = array_map(fn(OperatorInterface|TermInterface $arg) => $arg->serialize(), $this->arguments);
        return sprintf('%s(%s)', $this->functionIri->serialize(), implode(', ', $parts));
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
