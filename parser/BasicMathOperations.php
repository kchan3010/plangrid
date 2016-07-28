<?php
/**
 * BasicMathOperations.php
 *
 * @company StitchLabs
 * @project kenny
 *
 * @author  kchan
 */

/**
 * Interface BasicMathOperations
 */
interface BasicMathOperations
{
    public function sumIt($first_operand, $second_operand);

    public function subtractIt($first_operand, $second_operand);

    public function multiplyIt($first_operand, $second_operand);

    public function divideIt($first_operand, $second_operand);
}