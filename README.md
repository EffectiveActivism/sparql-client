# SparQl client

An OOP SPARQL 1.1 client for Symfony with support for SELECT, CONSTRUCT, ASK,
DESCRIBE, DELETE, INSERT and DELETE+INSERT operations. Includes graph patterns,
aggregates, scalar functions, dataset clauses, and term, namespace and statement
validation.

## Table of content

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Namespaces and base URI](#namespaces-and-base-uri)
    - [Select statement](#select-statement)
        - [Limit, offset and order](#limit-offset-and-order)
        - [Distinct and reduced](#distinct-and-reduced)
        - [Group by and having](#group-by-and-having)
        - [Expressions](#expressions)
        - [Dataset clauses](#dataset-clauses)
    - [Ask statement](#ask-statement)
    - [Construct statement](#construct-statement)
    - [Insert statement](#insert-statement)
    - [Delete statement](#delete-statement)
    - [Replace statement](#replace-statement)
    - [Describe statement](#describe-statement)
    - [Graph management](#graph-management)
    - [Graph patterns](#graph-patterns)
    - [Union](#union)
    - [Subquery](#subquery)
    - [Property paths and sets](#property-paths-and-sets)
        - [Inverse path example](#inverse-path-example)
        - [Sequence path example](#sequence-path-example)
        - [Alternative path example](#alternative-path-example)
        - [Zero-or-more, one-or-more and zero-or-one path examples](#zero-or-more-one-or-more-and-zero-or-one-path-examples)
        - [Negated set example](#negated-set-example)
    - [Assignment](#assignment)
        - [Bind example](#bind-example)
        - [Values example](#values-example)
    - [Validation](#validation)
    - [Optional clauses](#optional-clauses)
    - [Service](#service)
    - [Constraints](#constraints)
        - [Filter examples](#filter-examples)
        - [Filter exists](#filter-exists)
        - [Minus](#minus)
        - [Operator catalogue](#operator-catalogue)
    - [Aggregates](#aggregates)
    - [Error handling](#error-handling)
    - [Cache tagging](#cache-tagging)
- [SHACL validator](#shacl-validator)
- [Example docker-compose setup](#example-docker-compose-setup)

## Installation

To install, run
```bash
composer require effectiveactivism/sparql-client
```

## Configuration

This bundle requires two SPARQL endpoints: one for query operations
(SELECT, ASK, CONSTRUCT, DESCRIBE) and one for update operations
(INSERT, DELETE, CLEAR, DROP, CREATE, REPLACE). You can also optionally
define a SHACL endpoint.

For Blazegraph, both endpoints are the same URL:

```yaml
sparql_client:
  query_endpoint: http://blazegraph:9999/blazegraph/sparql
  update_endpoint: http://blazegraph:9999/blazegraph/sparql
  shacl_endpoint: http://test-validator-endpoint/shacl/myshapes/api/validate
```

For Oxigraph, use the separate `/query` and `/update` paths:

```yaml
sparql_client:
  query_endpoint: http://oxigraph:7878/query
  update_endpoint: http://oxigraph:7878/update
  shacl_endpoint: http://test-validator-endpoint/shacl/myshapes/api/validate
```

## Usage

### Namespaces and base URI

Namespace prefixes and the optional `BASE` URI are declared on the
statement, not on the client — every example below chains
`->withNamespaces([...])` onto its builder. Both methods return the
statement and accept further chaining.

```php
<?php

$selectStatement = $sparQlClient
    ->select([$subject])
    ->withNamespaces([
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'schema' => 'http://schema.org/',
    ])
    ->withBase('http://example.org/')
    ->where([$triple]);
```

Calling `withNamespaces()` more than once merges the new prefixes into
the existing set. Prefix names are validated against the SPARQL
`PN_PREFIX` grammar; invalid input throws a `SparQlException`. The
`withBase()` URI is also validated.

### Select statement

Retrieve any subjects that have a `schema:headline` of `"Lorem"@la`.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create a select statement and declare the 'schema' namespace.
        $selectStatement = $sparQlClient
            ->select([$subject])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([$triple]);
        // Perform the query.
        $result = $sparQlClient->execute($selectStatement);
        // The result will contain each 'subject' found.
        /** @var TermInterface[] $row */
        foreach ($result->getRows() as $row) {
            dump($row[$subject->getVariableName()]);
        }
    }
}
```

#### Limit, offset and order

Select statements can use result modifiers:

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Order\Asc;
use EffectiveActivism\SparQlClient\Syntax\Order\Desc;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Multiply;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;

$selectStatement->limit(3);

$selectStatement->offset(2);

$orderVariable = new Variable('orderByThis');
$multiplier = new TypedLiteral(2);
// ASC and DESC accept any Variable or operator expression; mix freely.
$selectStatement->orderBy([
    new Asc(new Multiply($orderVariable, $multiplier)),
    new Desc($orderVariable),
]);
```

#### Distinct and reduced

```php
<?php

$selectStatement->distinct();
// or
$selectStatement->reduced();
```

#### Group by and having

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\GreaterThan;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Count;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$subject = new Variable('subject');
$countVar = new Variable('count');

$selectStatement
    ->groupBy([$subject])
    ->having(new GreaterThan(new Count($subject), new TypedLiteral(5)));
```

#### Expressions

`SelectExpression` binds an operator expression to a variable in the SELECT clause, equivalent to `( <expr> AS ?var )`.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Count;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression\SelectExpression;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$subject = new Variable('subject');
$countVar = new Variable('count');
$countExpression = new SelectExpression(new Count($subject), $countVar);

$selectStatement = $sparQlClient->select([$subject, $countExpression])->where([$triple]);
```

#### Dataset clauses

`FROM` and `FROM NAMED` dataset clauses are available on SELECT, ASK, CONSTRUCT and DESCRIBE statements.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;

$selectStatement
    ->from(new Iri('urn:example:dataset'))
    ->fromNamed(new Iri('urn:example:named-dataset'));
```

### Ask statement

Query whether a set of clauses has a solution:

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Iri('urn:uuid:5b08b896-a8ee-11eb-acf0-a3d5edd5c2a6');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create an ask statement and declare the 'schema' namespace.
        $askStatement = $sparQlClient
            ->ask()
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([$triple]);
        // Perform the query.
        $result = $sparQlClient->execute($askStatement);
        // The result will be a boolean value.
        if ($result->getAnswer() === true) {
            dump('yes');
        }
    }
}
```

### Construct statement

Construct a set of triples:

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create a construct statement and declare the 'schema' namespace.
        $constructStatement = $sparQlClient
            ->construct([$triple])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([$triple]);
        // Perform the query.
        $result = $sparQlClient->execute($constructStatement);
        // The result will contain each constructed triple.
        foreach ($result->getTriples() as $triple) {
            dump($triple);
        }
    }
}
```

### Insert statement

Insert the following triple:

```turtle
<urn:uuid:a61d21a4-824d-11eb-95c3-ebff6d3fb918> schema:headline "Lorem"@la .
```

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Iri('urn:uuid:a61d21a4-824d-11eb-95c3-ebff6d3fb918');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create an insert statement and declare the 'schema' namespace.
        $insertStatement = $sparQlClient
            ->insert([$triple])
            ->withNamespaces(['schema' => 'http://schema.org/']);
        // Perform the update. Returns an UpdateResultInterface with getStatusCode() and getBody().
        $sparQlClient->execute($insertStatement);
    }
}
```

### Delete statement

Delete all triples where the subject has the headline `"lorem"@la` but only
if the subject has type `schema:Article`.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $tripleToDelete = new Triple($subject, $predicate, $object);
        // Add a prefixed IRI of the form 'rdf:type'.
        $predicate2 = new PrefixedIri('rdf', 'type');
        // Add a prefixed IRI of the form 'schema:Article'.
        $object2 = new PrefixedIri('schema', 'Article');
        $tripleToFilter = new Triple($subject, $predicate2, $object2);
        // Create a delete statement and declare the 'schema' namespace.
        $deleteStatement = $sparQlClient
            ->delete([$tripleToDelete])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([$tripleToFilter]);
        // Perform the update.
        $sparQlClient->execute($deleteStatement);
    }
}
```

### Replace statement

Also known as DELETE+INSERT statements.

Replace all triples where a subject has a `schema:headline` of `"lorem"@la`
with a `schema:headline` of `"ipsum"@la`.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $tripleToReplace = new Triple($subject, $predicate, $object);
        // Add a prefixed IRI of the form 'rdf:type'.
        $predicate2 = new PrefixedIri('schema', 'headline');
        // Add a prefixed IRI of the form 'schema:Article'.
        $object2 = new PlainLiteral('ipsum', 'la');
        // Add a replacement triple.
        $replacementTriple = new Triple($subject, $predicate2, $object2);
        // Create a replace statement and declare the 'schema' namespace.
        $replaceStatement = $sparQlClient
            ->replace([$tripleToReplace])
            ->with([$replacementTriple])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([$tripleToReplace]);
        // Perform the update.
        $sparQlClient->execute($replaceStatement);
    }
}
```

### Describe statement

Describe one or more variables or IRIs.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create a describe statement and declare the 'schema' namespace.
        $describeStatement = $sparQlClient
            ->describe([$subject])
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([$triple]);
        // Perform the query.
        $result = $sparQlClient->execute($describeStatement);
        // The result will contain the resource description as triples.
        foreach ($result->getTriples() as $triple) {
            dump($triple);
        }
    }
}
```

### Graph management

Graph lifecycle operations are exposed as builder methods on the client.
Each returns a statement that can be passed to `execute()`. All graph
statements support a `silent()` modifier that emits the SPARQL `SILENT`
keyword so the triplestore returns success even when the operation
would otherwise fail (e.g. dropping a non-existent graph).

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;

$source = new Iri('http://example.org/source');
$destination = new Iri('http://example.org/destination');

// CREATE / DROP / CLEAR a single graph.
$sparQlClient->execute($sparQlClient->createGraph($source));
$sparQlClient->execute($sparQlClient->clearGraph($source));
$sparQlClient->execute($sparQlClient->dropGraph($source)->silent());

// COPY / MOVE / ADD between graphs.
$sparQlClient->execute($sparQlClient->copyGraph($source, $destination));
$sparQlClient->execute($sparQlClient->moveGraph($source, $destination));
$sparQlClient->execute($sparQlClient->addGraph($source, $destination));

// LOAD a remote document. Use into() to target a specific graph.
$sparQlClient->execute($sparQlClient->load(new Iri('http://example.org/data.ttl')));
$sparQlClient->execute(
    $sparQlClient
        ->load(new Iri('http://example.org/data.ttl'))
        ->into($destination)
);
```

Each call returns an `UpdateResultInterface`; cache entries tagged with
the affected graph IRIs are invalidated automatically.

### Graph patterns

Restrict a set of triple patterns to a named graph using the `Graph` class.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Graph\Graph;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$subject = new Variable('subject');
$triple = new Triple($subject, new PrefixedIri('schema', 'headline'), new Variable('headline'));
$graph = new Graph(new Iri('urn:example:graph'), [$triple]);

$selectStatement = $sparQlClient->select([$subject])->where([$graph]);
```

### Union

Match triples from either of two sets of patterns using `Union`.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Union\Union;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$subject = new Variable('subject');
$leftTriple = new Triple($subject, new PrefixedIri('schema', 'headline'), new PlainLiteral('Lorem'));
$rightTriple = new Triple($subject, new PrefixedIri('schema', 'headline'), new PlainLiteral('Ipsum'));
$union = new Union([$leftTriple], [$rightTriple]);

$selectStatement = $sparQlClient->select([$subject])->where([$union]);
```

### Subquery

Embed a SELECT statement as a subquery using the `Subquery` class.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$subject = new Variable('subject');
$innerTriple = new Triple($subject, new PrefixedIri('schema', 'headline'), new Variable('headline'));
$innerSelect = $sparQlClient->select([$subject])->where([$innerTriple]);
$subquery = new Subquery($innerSelect);

$outerTriple = new Triple($subject, new PrefixedIri('schema', 'name'), new Variable('name'));
$outerSelect = $sparQlClient->select([$subject])->where([$subquery, $outerTriple]);
```

### Property paths and sets

To use path operators such as inverse path (`^`), use the
`EffectiveActivism\SparQlClient\Syntax\Term\Path` classes.

#### Inverse path example

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath; 

$predicate = new PrefixedIri('schema', 'headline');
// Inverse predicate
$inversePredicate = new InversePath($predicate);
// The below will output "^schema:headline"
dump($inversePredicate->serialize());
```

#### Sequence path example

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\SequencePath; 

$predicate = new PrefixedIri('schema', 'headline');
// Inverse predicate
$inversePredicate = new InversePath($predicate);
// Sequence of predicate and inverse predicate.
$sequencePath = new SequencePath($predicate, $inversePredicate);
// The below will output "schema:headline / ^schema:headline"
dump($sequencePath->serialize());
```

#### Alternative path example

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\AlternativePath;

$left = new PrefixedIri('schema', 'headline');
$right = new PrefixedIri('schema', 'name');
$alternative = new AlternativePath($left, $right);
// Outputs "schema:headline | schema:name"
dump($alternative->serialize());
```

#### Zero-or-more, one-or-more and zero-or-one path examples

These unary path operators correspond to SPARQL's `*`, `+` and `?` path
quantifiers. They wrap any IRI or path term.

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\OneOrMorePath;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\ZeroOrMorePath;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\ZeroOrOnePath;

$predicate = new PrefixedIri('schema', 'knows');
// Outputs "schema:knows*"
dump((new ZeroOrMorePath($predicate))->serialize());
// Outputs "schema:knows+"
dump((new OneOrMorePath($predicate))->serialize());
// Outputs "schema:knows?"
dump((new ZeroOrOnePath($predicate))->serialize());
```

#### Negated set example

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use \EffectiveActivism\SparQlClient\Syntax\Term\Path\InversePath;
use \EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet; 

$predicate = new PrefixedIri('schema', 'headline');
// Inverse predicate
$inversePredicate = new InversePath($predicate);
// Negated set of predicate and inverse predicate.
$negatedSet = new NegatedPropertySet([$predicate, $inversePredicate]);
// The below will output "!(schema:headline | (^schema:headline))"
dump($negatedSet->serialize());
```

### Assignment

Two assignment options are available: BIND and VALUES.

#### BIND example

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment\Bind;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Multiply;use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        $commentCountVariable = new Variable('commentCount');
        $multipliedCommentCountVariable = new Variable('multipliedCommentCount');
        $multiplier = new TypedLiteral(2);
        // Bind an expression such as a multiplication to a variable.
        $bind = new Bind(new Multiply($multiplier, $commentCountVariable), $multipliedCommentCountVariable);
        $selectStatement = $sparQlClient
            ->select([$commentCountVariable])
            ->where([$bind]);
        $sparQlClient->execute($selectStatement);
    }
}
```

#### VALUES example

To use `UNDEF` in a VALUES expression, use `null`. Value arrays must have
the same dimension as the variables array.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment\Values;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        $headlineVariable = new Variable('headline');
        $commentCountVariable = new Variable('commentCount');
        $headlineValue1 = new PlainLiteral('Lorem');
        $headlineValue2 = new PlainLiteral('Ipsum');
        $commentCountValue1 = new TypedLiteral(2);
        $values = new Values([
            $headlineVariable,
            $commentCountVariable
        ], [
            // Each sub-array has the same dimension as the variable array
            // above.
            [$headlineValue1, $headlineValue2],
            // Null values are used to signify an undefined value.
            [$commentCountValue1, null]
        ]);
        $selectStatement = $sparQlClient
            ->select([$headlineVariable, $commentCountVariable])
            ->where([$values]);
        $sparQlClient->execute($selectStatement);
    }
}
```

The above example will produce the following statement

```
SELECT ?headline ?commentCount WHERE { VALUES ( ?headline ?commentCount ) { ( "Lorem" "2"^^xsd:integer ) ( "Ipsum" UNDEF ) } . }
```

### Validation

This bundle supports validation of terms. For example, the below assignment
will throw a `SparQlException` because the prefix contains illegal
characters.

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

$subject = new PrefixedIri('‿schema', 'headline');
```

Namespaces are validated when added dynamically in code.

Rudimentary validation of statements is also supported. For example, the
below statement will throw a `SparQlException` because the 'where' clause
does not contain any of the requested variables.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        $variable = new Variable('foo');
        $subject = new Iri('urn:uuid:c40f9982-8322-11eb-b0ba-57776fae8cf3');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem', 'la');
        $statement = $sparQlClient
            ->select([$variable])
            ->where([new Triple($subject, $predicate, $object)]);
        // Throws a SparQlException.
        $sparQlClient->execute($statement);
    }
}
```

### Optional clauses

To add an optional clause, use the
`EffectiveActivism\SparQlClient\Syntax\Optionally\Optionally` class.

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Pattern\Optionally\Optionally;

$optionalClause = new Optionally([$triple, $filter]);
$statement->where([$triple, $optionalClause]);
```

### Service

To use a service, use the
`EffectiveActivism\SparQlClient\Syntax\Pattern\Service\Service` class.

The first parameter is the service IRI, while the second parameter is an array
of patterns to use with the service.

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Pattern\Service\Service;
use \EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use \EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use \EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$service = new Service(
    new PrefixedIri('bds', 'search'),
    [
        new Triple(
            new Variable('object'),
            new PrefixedIri('bds', 'search'),
            new PlainLiteral('foo')
        )
    ]
);
$statement->where([
    $service,
    new Triple(
      new Variable('subject'),
      new Variable('predicate'),
      new Variable('object')
    )
]);
```

### Constraints

To apply a constraint, such as a filter, use the
`EffectiveActivism\SparQlClient\Syntax\Constraint` classes.

To use an operator with the `Filter()` class, use the
`EffectiveActivism\SparQlClient\Syntax\Constraint\Operator` classes.

#### Filter examples

The example below showcase how to select all subjects that has a
`schema:headline` value of `lorem` except any subjects with a
`schema:identifier` value of `13a5b1da-9060-11eb-a695-2bfde2d1d6bd`.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\FilterNotExists;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        $variable = new Variable('foo');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem');
        $filterPredicate = new PrefixedIri('schema', 'identifier');
        $filterObject = new PlainLiteral('13a5b1da-9060-11eb-a695-2bfde2d1d6bd');
        $statement = $sparQlClient
            ->select([$variable])
            ->where([
                new Triple($variable, $predicate, $object),
                new FilterNotExists([
                    new Triple($variable, $filterPredicate, $filterObject)
                ]),
            ]);
        $result = $sparQlClient->execute($statement);
    }
}
```

This example showcases how to use filter operators. Type enforcement
ensures some argument validation.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Filter;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Equal;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\NotEqual;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;

$object1 = new PlainLiteral('Lorem');
$object2 = new PlainLiteral('Ipsum');
$object3 = new PlainLiteral(12);

// Returns a filter of the form FILTER('Lorem' = 'Ipsum')
new Filter(new Equal($object1, $object2));

// Throws a SparQlException because the argument data types do not match.
new Filter(new NotEqual($object1, $object3));
```

#### Filter exists

`FilterExists` is the positive counterpart to `FilterNotExists`. It accepts
the same shape — an array of patterns — and emits `FILTER EXISTS { ... }`.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\FilterExists;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;

$statement->where([
    new Triple($subject, $predicate, $object),
    new FilterExists([
        new Triple($subject, new PrefixedIri('schema', 'identifier'), $identifier),
    ]),
]);
```

#### Minus

`Minus` removes solutions that match a second pattern group, emitting
`MINUS { ... }`. Unlike `FilterNotExists`, it operates on the solution
set rather than the candidate bindings, which matters when the inner
pattern shares no variables with the outer one.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Minus;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;

$statement->where([
    new Triple($subject, $predicate, $object),
    new Minus([
        new Triple($subject, new PrefixedIri('schema', 'identifier'), $identifier),
    ]),
]);
```

#### Operator catalogue

The library ships a large operator library beyond the few used in the
examples above. They live under
`EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator`, split
by arity:

- `Operator\Binary` — comparisons (`Equal`, `NotEqual`, `GreaterThan`,
  `GreaterThanOrEqual`, `LessThan`, `LessThanOrEqual`, `SameTerm`),
  arithmetic (`Add`, `Substract`, `Multiply`, `Divide`), boolean
  (`LogicalAnd`, `LogicalOr`), strings (`Contains`, `LangMatches`,
  `StrAfter`, `StrBefore`, `StrEnds`, `StrStarts`, `StrDt`, `StrLang`).
- `Operator\Unary` — type tests (`IsBlank`, `IsIri`, `IsLiteral`,
  `IsNumeric`, `IsUri`, `Bound`), term constructors (`IriOp`, `BNodeOp`,
  `Lang`, `Datatype`, `Str`, `Uuid`, `StrUuid`, `EncodeForUri`),
  numeric (`Abs`, `Ceil`, `Floor`, `Round`, `Negative`, `Positive`,
  `Rand`, `StrLen`), string casing (`LCaseOp`, `UCaseOp`), date/time
  (`Now`, `Year`, `Month`, `Day`, `Hours`, `Minutes`, `Seconds`,
  `Timezone`, `Tz`), hashing (`Md5`, `Sha1`, `Sha256`, `Sha384`,
  `Sha512`), boolean (`Not`).
- `Operator\Trinary` — `IfOp`, `Regex`, `ReplaceOp`, `SubStr`.
- `Operator\Variadic` — `Coalesce`, `Concat`, `In`, `NotIn`.

All operators implement `OperatorInterface` and can be used wherever an
operator is accepted: inside `Filter`, `having()`, `orderBy()`,
`Bind`, `SelectExpression`, etc.

### Aggregates

Aggregate functions are available under
`EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate`.
They implement `OperatorInterface` and can be used anywhere an operator is
accepted, including `SelectExpression`, `groupBy()`, and `having()`.

Available aggregates: `Count`, `Sum`, `Avg`, `Min`, `Max`, `Sample`, `GroupConcat`.

All aggregates support a `distinct()` modifier. `Count` also accepts `null` to
produce `COUNT(*)`.

```php
<?php

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Count;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\GroupConcat;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Sum;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression\SelectExpression;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

$subject = new Variable('subject');
$value = new Variable('value');

// COUNT(*) AS ?total
$total = new SelectExpression(new Count(), new Variable('total'));

// COUNT(DISTINCT ?subject) AS ?uniqueSubjects
$unique = new SelectExpression((new Count($subject))->distinct(), new Variable('uniqueSubjects'));

// SUM(?value) AS ?valueSum
$sum = new SelectExpression(new Sum($value), new Variable('valueSum'));

$selectStatement = $sparQlClient
    ->select([$subject, $total, $unique, $sum])
    ->where([$triple])
    ->groupBy([$subject]);
```

### Error handling

All methods on `SparQlClientInterface` throw `SparQlException` on failure. The exception exposes three additional accessors beyond the standard `getMessage()`:

- `getStatusCode(): ?int` — HTTP status code from the triplestore response, if available.
- `getResponseBody(): ?string` — raw response body, if available.
- `getQuery(): ?string` — the serialized SPARQL query that caused the failure.

```php
<?php

use EffectiveActivism\SparQlClient\Exception\SparQlException;

try {
    $result = $sparQlClient->execute($selectStatement);
} catch (SparQlException $exception) {
    // HTTP status code returned by the triplestore (null if the request never completed).
    $exception->getStatusCode();
    // Raw response body (null if unavailable).
    $exception->getResponseBody();
    // The SPARQL query string that triggered the failure.
    $exception->getQuery();
}
```

### Cache tagging

SELECT, CONSTRUCT and DESCRIBE responses are stored in the configured
tag-aware cache pool and tagged so subsequent INSERT, DELETE and REPLACE
operations can invalidate the affected entries. Two kinds of tag are
produced for each cached response:

- **Structural tags** — derived from the query pattern (the conditions
  passed to `where()` plus, for DESCRIBE, the resources). These cover
  every IRI and literal mentioned in the query itself.
- **Per-row tags** — derived from each IRI and literal that appears in
  the response rows / triples.

For aggregate queries or large result sets, the per-row tag walk can
dominate wall time after the triplestore has already responded — every
unique IRI/literal contributes an extra `Uuid::uuid5()` call. When the
extra invalidation precision isn't worth that cost (typically: any
single write to the matched pattern would evict the entry anyway, so
the structural tags are sufficient), call `withoutResultTags()` on the
statement to skip the per-row walk entirely:

```php
<?php

$selectStatement = $sparQlClient
    ->select([$subject])
    ->where([$triple])
    ->withoutResultTags();
$result = $sparQlClient->execute($selectStatement);
```

`withoutResultTags()` is available on SELECT, CONSTRUCT and DESCRIBE
statements. The default behaviour is to produce both kinds of tag.

# SHACL validator

To use the validator service, define the SHACL validator endpoint and
inject `ShaclClientInterface`. The validator only accepts CONSTRUCT
statements directly; `convertToConstructStatement()` rewrites an
INSERT, DELETE, REPLACE or CONSTRUCT statement into a validation-shaped
CONSTRUCT first.

`validate()` returns a `ValidationResultInterface` with two accessors:

- `getStatus(): bool` — `true` when the statement passes validation.
- `getMessages(): array` — human-readable validation messages from the
  validator (empty when the status is `true`).

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\ShaclClientInterface;
use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(ShaclClientInterface $shaclClient, SparQlClientInterface $sparQlClient)
    {
        /** @var TripleInterface $triple */
        $triple = new Triple(...);
        $insertStatement = $sparQlClient
            ->insert([$triple])
            ->where([$triple]);
        // Convert the mutation to a CONSTRUCT for validation.
        $constructStatement = $shaclClient->convertToConstructStatement($insertStatement);
        $result = $shaclClient->validate($constructStatement);
        if ($result->getStatus()) {
            dump('statement is valid!');
        } else {
            foreach ($result->getMessages() as $message) {
                dump($message);
            }
        }
    }
}
```

# Example docker-compose setup

The docker services below showcase a working setup. This client has not
been tested with other triplestores or validators.

The Blazegraph docker image requires no setup.
To setup the isaitb SHACL validator, go [here](https://www.itb.ec.europa.eu/docs/guides/latest/validatingRDF/#step-4-setup-validator-as-docker-container)

```yaml
version: '3.3'

services:
  
  ###############
  # Triplestore #
  ###############

  triplestore:
    restart: unless-stopped
    image: nawer/blazegraph:2.1.5
    volumes:
      - ./triplestore/data:/var/lib/blazegraph
    ports:
      - 9999:9999

  #############
  # Validator #
  #############

  validator:
    restart: unless-stopped
    image: isaitb/shacl-validator:1.0.0
    environment:
      - validator.resourceRoot:/validator/resources/
    volumes:
      - ./validator/resources:/validator/resources
    ports:
      - 8080:8080
```
