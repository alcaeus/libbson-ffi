<?php

namespace Mongodb\LibbsonFfi\Benchmark;

use Generator;
use MongoDB\BSON\Document as NativeDocument;
use Mongodb\LibbsonFfi\Document as FFIDocument;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Warmup;
use function hex2bin;

final class CreateDocumentBench
{
    private const DOCUMENT = '1200000002666f6f00040000006261720000';

    public function provideLists(): Generator
    {
        yield 'Native' => ['class' => NativeDocument::class];
        yield 'FFI' => ['class' => FFIDocument::class];
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchFromBson(array $params): void
    {
        ['class' => $class] = $params;

        $class::fromBson(hex2bin(self::DOCUMENT));
    }
}
