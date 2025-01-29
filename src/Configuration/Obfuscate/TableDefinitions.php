<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration\Obfuscate;

class TableDefinitions
{
    /**
     * @var array<string, string[]>
     */
    private array $tables = [];

    /**
     * @param Table[] $tables
     */
    public function __construct(array $tables)
    {
        foreach ($tables as $table => $columns) {
            $this->tables[$table] = array_keys($columns->columns);
        }
    }

    public function hasColumnDefinition(string $table, string $column): bool
    {
        return in_array($column, $this->tables[$table] ?? [], true);
    }
}
