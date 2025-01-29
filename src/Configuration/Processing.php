<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration;

use App\Configuration\Obfuscate\Config;

readonly class Processing
{
    /**
     * @param string[]                                $truncate
     * @param array<string, array<array-key, string>> $json
     * @param array<string, array<array-key, string>> $binaryEmpty
     * @param array<string, string>                   $staticText
     * @param string[]                                $scripts
     * @param array<string, array<string, string>>    $tables
     * @param array<string, string[]|null>            $defaultMasking
     */
    public function __construct(
        public array $truncate,
        public array $json,
        public array $binaryEmpty,
        public array $staticText,
        public array $scripts,
        public array $tables,
        public array $defaultMasking,
        public Config $obfuscate,
    ) {
    }

    public static function fromStdClass(\stdClass $obj): Processing
    {
        return new self(
            $obj->truncate,
            (array) $obj->json,
            (array) $obj->binary_empty ?: [],
            (array) $obj->static_text ?: [],
            $obj->scripts ?: [],
            (array) $obj->tables ?: [],
            (array) $obj->default_masking,
            Config::fromArray((array) $obj->obfuscate),
        );
    }
}
