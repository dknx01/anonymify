<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Configuration;

use JsonSchema\Validator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

readonly class Reader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/data/pre-processing.schema.json')] private string $schemaPath,
        private Validator $validator = new Validator(),
    ) {
    }

    /**
     * @throws \JsonException
     * @throws ReaderValidationException
     */
    public function readConfig(string $configFile): Processing
    {
        $fs = new Filesystem();

        $data = json_decode($fs->readFile($configFile), false, 512, JSON_THROW_ON_ERROR);
        $this->validator->validate(
            $data,
            (object) ['$ref' => 'file://'.realpath($this->schemaPath)]
        );

        if (!$this->validator->isValid()) {
            $errors = [];
            foreach ($this->validator->getErrors() as $error) {
                $errors[] = printf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new ReaderValidationException(implode("\n", $errors));
        }

        return Processing::fromStdClass($data);
    }
}
