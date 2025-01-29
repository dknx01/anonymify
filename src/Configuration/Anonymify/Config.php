<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration\Anonymify;

readonly class Config
{
    public TableDefinitions $tableDefinitions;

    public function __construct(
        /** @var Definition[] */
        public array $general,
        /** @var Table[] */
        public array $tables,
    ) {
        $this->tableDefinitions = new TableDefinitions($this->tables);
    }

    /**
     * @param array<string, array<string, mixed>> $config
     */
    public static function fromArray(array $config): self
    {
        $general = [];
        foreach ($config['general'] as $name => $value) {
            $general[$name] = new Definition($name, $value);
        }
        $tables = [];
        foreach ($config['tables'] as $name => $value) {
            $tables[$name] = new Table($name, (array) $value);
        }

        return new self(
            $general,
            $tables,
        );
    }
}
