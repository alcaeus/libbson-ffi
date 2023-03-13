<?php

namespace MongoDB\LibbsonFfi\Tests;

use Generator;
use MongoDB\BSON\ObjectId;
use Mongodb\LibbsonFfi\Document;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function hex2bin;
use function iterator_to_array;

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
        self::assertSame($expected, (string) Document::fromPHP($php));
    }

    public function testHas(): void
    {
        $document = Document::fromPHP(['foo' => 'bar', 'bar' => 'baz']);

        self::assertSame(true, $document->has('foo'));
        self::assertSame(true, $document->has('bar'));
        self::assertSame(false, $document->has('baz'));
    }

    public function testGet(): void
    {
        $document = Document::fromPHP(['foo' => 'bar', 'bar' => 'baz']);

        self::assertSame('bar', $document->get('foo'));
        self::assertSame('baz', $document->get('bar'));

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Could not find key "baz" in BSON data');
        $document->get('baz');
    }

    public function testGetDocument(): void
    {
        $object = [
            'document' => (object) [
                'foo' => 'bar',
            ],
        ];

        $document = Document::fromPHP($object);
        $value = $document->get('document');

        self::assertInstanceOf(Document::class, $value);
        self::assertSame('bar', $value->get('foo'));
    }

    public function testGetIterator(): void
    {
        $object = [
            'string' => 'bar',
            'int32' => 1,
            'int64' => 2**33,
            'double' => 3.14,
            'bool' => true,
            'null' => null,
            'oid' => new ObjectId(),
            'object' => (object) [
                'foo' => 'bar',
            ],
        ];

        $expected = [
            'object' => Document::fromPHP($object['object']),
        ] + $object;

        $document = Document::fromPHP($object);
        $iterator = $document->getIterator();

        self::assertEquals(
            $expected,
            iterator_to_array($iterator),
        );
    }
}
