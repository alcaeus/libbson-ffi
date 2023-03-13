<?php

namespace Mongodb\LibbsonFfi\Benchmark;

use Exception;
use Generator;
use MongoDB\BSON\Document as NativeDocument;
use Mongodb\LibbsonFfi\Document as FFIDocument;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Warmup;
use function array_combine;
use function array_map;
use function hex2bin;
use function range;
use function uniqid;

final class DocumentBench
{
    private const DOCUMENT = '1200000002666f6f00040000006261720000';

    private const OBJECT = ['foo' => 'bar'];

    private static array $document = [];
    private static array $hugeDocument = [];

    private static function getHugeObject(): object
    {
        return (object) array_combine(
            array_map(uniqid(...), range(0, 10000)),
            array_map(uniqid(...), range(0, 10000)),
        );
    }

    private static function getHugeDocument(string $class): object
    {
        return self::$hugeDocument[$class] ??= $class::fromPHP(self::getHugeObject());;
    }

    private static function getDocument(string $class): object
    {
        return self::$document[$class] ??= $class::fromPHP(['foo' => 'bar']);
    }

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

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchFromPHP(array $params): void
    {
        ['class' => $class] = $params;

        $class::fromPHP(self::OBJECT);
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchFromPHPHuge(array $params): void
    {
        ['class' => $class] = $params;

        $class::fromPHP(self::getHugeObject());
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchHas(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getDocument($class);
        $object->has('bar');
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchHasHuge(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getHugeDocument($class);
        $object->has('bar');
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchGet(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getDocument($class);
        $object->get('foo');
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchGetHuge(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getHugeDocument($class);
        try {
            $object->get('bar');
        } catch (Exception) {
            // Silence exception, we're expecting one
        }
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchGetIterator(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getDocument($class);
        $object->getIterator();
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchGetIteratorHuge(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getHugeDocument($class);
        $object->getIterator();
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchIterate(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getDocument($class);
        foreach ($object->getIterator() as $key => $value) {}
    }

    #[ParamProviders('provideLists')]
    #[Warmup(1)]
    public function benchIterateHuge(array $params): void
    {
        ['class' => $class] = $params;

        $object = self::getHugeDocument($class);
        foreach ($object->getIterator() as $key => $value) {}
    }
}
