<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use JsonSchema\Uri\UriRetriever;

/**
 * Take in an object that's a JSON schema and take care of all $ref references
 *
 * @author Tyler Akins <fidian@rumkin.com>
 * @see    README.md
 */
class RefResolver
{
    /**
     * HACK to prevent too many recursive expansions.
     * Happens e.g. when you want to validate a schema against the schema
     * definition.
     *
     * @var integer
     */
    protected static $depth = 0;

    /**
     * maximum references depth
     * @var integer
     */
    public static $maxDepth = 8;

    /**
     * @var UriRetrieverInterface
     */
    protected $uriRetriever = null;

	private $lFunction = array();

    public $notIncludeFunction = false;

	/**
	 * 是否是为了展示html
	 * @var bool
	 */
	private $bShowHtml = false;

    /**
     * @param UriRetriever $retriever
     */
    public function __construct($retriever = null)
    {
        $this->uriRetriever = $retriever;
    }

	/**
	 * @return array
	 */
	public function getFunctions() {
		return $this->lFunction;
	}

	/**
	 * @param $bShowHtml
	 */
	public function setShowHtml($bShowHtml) {
		$this->bShowHtml = $bShowHtml;
	}

    /**
     * Retrieves a given schema given a ref and a source URI
     *
     * @param  string $ref       Reference from schema
     * @param  string $sourceUri URI where original schema was located
	 * @param  string $functionDir
     * @return object            Schema
     */
    public function fetchRef($ref, $sourceUri, $functionDir)
    {
        $retriever  = $this->getUriRetriever();
        $jsonSchema = $retriever->retrieve($ref, $sourceUri);
        $this->resolve($jsonSchema, null, $functionDir);

        return $jsonSchema;
    }

    /**
     * Return the URI Retriever, defaulting to making a new one if one
     * was not yet set.
     *
     * @return UriRetriever
     */
    public function getUriRetriever()
    {
        if (is_null($this->uriRetriever)) {
            $this->setUriRetriever(new UriRetriever);
        }

        return $this->uriRetriever;
    }

    /**
     * Resolves all $ref references for a given schema.  Recurses through
     * the object to resolve references of any child schemas.
     *
     * The 'format' property is omitted because it isn't required for
     * validation.  Theoretically, this class could be extended to look
     * for URIs in formats: "These custom formats MAY be expressed as
     * an URI, and this URI MAY reference a schema of that format."
     *
     * The 'id' property is not filled in, but that could be made to happen.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
	 * @param string $functionDir
     */
    public function resolve($schema, $sourceUri = null, $functionDir = null)
    {
        if (self::$depth > self::$maxDepth) {
            return;
        }
        ++self::$depth;

        if (! is_object($schema)) {
            --self::$depth;
            return;
        }

        if (null === $sourceUri && ! empty($schema->id)) {
            $sourceUri = $schema->id;
        }

        // Resolve $ref first
        $this->resolveRef($schema, $sourceUri, $functionDir);

		// Resolve $function (Royal Story)
		$this->resolveFunctionRef($schema, $functionDir);

		$this->resolveFunctionValid($schema, $functionDir);

        // These properties are just schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('additionalItems', 'additionalProperties', 'extends', 'items') as $propertyName) {
            $this->resolveProperty($schema, $propertyName, $sourceUri, $functionDir);
        }

        // These are all potentially arrays that contain schema objects
        // eg.  type can be a value or an array of values/schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('disallow', 'extends', 'items', 'type', 'allOf', 'anyOf', 'oneOf') as $propertyName) {
            $this->resolveArrayOfSchemas($schema, $propertyName, $sourceUri, $functionDir);
        }

        // These are all objects containing properties whose values are schemas
        foreach (array('dependencies', 'patternProperties', 'properties') as $propertyName) {
            $this->resolveObjectOfSchemas($schema, $propertyName, $sourceUri, $functionDir);
        }

        --self::$depth;
    }

    /**
     * Given an object and a property name, that property should be an
     * array whose values can be schemas.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
	 * @param string $functionDir
     */
    public function resolveArrayOfSchemas($schema, $propertyName, $sourceUri, $functionDir)
    {
        if (! isset($schema->$propertyName) || ! is_array($schema->$propertyName)) {
            return;
        }

        foreach ($schema->$propertyName as $possiblySchema) {
            $this->resolve($possiblySchema, $sourceUri, $functionDir);
        }
    }

    /**
     * Given an object and a property name, that property should be an
     * object whose properties are schema objects.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
	 * @param string $functionDir
     */
    public function resolveObjectOfSchemas($schema, $propertyName, $sourceUri, $functionDir)
    {
        if (! isset($schema->$propertyName) || ! is_object($schema->$propertyName)) {
            return;
        }
        foreach (get_object_vars($schema->$propertyName) as $sKey =>$possiblySchema) {
			//兼容properties key 有 $ref 的情况 (Royal Story)
			if($sKey === '$ref') {
				$this->resolve($schema->$propertyName, $sourceUri, $functionDir);
				unset($schema->$propertyName->id);
			}else{
				$this->resolve($possiblySchema, $sourceUri, $functionDir);
			}
        }
    }

    /**
     * Given an object and a property name, that property should be a
     * schema object.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
	 * @param string $functionDir
     */
    public function resolveProperty($schema, $propertyName, $sourceUri, $functionDir)
    {
        if (! isset($schema->$propertyName)) {
            return;
        }

        $this->resolve($schema->$propertyName, $sourceUri, $functionDir);
    }

    /**
     * Look for the $ref property in the object.  If found, remove the
     * reference and augment this object with the contents of another
     * schema.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
	 * @param string $functionDir
     */
    public function resolveRef($schema, $sourceUri, $functionDir)
    {
        $ref = '$ref';

        if (empty($schema->$ref)) {
            return;
        }

        $refSchema = $this->fetchRef($schema->$ref, $sourceUri, $functionDir);
        unset($schema->$ref);

        // Augment the current $schema object with properties fetched
        foreach (get_object_vars($refSchema) as $prop => $value) {
            $schema->$prop = $value;
        }
    }

    /**
     * Set URI Retriever for use with the Ref Resolver
     *
     * @param UriRetriever $retriever
     * @return $this for chaining
     */
    public function setUriRetriever(UriRetriever $retriever)
    {
        $this->uriRetriever = $retriever;

        return $this;
    }

	/**
	 * (Royal Story)
	 * @param object $schema    JSON Schema to flesh out
	 * @param string $functionDir URI where this schema was located
	 */
	private function resolveFunctionRef($schema, $functionDir)
	{
        if($this->notIncludeFunction) {
            return;
        }
		$function = '$function_ref';

		if (empty($schema->$function)) {
			return;
		}

		$funSchema = $this->fetchFunctionRef($schema->$function, $functionDir);
		unset($schema->$function);
		if($this->bShowHtml) {
			$schema->function_id = $funSchema;
		}else{
			foreach (get_object_vars($funSchema) as $prop => $value) {
				$schema->$prop = $value;
			}
		}

	}

	/**
	 * Retrieves a given schema given a function and a source URI (Royal Story)
	 *
	 * @param  string $function       Function from schema
	 * @param  string $functionDir URI where original schema was located
	 * @return array            Schema
	 */
	private function fetchFunctionRef($function, $functionDir)
	{
		if(!isset($this->lFunction[$function])) {
			$this->lFunction[$function] = require rtrim($functionDir, '/') . '/' . $function . '.php';
			$rs = $this->lFunction[$function]();
			$this->lFunction[$function] = json_decode(json_encode($rs));
			unset($rs);
		}
		if($this->bShowHtml) {
			return $function;
		}else{
			return $this->lFunction[$function];
		}
	}

	/**
	 * (Royal Story)
	 * @param object $schema    JSON Schema to flesh out
	 * @param string $functionDir URI where this schema was located
	 */
	private function resolveFunctionValid($schema, $functionDir)
	{
        if($this->notIncludeFunction) {
            return;
        }
		$function = '$function_valid';

		if (empty($schema->$function)) {
			return;
		}
		if(is_string($schema->$function)) {
			$schema->$function = $this->fetchFunctionValid($schema->$function, $functionDir);
		}elseif(is_array($schema->$function)) {
			$lFunction = [];
			foreach($schema->$function as $sFunction) {
				$lFunction[] = $this->fetchFunctionValid($sFunction, $functionDir);
			}
			$schema->$function = $lFunction;
		}
	}

	/**
	 * Retrieves a given schema given a function and a source URI (Royal Story)
	 *
	 * @param  string $function       Function from schema
	 * @param  string $functionDir URI where original schema was located
	 * @return array            Schema
	 */
	private function fetchFunctionValid($function, $functionDir)
	{
		if(!isset($this->lFunction[$function])) {
			$oFunction = include rtrim($functionDir, '/') . '/' . $function . '.php';
			$this->lFunction[$function] = $oFunction;
		}
		return $this->lFunction[$function];
	}

    public function __destruct(){
        unset($this->lFunction);
    }

}
