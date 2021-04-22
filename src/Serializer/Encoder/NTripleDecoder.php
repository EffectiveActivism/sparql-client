<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Encoder;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use InvalidArgumentException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class NTripleDecoder implements DecoderInterface
{
    const FORMAT = 'ntriple';

    public function decode(string $data, string $format, array $context = [])
    {
        $triples = [];
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $line = trim($line, '. ');
            if (preg_match('/^((?:<.*>)|(?:_:.*)|(?:".*"(?:(?:\^\^<.*>)|(?:@.*))?)) ((?:<.*>)|(?:_:.*)|(?:".*"(?:(?:\^\^<.*>)|(?:@.*))?)) ((?:<.*>)|(?:_:.*)|(?:".*"(?:(?:\^\^<.*>)|(?:@.*))?))$/m', $line, $matches)) {
                if (count($matches) === 4) {
                    $subject = $this->extractTerm($matches[1]);
                    $predicate = $this->extractTerm($matches[2]);
                    $object = $this->extractTerm($matches[3]);
                    $triples[] = new Triple($subject, $predicate, $object);
                }
            }
            elseif (!empty($line)) {
                throw new InvalidArgumentException(sprintf('Line could not be parsed: %s', $line));
            }
        }
        return $triples;
    }

    public function supportsDecoding(string $format)
    {
        return self::FORMAT === $format;
    }

    protected function extractTerm(string $data): TermInterface
    {
        // Check for blank nodes.
        if (preg_match('/^_:(.+)$/m', $data, $matches)) {
            return new BlankNode($matches[1]);
        }
        // Check for IRIs.
        elseif (preg_match('/^<(.+)>$/m', $data, $matches)) {
            return new Iri($matches[1]);
        }
        // Check for typed literals.
        elseif (preg_match('/^(".*")\^\^<(.*)>$/m', $data, $matches)) {
            return new TypedLiteral(trim($matches[1], '"'), new Iri($matches[2]));
        }
        // Check for plain literals with language tag.
        elseif(preg_match('/^(".*")@(.*)$/m', $data, $matches)) {
            return new PlainLiteral(trim($matches[1], '"'), $matches[2]);
        }
        // Check for plain literals.
        elseif (preg_match('/(".*")$/m', $data, $matches)) {
            return new PlainLiteral(trim($matches[1], '"'));
        }
        else {
            throw new InvalidArgumentException(sprintf('Term "%s" cannot be parsed', $data));
        }
    }
}
