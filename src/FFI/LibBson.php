<?php

namespace Mongodb\LibbsonFfi\FFI;

use FFI;
use FFI\CData;

/** @internal */
final class LibBson
{
    private static FFI $ffi;

    private function __construct() {}

    public static function getFFI(): FFI
    {
        return self::$ffi ??= self::createFFI();
    }

    private static function createFFI(): FFI
    {
//        return FFI::load('/opt/homebrew/include/libbson-1.0/bson/bson.h');
        return FFI::load(__DIR__ . '/../bson.h');
    }

    public static function new($type, bool $owned = true, bool $persistent = false): ?CData
    {
        return self::getFFI()->new($type, $owned, $persistent);
    }

    public static function bson_reader_new_from_data(string $data): CData
    {
        return self::getFFI()->bson_reader_new_from_data($data, strlen($data));
    }

    public static function bson_reader_read(CData $reader, bool &$reached_eof): ?CData
    {
        return self::getFFI()->bson_reader_read($reader, FFI::addr(FFI::cast('bool', $reached_eof)));
    }

    public static function bson_get_data(CData $bson): string
    {
        return FFI::string(FFI::cast('const char*', self::getFFI()->bson_get_data($bson)), $bson->len);
    }

    public static function bson_copy(CData $bson): CData
    {
        return self::getFFI()->bson_copy($bson);
    }
}
