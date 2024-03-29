<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

/**
 * The Number Constraints, validates an number against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class Number extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
		//兼容(Royal Story)
		if (is_string($element)) {
			if (isset($schema->type) && 'integer' === $schema->type) {
				$element = intval($element);
			} else {
				if (strpos($element, '.') === false) {
					$element = intval($element);
				} else {
					$element = floatval($element);
				}
			}
		}

        // Verify minimum
        if (isset($schema->exclusiveMinimum)) {
            if (isset($schema->minimum)) {
                if ($schema->exclusiveMinimum && $element === $schema->minimum) {
                    $this->addError($path, "must have a minimum value greater than boundary value of " . $schema->minimum);
                } else if ($element < $schema->minimum) {
                    $this->addError($path, "must have a minimum value of " . $schema->minimum);
                }
            } else {
                $this->addError($path, "use of exclusiveMinimum requires presence of minimum");
            }
        } else if (isset($schema->minimum) && $element < $schema->minimum) {
            $this->addError($path, "must have a minimum value of " . $schema->minimum);
        }

        // Verify maximum
        if (isset($schema->exclusiveMaximum)) {
            if (isset($schema->maximum)) {
                if ($schema->exclusiveMaximum && $element === $schema->maximum) {
                    $this->addError($path, "must have a maximum value less than boundary value of " . $schema->maximum);
                } else if ($element > $schema->maximum) {
                    $this->addError($path, "must have a maximum value of " . $schema->maximum);
                }
            } else {
                $this->addError($path, "use of exclusiveMaximum requires presence of maximum");
            }
        } else if (isset($schema->maximum) && $element > $schema->maximum) {
            $this->addError($path, "must have a maximum value of " . $schema->maximum);
        }

        // Verify divisibleBy - Draft v3
        if (isset($schema->divisibleBy) && $this->fmod($element, $schema->divisibleBy) != 0) {
            $this->addError($path, "is not divisible by " . $schema->divisibleBy);
        }

        // Verify multipleOf - Draft v4
        if (isset($schema->multipleOf) && $this->fmod($element, $schema->multipleOf) != 0) {
            $this->addError($path, "must be a multiple of " . $schema->multipleOf);
        }

        $this->checkFormat($element, $schema, $path, $i);
    }

    private function fmod($number1, $number2)
    {
        $modulus = fmod($number1, $number2);
        $precision = abs(0.0000000001);
        $diff = (float)($modulus - $number2);

        if (-$precision < $diff && $diff < $precision) {
            return 0.0;
        }

        $decimals1 = mb_strpos($number1, ".") ? mb_strlen($number1) - mb_strpos($number1, ".") - 1 : 0;
        $decimals2 = mb_strpos($number2, ".") ? mb_strlen($number2) - mb_strpos($number2, ".") - 1 : 0;

        return (float)round($modulus, max($decimals1, $decimals2));
    }
}
