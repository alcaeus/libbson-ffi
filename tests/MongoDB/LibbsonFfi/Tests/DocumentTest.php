<?php

namespace MongoDB\LibbsonFfi\Tests;

use Generator;
use Mongodb\LibbsonFfi\Document;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function hex2bin;

final class DocumentTest extends TestCase
{
    public function testFromBSON(): void
    {
        $bson = hex2bin('1200000002666f6f00040000006261720000');
        $document = Document::fromBSON($bson);
        self::assertSame($bson, (string) $document);
    }

    public static function fromPHPSamples(): Generator
    {
        yield 'List as array' => [
            'expected' => hex2bin('1a00000010300001000000103100020000001032000300000000'),
            'php' => [1, 2, 3],
        ];

        yield 'Document as array' => [
            'expected' => hex2bin('1200000002666f6f00040000006261720000'),
            'php' => ['foo' => 'bar'],
        ];

        yield 'List as object' => [
            'expected' => hex2bin('1a00000010300001000000103100020000001032000300000000'),
            'php' => (object) [1, 2, 3],
        ];

        yield 'Document as object' => [
            'expected' => hex2bin('1200000002666f6f00040000006261720000'),
            'php' => (object) ['foo' => 'bar'],
        ];
    }

    #[DataProvider('fromPHPSamples')]
    public function testFromPHP($expected, $php): void
    {
        $this->assertSame($expected, (string) Document::fromPHP($php));
    }

    public function testHas(): void
    {
        $document = Document::fromPHP(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame(true, $document->has('foo'));
        $this->assertSame(true, $document->has('bar'));
        $this->assertSame(false, $document->has('baz'));
    }
}
