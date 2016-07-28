<?php

/**
 * BaseParser.php
 *
 * @company StitchLabs
 * @project kenny
 *
 * @author  kchan
 */

/**
 * Interface BaseParser
 */
interface BaseParser
{
    public function parseCsv();

    public function evaluateCell($cell, &$stack);

    public function setUpColumnHeader();
}