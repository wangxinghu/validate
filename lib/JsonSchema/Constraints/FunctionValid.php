<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

class FunctionValid extends Constraint
{
	/**
	 * (Royal Story)
	 * {@inheritDoc}
	 */
	public function check($element, $schema = null, $path = null, $i = null)
	{
		// Only validate enum if the attribute exists
		$function = '$function_valid';
		if(!isset($schema->$function) || $element instanceof Undefined) {
			return;
		}
		$oFunction = $schema->$function;
		if (!is_callable($oFunction) && !is_array($oFunction))  {
			return;
		}

		if(is_array($oFunction)) {
			$lFunction = $oFunction;
		}else{
			$lFunction[] = $oFunction;
		}
		foreach($lFunction as $oFunction) {
			if(($str = $oFunction($element)) !== true) {
				if(!is_string($str)) {
					$this->addError($path, 'funtion return must be string');
				}else{
					$this->addError($path, $str);
				}
			}
		}

	}
}
