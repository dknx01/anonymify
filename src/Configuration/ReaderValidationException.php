<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration;

class ReaderValidationException extends \Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct(sprintf('Reader Validation Exception: %s', $message));
    }
}
