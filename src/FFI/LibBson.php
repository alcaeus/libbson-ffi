<?php

namespace Mongodb\LibbsonFfi\FFI;

use FFI;
use FFI\CData;
use MongoDB\BSON\MaxKey;
use MongoDB\BSON\MinKey;
use MongoDB\BSON\ObjectId;
use Mongodb\LibbsonFfi\Document;
use UnexpectedValueException;
use function sprintf;
use function var_export;

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

    public static function bson_iter_init(CData $iter, CData $bson): bool
    {
        return self::getFFI()->bson_iter_init($iter, $bson);
    }

    public static function bson_iter_find(CData $iter, string $key): bool
    {
        return self::getFFI()->bson_iter_find($iter, $key);
    }

    public static function bson_iter_find_w_len(CData $iter, string $key, int $keylen): bool
    {
        return self::getFFI()->bson_iter_find_w_len($iter, $key, $keylen);
    }

    public static function bson_iter_next(CData $iter): bool
    {
        return self::getFFI()->bson_iter_next($iter);
    }

    public static function bson_iter_key(CData $iter): string
    {
        return self::getFFI()->bson_iter_key($iter);
    }

    public static function bson_iter_type(CData $iter): int
    {
        return self::getFFI()->bson_iter_type($iter);
    }

    public static function bson_iter_as_int64(CData $iter): int
    {
        return self::getFFI()->bson_iter_as_int64($iter);
    }

    public static function bson_iter_utf8(CData $iter, int &$data_len): string
    {
        return self::getFFI()->bson_iter_utf8($iter, FFI::addr(FFI::cast('uint32_t', $data_len)));
    }

    public static function bson_iter_bool(CData $iter): bool
    {
        return self::getFFI()->bson_iter_bool($iter);
    }

    public static function bson_iter_double(CData $iter): float
    {
        return self::getFFI()->bson_iter_double($iter);
    }

    public static function bson_iter_oid(CData $iter): CData
    {
        return self::getFFI()->bson_iter_oid($iter);
    }

    public static function bson_iter_document(CData $iter, int &$document_len): mixed
    {
        $data = FFI::new('const uint8_t*');
        $length = FFI::new('uint32_t');

        self::getFFI()->bson_iter_document(
            $iter,
            FFI::addr($length),
            FFI::addr($data),
        );

        $document_len = $length->cdata;

        return new Document(self::getFFI()->bson_new_from_data($data, $document_len));
    }

    public static function bson_oid_to_string(CData $oid): string
    {
        $oidString = str_repeat('0', 24);
        self::getFFI()->bson_oid_to_string($oid, $oidString);

        return $oidString;
    }

    public static function bson_utf8_validate(string $utf8, int $utf8_len, bool $allow_null): bool
    {
        return self::getFFI()->bson_utf8_validate($utf8, $utf8_len, $allow_null);
    }

    public static function bson_iter_to_php(CData $iter): mixed
    {
        switch (LibBson::bson_iter_type($iter)) {
            case self::getFFI()->BSON_TYPE_UTF8:
                $data_len = 0;
                $value = LibBson::bson_iter_utf8($iter, $data_len);
                if (!LibBson::bson_utf8_validate($value, $data_len, true)) {
                    throw new UnexpectedValueException(sprintf('Detected corrupt BSON data at offset %d', $iter->err_off));
                }

                return $value;

            case self::getFFI()->BSON_TYPE_EOD:
                return null;

            case self::getFFI()->BSON_TYPE_DOCUMENT:
                $data_len = 0;
                return LibBson::bson_iter_document(
                    $iter,
                    $data_len,
                );
//                return Document::fromBSON(
//                    LibBson::bson_iter_document(
//                        $iter,
//                        $data_len,
//                    ),
//                );

            case self::getFFI()->BSON_TYPE_DOUBLE:
                return LibBson::bson_iter_double($iter);

            case self::getFFI()->BSON_TYPE_ARRAY:
                // TODO: Array
                break;
//                bson_iter_array(iter, &data_len, (const uint8_t**) &data);
//
//			object_init_ex(zv, php_phongo_packedarray_ce);
//			array_intern       = Z_PACKEDARRAY_OBJ_P(zv);
//			array_intern->bson = bson_new_from_data((const uint8_t*) data, data_len);
//			return true;

            case self::getFFI()->BSON_TYPE_BINARY:
                // TODO: Binary
//                bson_iter_binary(iter, (bson_subtype_t*) &subtype, &data_len, (const uint8_t**) &data);
//			php_phongo_bson_new_binary_from_binary_and_type(zv, data, data_len, subtype);
//			return true;

            case self::getFFI()->BSON_TYPE_UNDEFINED:
                // TODO: Undefined
//                object_init_ex(zv, php_phongo_undefined_ce);
//                return true;

            case self::getFFI()->BSON_TYPE_OID:
                return new ObjectId(LibBson::bson_oid_to_string(LibBson::bson_iter_oid($iter)));

            case self::getFFI()->BSON_TYPE_BOOL:
                return LibBson::bson_iter_bool($iter);

            case self::getFFI()->BSON_TYPE_DATE_TIME:
                // TODO: DateTime
//                php_phongo_bson_new_utcdatetime_from_epoch(zv, bson_iter_date_time(iter));
//                return true;

            case self::getFFI()->BSON_TYPE_NULL:
                return null;

            case self::getFFI()->BSON_TYPE_REGEX:
                // TODO: Regex
//                data = bson_iter_regex(iter, &options);
//                if (!bson_utf8_validate(data, strlen(data), true)) {
//                    phongo_throw_exception(PHONGO_ERROR_UNEXPECTED_VALUE, "Detected corrupt BSON data at offset %d", iter->err_off);
//                    return false;
//                }
//
//                php_phongo_bson_new_regex_from_regex_and_options(zv, data, options);
//                return true;

            case self::getFFI()->BSON_TYPE_DBPOINTER:
                // TODO: DBPointer
//                bson_iter_dbpointer(iter, &data_len, &data, &oid);
//                if (!bson_utf8_validate(data, data_len, true)) {
//                    phongo_throw_exception(PHONGO_ERROR_UNEXPECTED_VALUE, "Detected corrupt BSON data at offset %d", iter->err_off);
//                    return false;
//                }
//
//                php_phongo_bson_new_dbpointer(zv, data, data_len, oid);
//                return true;

            case self::getFFI()->BSON_TYPE_CODE:
                // TODO: Code
//                data = bson_iter_code(iter, &data_len);
//                if (!bson_utf8_validate(data, data_len, true)) {
//                    phongo_throw_exception(PHONGO_ERROR_UNEXPECTED_VALUE, "Detected corrupt BSON data at offset %d", iter->err_off);
//                    return false;
//                }
//
//                php_phongo_bson_new_javascript_from_javascript(zv, data, data_len);
//                return true;

            case self::getFFI()->BSON_TYPE_SYMBOL:
                // TODO: Symbol
//                data = bson_iter_symbol(iter, &data_len);
//                if (!bson_utf8_validate(data, data_len, true)) {
//                    phongo_throw_exception(PHONGO_ERROR_UNEXPECTED_VALUE, "Detected corrupt BSON data at offset %d", iter->err_off);
//                    return false;
//                }
//
//                php_phongo_bson_new_symbol(zv, data, data_len);
//                return true;

            case self::getFFI()->BSON_TYPE_CODEWSCOPE:
                // TODO: CodeWScope
//                data = bson_iter_codewscope(iter, &data_len, &options_len, (const uint8_t**) &options);
//			if (!bson_utf8_validate(data, data_len, true)) {
//                phongo_throw_exception(PHONGO_ERROR_UNEXPECTED_VALUE, "Detected corrupt BSON data at offset %d", iter->err_off);
//                return false;
//            }
//
//			bson_init_from_json(&bson, options, options_len, NULL);
//			php_phongo_bson_new_javascript_from_javascript_and_scope(zv, data, data_len, &bson);
//			return true;

            case self::getFFI()->BSON_TYPE_INT32:
            case self::getFFI()->BSON_TYPE_INT64:
                return LibBson::bson_iter_as_int64($iter);

            case self::getFFI()->BSON_TYPE_TIMESTAMP:
                // TODO: Timestamp
//                bson_iter_timestamp(iter, &data_len, &options_len);
//                php_phongo_bson_new_timestamp_from_increment_and_timestamp(zv, options_len, data_len);
//                return true;

            case self::getFFI()->BSON_TYPE_DECIMAL128:
                // TODO: Decimal128
//                bson_iter_decimal128(iter, &decimal);
//                php_phongo_bson_new_decimal128(zv, &decimal);
//                return true;

            case self::getFFI()->BSON_TYPE_MAXKEY:
                return new MaxKey();

            case self::getFFI()->BSON_TYPE_MINKEY:
                return new MinKey();
        }

        throw new UnexpectedValueException(sprintf('Detected unsupported BSON type %d at offset %d', $iter->type, $iter->off));
    }
}
