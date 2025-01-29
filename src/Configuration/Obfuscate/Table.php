<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration\Obfuscate;

readonly class Table
{
    /**
     * @var Definition[]
     */
    public array $columns;

    /**
     * @param array<string, string|\stdClass|null> $columns
     */
    public function __construct(
        public string $name,
        array $columns,
    ) {
        $definitions = [];
        foreach ($columns as $column => $def) {
            $definitions[$column] = new Definition($column, $def);
        }
        $this->columns = $definitions;
    }
}
