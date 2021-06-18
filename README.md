# SparQl client

An OOP SparQl 1.1 client for Symfony with support for SELECT, CONSTRUCT, DELETE,
INSERT and DELETE+INSERT operations. Includes term, namespace and (basic)
statement validation.

## Table of content

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Select statement](#select-statement)
    - [Construct statement](#construct-statement)
    - [Insert statement](#insert-statement)
    - [Delete statement](#delete-statement)
    - [Replace statement](#replace-statement)
    - [Property paths and sets](#property-paths-and-sets)
        - [Inverse path example](#inverse-path-example)
        - [Sequence path example](#sequence-path-example)
        - [Negated set example](#negated-set-example)
    - [Assignment](#assignment)
        - [Bind example](#bind-example)
        - [Values example](#values-example)
    - [Validation](#validation)
    - [Optional clauses](#optional-clauses)
    - [Service](#service)
    - [Constraints](#constraints)
        - [Filter examples](#filter-examples)
    - [Upload files](#upload-files)
- [SHACL validator](#shacl-validator)
- [Example docker-compose setup](#example-docker-compose-setup)
- [Planned features](#planned-features)

## Installation

To install, run
```bash
composer require effectiveactivism/sparql-client
```

## Configuration

This bundle requires a SparQl endpoint string. You can optionally define
namespaces that should be included in every request. You can also optionally
define a SHACL endpoint.

```yaml
sparql_client:
  sparql_endpoint: http://test-sparql-endpoint:9999/blazegraph/sparql
  shacl_endpoint: http://test-validator-endpoint/shacl/myshapes/api/validate
  namespaces:
    - schema: http://schema.org/
    - dbo: http://dbpedia.org/ 
```

## Usage

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
        // Add the 'schema' namespace.
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create a select statement.
        $selectStatement = $sparQlClient->select([$subject])->where([$triple]);
        // Perform the query.
        $sets = $sparQlClient->execute($selectStatement);
        // The result will contain each 'subject' found.
        /** @var TermInterface[] $set */
        foreach ($sets as $set) {
            dump($set[$subject->getVariableName()]);
        }
    }
}
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
        // Add the 'schema' namespace.
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        // Add a subject as a variable '_subject'.
        $subject = new Iri('urn:uuid:5b08b896-a8ee-11eb-acf0-a3d5edd5c2a6');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create an ask statement.
        $askStatement = $sparQlClient->ask()->where([$triple]);
        // Perform the query.
        $result = $sparQlClient->execute($askStatement);
        // The result will be a boolean value.
        if ($result === true) {
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
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyController extends AbstractController
{
    public function view(SparQlClientInterface $sparQlClient)
    {
        // Add the 'schema' namespace.
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        // Add a subject as a variable '_subject'.
        $subject = new Variable('subject');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create a construct statement.
        $constructStatement = $sparQlClient->construct([$triple])->where([$triple]);
        // Perform the query.
        $sets = $sparQlClient->execute($constructStatement);
        // The result will contain each 'subject' found.
        /** @var TermInterface[] $set */
        foreach ($sets as $set) {
            dump($set[$subject->getVariableName()]);
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
        // Add the 'schema' namespace.
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
        // Add a subject as a variable '_subject'.
        $subject = new Iri('urn:uuid:a61d21a4-824d-11eb-95c3-ebff6d3fb918');
        // Add a prefixed IRI of the form 'schema:headline'.
        $predicate = new PrefixedIri('schema', 'headline');
        // Add a plain literal of the form 'Lorem@la'.
        $object = new PlainLiteral('Lorem', 'la');
        // Add a triple that contains all the above terms.
        $triple = new Triple($subject, $predicate, $object);
        // Create an insert statement.
        $insertStatement = $sparQlClient->insert($triple);
        // Perform the update.
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
        // Add the 'schema' namespace.
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
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
        // Create a delete statement.
        $deleteStatement = $sparQlClient->delete($tripleToDelete)->where([$tripleToFilter]);
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
        // Add the 'schema' namespace.
        $sparQlClient->setExtraNamespaces(['schema' => 'http://schema.org/']);
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
        // Create a replace statement.
        $replaceStatement = $sparQlClient
            ->replace($tripleToReplace)
            ->with($replacementTriple)
            ->where([$tripleToReplace]);
        // Perform the update.
        $sparQlClient->execute($replaceStatement);
    }
}
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
will throw an InvalidArgumentException because the prefix contains illegal
characters.

```php
<?php

use \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

$subject = new PrefixedIri('‿schema', 'headline');
```

Namespaces are validated, both when defined in configuration
(via sparql_client.yml) or added dynamically in code.

Rudimentary validation of statements is also supported. For example, the
below statement will throw an InvalidArgumentException because the
'where' clause does not contain any of the requested variables.

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
        // Throws an InvalidArgumentException.
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

// Throws an InvalidArgumentException because the argument data types do not match.
new Filter(new NotEqual($object1, $object3));
```

### Upload files

To upload a file containing a vocabulary, use the `upload` method.

```php
<?php

namespace App\Controller;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class MyController extends AbstractController
{
    public function view(Request $request, SparQlClientInterface $sparQlClient)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        if ($sparQlClient->upload($file, 'application/rdf+xml')) {
            dump('File uploaded!');
        }
    }
}
```

You must specify the content type of the file as the second parameter.
A default content type of `application/x-turtle` is assumed.

# SHACL validator

To use the validator service, define the SHACL validator endpoint.
You can retrieve the SHACL client as a service.
Insert, delete, replace and construct statements can be validated.

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
        $statement = $sparQlClient
            ->insert($triple)
            ->where([$triple]);
        if ($shaclClient->validate($statement)) {
            dump('statement is valid!');
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

# Planned features

- Support for graphs, including named graphs and management operations.
- Support for DESCRIBE statements.
- Support for UNION.
- Support for empty prefixes.
- Validation of typed literals using their datatype.
- Improve error reporting from triplestores.
- Possibly return more meaningful data from INSERT and DELETE statement executions.
- Expand statement validation.
