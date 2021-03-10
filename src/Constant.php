<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient;

class Constant
{
    /**
     * Regular expressions
     *
     * @see https://www.w3.org/TR/sparql11-query/.
     */

    /**
     * @see https://www.w3.org/TR/sparql11-query/#rPN_CHARS_BASE.
     */
    const PN_CHARS_BASE = '[A-Z]|[a-z]|[\x{00C0}-\x{00D6}]|[\x{00D8}-\x{00F6}]|[\x{00F8}-\x{02FF}]|[\x{0370}-\x{037D}]|[\x{037F}-\x{1FFF}]|[\x{200C}-\x{200D}]|[\x{2070}-\x{218F}]|[\x{2C00}-\x{2FEF}]|[\x{3001}-\x{D7FF}]|[\x{F900}-\x{FDCF}]|[\x{FDF0}-\x{FFFD}]|[\x{10000}-\x{EFFFF}]';

    /**
     * @see https://www.w3.org/TR/sparql11-query/#rPN_CHARS_U.
     */
    const PN_CHARS_U = self::PN_CHARS_BASE . '|[_]';

    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#rPN_CHARS.
     */
    const PN_CHARS = self::PN_CHARS_U . '|-|[0-9]|\x{00B7}|[\x{0300}-\x{036F}|[\x{203F}-\x{2040}]';

    /**
     * @see https://www.w3.org/TR/sparql11-query/#rVARNAME.
     */
    const VARNAME = '^(' . self::PN_CHARS_U . '|[0-9])(' . self::PN_CHARS_U . '|[0-9]|\x{00B7}|[\x{0300}-\x{036F}]|[\x{203F}-\x{2040}])*$';

    /**
     * Triple quotes and triple citation marks are not allowed, as they are used to denote beginning and end of literal values.
     * @see https://www.w3.org/TR/sparql11-query/#QSynLiterals.
     */
    const LITERAL = '^(?!.*("""|\'\'\')).*$';

    /**
     * @see https://tools.ietf.org/html/rfc3066#section-2.1.
     */
    const LANGUAGE_TAG = '^[a-z]{2,3}(?:-[a-z]{2,3}(?:-[a-z]{4})?)?$';

    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-URI-reference.
     */
    const CONTROL_CHARACTERS = '[\x{00}-\x{1F}]|[\x{7F}-\x{9F}]';

    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#rPN_PREFIX.
     */
    const PN_PREFIX = '^((' . self::PN_CHARS_BASE . ')((' . self::PN_CHARS . '|\.)*(' . self::PN_CHARS . '))?)$';

    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#rPN_LOCAL.
     */
    const PN_LOCAL = '^(' . self::PN_CHARS_U . '|:|[0-9])((' . self::PN_CHARS . '|\.)*(' . self::PN_CHARS . '))?$';

    /**
     * @see https://tools.ietf.org/html/rfc8141.
     */
    const URN = '^urn:[a-z0-9][a-z0-9-]{0,31}:[a-z0-9()+,\-.:=@;$_!*\'%\/?#]+$';

    /**
     * UUID namespaces
     *
     * @see https://en.wikipedia.org/wiki/Universally_unique_identifier#Versions_3_and_5_(namespace_name-based).
     */

    /**
     * Namespace used for storing values in cache.
     */
    const NAMESPACE_CACHE = '65921776-7f6d-11eb-8f42-17ee83e595f7';

    /**
     * Namespaces
     */

    /**
     * Default W3C namespaces.
     */
    const W3C_NAMESPACES = [
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'owl' => 'http://www.w3.org/2002/07/owl#',
        'skos' => 'http://www.w3.org/2004/02/skos/core#',
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
    ];
}
