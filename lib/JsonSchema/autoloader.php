<?php
$mapping = array(
	'JsonSchema\RefResolver' => __DIR__ . '/RefResolver.php',
	'JsonSchema\Validator' => __DIR__ . '/Validator.php',

	'JsonSchema\Uri\UriResolver' => __DIR__ . '/Uri/UriResolver.php',
	'JsonSchema\Uri\UriRetriever' => __DIR__ . '/Uri/UriRetriever.php',
	'JsonSchema\Uri\Retrievers\AbstractRetriever' => __DIR__ . '/Uri/Retrievers/AbstractRetriever.php',
	'JsonSchema\Uri\Retrievers\Curl' => __DIR__ . '/Uri/Retrievers/Curl.php',
	'JsonSchema\Uri\Retrievers\FileGetContents' => __DIR__ . '/Uri/Retrievers/FileGetContents.php',
	'JsonSchema\Uri\Retrievers\PredefinedArray' => __DIR__ . '/Uri/Retrievers/PredefinedArray.php',
	'JsonSchema\Uri\Retrievers\UriRetrieverInterface' => __DIR__ . '/Uri/Retrievers/UriRetrieverInterface.php',

	'JsonSchema\Exception\InvalidArgumentException' => __DIR__ . '/Exception/InvalidArgumentException.php',
	'JsonSchema\Exception\InvalidSchemaMediaTypeException' => __DIR__ . '/Exception/InvalidSchemaMediaTypeException.php',
	'JsonSchema\Exception\InvalidSourceUriException' => __DIR__ . '/Exception/InvalidSourceUriException.php',
	'JsonSchema\Exception\JsonDecodingException' => __DIR__ . '/Exception/JsonDecodingException.php',
	'JsonSchema\Exception\ResourceNotFoundException' => __DIR__ . '/Exception/ResourceNotFoundException.php',
	'JsonSchema\Exception\UriResolverException' => __DIR__ . '/Exception/UriResolverException.php',

	'JsonSchema\Constraints\Collection' => __DIR__ . '/Constraints/Collection.php',
	'JsonSchema\Constraints\Constraint' => __DIR__ . '/Constraints/Constraint.php',
	'JsonSchema\Constraints\ConstraintInterface' => __DIR__ . '/Constraints/ConstraintInterface.php',
	'JsonSchema\Constraints\Enum' => __DIR__ . '/Constraints/Enum.php',
	'JsonSchema\Constraints\Format' => __DIR__ . '/Constraints/Format.php',
	'JsonSchema\Constraints\FunctionValid' => __DIR__ . '/Constraints/FunctionValid.php',
	'JsonSchema\Constraints\Number' => __DIR__ . '/Constraints/Number.php',
	'JsonSchema\Constraints\Object' => __DIR__ . '/Constraints/Object.php',
	'JsonSchema\Constraints\Schema' => __DIR__ . '/Constraints/Schema.php',
	'JsonSchema\Constraints\String' => __DIR__ . '/Constraints/String.php',
	'JsonSchema\Constraints\Type' => __DIR__ . '/Constraints/Type.php',
	'JsonSchema\Constraints\Undefined' => __DIR__ . '/Constraints/Undefined.php',
);

spl_autoload_register(function ($class) use ($mapping) {
	if (isset($mapping[$class])) {
		require $mapping[$class];
	}
}, true, true);
