<?php
include_once(__DIR__ . '/parser/SpreadSheetParser.php');


$input_path = __DIR__ . '/input.csv';
$output_path = __DIR__ . '/output.csv';

$parser = new SpreadSheetParser($input_path, $output_path);
$parser->parseCsv();

echo "Done";
