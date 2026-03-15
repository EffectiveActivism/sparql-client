<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Union;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Union implements UnionInterface
{
    /** @var PatternInterface[] */
    protected array $leftPatterns;

    /** @var PatternInterface[] */
    protected array $rightPatterns;

    public function __construct(array $leftPatterns, array $rightPatterns)
    {
        foreach ($leftPatterns as $pattern) {
            if (!is_object($pattern) || (!($pattern instanceof PatternInterface))) {
                $class = is_object($pattern) ? get_class($pattern) : gettype($pattern);
                throw new SparQlException(sprintf('Invalid pattern class: %s', $class));
            }
        }
        foreach ($rightPatterns as $pattern) {
            if (!is_object($pattern) || (!($pattern instanceof PatternInterface))) {
                $class = is_object($pattern) ? get_class($pattern) : gettype($pattern);
                throw new SparQlException(sprintf('Invalid pattern class: %s', $class));
            }
        }
        $this->leftPatterns = $leftPatterns;
        $this->rightPatterns = $rightPatterns;
    }

    public function toArray(): array
    {
        return array_merge($this->leftPatterns, $this->rightPatterns);
    }

    public function getTerms(): array
    {
        $terms = [];
        foreach (array_merge($this->leftPatterns, $this->rightPatterns) as $pattern) {
            foreach ($pattern->getTerms() as $item) {
                if ($item instanceof TermInterface) {
                    $terms[] = $item;
                }
            }
        }
        return $terms;
    }

    public function serialize(): string
    {
        $leftString = '';
        foreach ($this->leftPatterns as $pattern) {
            $leftString .= sprintf(' %s .', $pattern->serialize());
        }
        $rightString = '';
        foreach ($this->rightPatterns as $pattern) {
            $rightString .= sprintf(' %s .', $pattern->serialize());
        }
        return sprintf('{%s } UNION {%s }', $leftString, $rightString);
    }
}
