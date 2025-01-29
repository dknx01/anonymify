<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\PreProcessing\Task;

use App\Configuration\Processing;

interface Task
{
    public function run(Processing $config): void;

    public static function getPriority(): int;
}
