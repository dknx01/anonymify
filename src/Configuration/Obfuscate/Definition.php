<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration\Obfuscate;

readonly class Definition
{
    public string $name;
    /**
     * @var string[]|int[]
     */
    public array $parameters;

    public function __construct(
        public string $column,
        string|\stdClass|null $value,
    ) {
        if (null === $value) {
            $this->name = 'default';
            $this->parameters = [];
        } elseif ($value instanceof \stdClass) {
            $this->parameters = $value->args;
            $this->name = $value->type;
        } else {
            $this->name = $value;
            $this->parameters = [];
        }
    }
}
