<?php
include_once('BaseParser.php');
include_once('BasicMathOperations.php');

/**
 * SpreadSheetParser.php
 *
 * @company StitchLabs
 * @project kenny
 *
 * @author  kchan
 */

/**
 * Class SpreadSheetParser
 */

class SpreadSheetParser implements BaseParser, BasicMathOperations
{
    protected $input_path = '';
    protected $output_path = '';
    protected $mapping = [];
    protected $valid_operators;

    public function __construct($input_path, $output_path)
    {
        $this->input_path = $input_path;
        $this->output_path = $output_path;
        $this->valid_operators = ['+', '-', '*', 'x', '/'];
    }

    public function parseCsv()
    {
        if (($handle = fopen($this->input_path, "r")) !== false) {

            $output_handle  = fopen($this->output_path, "w");

            //TODO:: OPTIMIZE METHOD TO GENERATE NECESSARY HEADERS

            //create a mapping so that we can support {Column}{Row} notation
            $header_columns = $this->setUpColumnHeader();

            $row_index = 1;
            while (($row_data = fgetcsv($handle)) !== false) {
                //create mapping
                foreach ($row_data as $key => $value) {
                    $header                              = $header_columns[$key];
                    $map_key                             = $header . $row_index;
                    $this->mapping[$row_index][$map_key] = $value;
                }
                $row_index++;
            }

            //loop through row and format and evaluate cell tokens
            foreach ($this->mapping as $k => $map_row) {
                $output = [];
                foreach ($map_row as $map_val) {
                    $stack = [];
                    if (strlen($map_val)) {
                        $cell = $this->formatCellData($map_val);
                        $this->evaluateCell($cell, $stack);
                    } else {
                        $stack[] = '';
                    }
                    $output[] = $stack[0];
                }

                //TODO:: could refactor to create CSV one time
                fputcsv($output_handle, $output);
            }

            fclose($handle);
            fclose($output_handle);
        } else {
            throw new Exception;
        }


    }

    public function evaluateCell($cell, &$stack)
    {
        foreach ($cell as $part) {
            if (is_numeric($part)) {
                array_unshift($stack, $part);
            } elseif (in_array(trim($part), $this->getValidOperators())) {
                if (count($stack) < 2) {
                    $stack = ['#ERR'];
                    break;
                } else {
                    $first_operand  = array_shift($stack);
                    $second_operand = array_shift($stack);

                    switch ($part) {
                        case "+":
                            $result = $this->sumIt($first_operand, $second_operand);
                            break;
                        case "-":
                            $result = $this->subtractIt($first_operand,$second_operand);
                            break;
                        case "*":
                            $result = $this->multiplyIt($first_operand,$second_operand);
                            break;
                        case "/":
                            $result = $this->divideIt($first_operand,$second_operand);
                            break;

                    }
                    array_unshift($stack, $result);

                }
            } else {
                //Are we trying to access a filed by {Column}{Row} notation?

                //check cell token if there are any numbers
                //this will tell us the row to check in the mapping
                $pattern = '/(\d+)/';
                preg_match($pattern, $part, $matches);

                //check the row in the mapping
                if (!empty($matches)) {
                    $row = $matches[0];
                    if (isset($this->mapping[$row]) && isset($this->mapping[$row][$part])) {
                        $cell = $this->formatCellData($this->mapping[$row][$part]);

                        //recurse through the cell tokens
                        $this->evaluateCell($cell, $stack);
                    } else {
                        $stack = ['#ERR'];
                        break;
                    }

                } else {
                    $stack = ['#ERR'];
                    break;
                }
            }

        }

    }

    public function setUpColumnHeader()
    {
        //TODO: REFACTOR TO SUPPORT 26 cubed
        $letters  = range('A', 'Z');
        $row_data = range(1, 676); //26 squared

        $header     = [];
        $letter_cnt = count($letters);

        foreach ($row_data as $key => $number) {

            if ($key >= $letter_cnt) {
                $prefix = floor($key / $letter_cnt);

                //TODO:: HANDLE PREFIX GREATER THAN LETTER COUNT
                $offset = $letter_cnt * $prefix;
                $letter_index  = $key - $offset;
                $header[] = $letters[$prefix - 1] . $letters[$letter_index];


            } else {

                $header[] = $letters[$key];
            }

        }

        return $header;

    }

    /**
     * @return mixed
     */
    public function getValidOperators()
    {
        return $this->valid_operators;
    }

    /**
     * @param mixed $valid_operators
     */
    public function setValidOperators($valid_operators)
    {
        $this->valid_operators = $valid_operators;
    }

    /**
     * @param $first_operand
     * @param $second_operand
     *
     * @return mixed
     */
    public function sumIt($first_operand, $second_operand)
    {
        return $first_operand + $second_operand;
    }

    /**
     * @param $first_operand
     * @param $second_operand
     *
     * @return mixed
     */
    public function subtractIt($first_operand, $second_operand)
    {
        return $first_operand - $second_operand;
    }

    /**
     * @param $first_operand
     * @param $second_operand
     *
     * @return mixed
     */
    public function multiplyIt($first_operand, $second_operand)
    {
        return $first_operand * $second_operand;
    }

    /**
     * @param $first_operand
     * @param $second_operand
     *
     * @return float
     */
    public function divideIt($first_operand, $second_operand)
    {
        return $first_operand / $second_operand;
    }


    /**
     * @param $data string
     *
     * @return array
     */
    private function formatCellData($data)
    {
        $pattern = '/(\s+)/';

        //remove all extra spaces
        $map_val = preg_replace($pattern, ' ', trim($data));

        return explode(' ', $map_val);

    }

}