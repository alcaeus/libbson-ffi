<?php

namespace Mongodb\LibbsonFfi;

use FFI;
use FFI\CData;
use IteratorAggregate;
use Mongodb\LibbsonFfi\FFI\LibBson;
use RuntimeException;
use Serializable;
use UnexpectedValueException;
use function MongoDB\BSON\fromPHP;
use function strlen;

final class Document implements BSON, IteratorAggregate, Serializable
{
    /** @internal */
    public function __construct(public readonly CData $cdata) {}

    public function __destruct()
    {
        // TODO: Destroy bson instance
    }

    final static public function fromBSON(string $bson): Document
    {
        $eof = true;
        $reader = LibBson::bson_reader_new_from_data($bson);

        if (!$bsonObject = LibBSON::bson_reader_read($reader, $eof)) {
            throw new UnexpectedValueException('Could not read document from BSON reader');
        }

        $instance = new self(LibBSON::bson_copy($bsonObject));

        if (LibBson::bson_reader_read($reader, $eof) || !$eof) {
            throw new UnexpectedValueException('Reading document did not exhaust input buffer');
        }

        return $instance;
    }

    final static public function fromJSON(string $json): Document
    {
    }

    final static public function fromPHP(array|object $value): static
    {
        return self::fromBSON(fromPHP($value));
    }

    final public function get(string $key): mixed
    {
        $iterator = LibBson::new('bson_iter_t');

        if (!LibBson::bson_iter_init(FFI::addr($iterator), $this->cdata)) {
            throw new RuntimeException('Could not initialize BSON iterator.');
        }

        if (!LibBson::bson_iter_find_w_len(FFI::addr($iterator), $key, strlen($key))) {
            throw new RuntimeException(sprintf('Could not find key "%s" in BSON data', $key));
        }

        return LibBson::bson_iter_to_php(FFI::addr($iterator));
    }

    final public function getIterator(): Iterator
    {
        return new Iterator($this);
    }

    final public function has(string $key): bool
    {
        $iterator = LibBson::new('bson_iter_t');

        if (!LibBson::bson_iter_init(FFI::addr($iterator), $this->cdata)) {
            throw new RuntimeException('Could not initialize BSON iterator.');
        }

        return LibBson::bson_iter_find_w_len(FFI::addr($iterator), $key, strlen($key));
    }

    final public function toPHP(?array $typeMap = null): array|object
    {
    }

    final public function toCanonicalExtendedJSON(): string
    {
    }

    final public function toRelaxedExtendedJSON(): string
    {
    }

    final public function __toString(): string
    {
        return LibBson::bson_get_data($this->cdata);
    }

    final public static function __set_state(array $properties): Document
    {
    }

    final public function serialize(): string
    {
    }

    /** @param string $serialized */
    final public function unserialize($serialized): void
    {
    }

    final public function __unserialize(array $data): void
    {
    }

    final public function __serialize(): array
    {
    }
}
