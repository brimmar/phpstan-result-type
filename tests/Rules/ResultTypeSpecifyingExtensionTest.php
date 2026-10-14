<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use PHPStan\Testing\TypeInferenceTestCase;

class ResultTypeSpecifyingExtensionTest extends TypeInferenceTestCase
{
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/data/result-narrowing.php');
    }

    /**
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon'];
    }
}
