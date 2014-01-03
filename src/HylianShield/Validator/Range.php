<?php
/**
 * Validate value ranges.
 *
 * @package HylianShield
 * @subpackage Validator
 * @copyright 2013 Jan-Marten "Joh Man X" de Boer
 */

namespace HylianShield\Validator;

use \InvalidArgumentException;

/**
 * Range.
 */
abstract class Range extends \HylianShield\Validator
{
    /**
     * The minimum length of the value.
     *
     * @var integer|float $minLength
     */
    protected $minLength = 0;

    /**
     * The maximum length of the value.
     *
     * @var integer|float $maxLength
     */
    protected $maxLength = 0;

    /**
     * Define the ability to overload the range while constucting the object.
     *
     * @var boolean $canOverloadRange
     */
    protected $canOverloadRange = true;

    /**
     * The type.
     *
     * @var string $type
     */
    protected $type = 'range';

    /**
     * The validator.
     *
     * @var callable $validator
     */
    protected $validator = 'is_scalar';

    /**
     * The callable to return the length of the value.
     *
     * @var callable $lengthCheck
     */
    protected $lengthCheck = 'intval';

    /**
     * Check the properties of the validator to ensure a perfect implementation.
     *
     * @param integer $minLength the minimum length of the value
     * @param integer $maxLength the maximum length of the value
     * @throws \InvalidArgumentException when either minLength of maxLength is not an integer or float
     */
    final public function __construct($minLength = null, $maxLength = null)
    {
        if ($this->canOverloadRange === false || !isset($minLength)) {
            $minLength = $this->minLength;
        } elseif (isset($minLength)) {
            $this->minLength = $minLength;
        }

        if ($this->canOverloadRange === false || !isset($maxLength)) {
            $maxLength = $this->maxLength;
        } elseif (isset($maxLength)) {
            $this->maxLength = $maxLength;
        }

        if (!(is_int($minLength) || is_float($minLength))
            || !(is_int($maxLength) || is_float($maxLength))
        ) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                'Min and max length should be of type integer or type float.'
            );
            // @codeCoverageIgnoreEnd
        }

        $validator = $this->createValidator();
        if (!is_callable($validator)) {
            throw new LogicException('Validator should be callable!');
        }

        if (!is_callable($this->lengthCheck)) {
            // @codeCoverageIgnoreStart
            throw new LogicException('Length checker should be callable!');
            // @codeCoverageIgnoreEnd
        }

        $lengthCheck = $this->lengthCheck;
        $lastResult = $this->lastResult;

        $this->validator = function (
            $value
        ) use (
            $validator,
            $minLength,
            $maxLength,
            $lengthCheck,
            $lastResult
        ) {
            // Check if the basic validation validates.
            $valid = call_user_func_array($validator, array($value));

            // Check if the minimum length validates.
            $valid = $valid && (
                $minLength === 0
                || call_user_func_array($lengthCheck, array($value)) >= $minLength
            );

            // Check if the maximum length validates.
            $valid = $valid && (
                $maxLength === 0
                || call_user_func_array($lengthCheck, array($value)) <= $maxLength
            );

            $lastResult = $valid;

            return $valid;
        };
    }

    /**
     * Return the current validator or overload to create a new one.
     *
     * @return callable
     */
    protected function createValidator() {
        return $this->validator;
    }

    /**
     * Return an indentifier.
     *
     * @return string
     */
    public function __tostring()
    {
        return "{$this->type}:{$this->minLength},{$this->maxLength}";
    }
}
