<?php

/*
 * Project: Anonymify
 * @copyright dknx01 (https://github.com/dknx01/anonymify)
 */

namespace App\Tests\Unit\Configuration;

use App\Configuration\Reader;
use App\Configuration\ReaderValidationException;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testReadConfigWithoutError(): void
    {
        $reader = new Reader(__DIR__.'/../../../data/pre-processing.schema.json');
        $reader->readConfig(__DIR__.'/../../../data/pre-processing.config.json');
        $this->expectNotToPerformAssertions();
    }

    public function testReadConfigWithErrors(): void
    {
        $this->expectException(ReaderValidationException::class);
        $reader = new Reader(__DIR__.'/../../../data/pre-processing.schema.json');
        $reader->readConfig(__DIR__.'/data/pre-processing_error.config.json');
        $this->expectNotToPerformAssertions();
    }
}
