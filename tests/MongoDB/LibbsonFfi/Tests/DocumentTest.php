<?php

namespace MongoDB\LibbsonFfi\Tests;

use Mongodb\LibbsonFfi\Document;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{
    public function testFromBSON(): void
    {
        $bson = hex2bin('1200000002666f6f00040000006261720000');
        $document = Document::fromBSON($bson);
        self::assertSame($bson, (string) $document);
    }
}
