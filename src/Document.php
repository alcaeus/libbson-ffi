<?php

namespace Mongodb\LibbsonFfi;

use FFI\CData;
use IteratorAggregate;
use Serializable;
use UnexpectedValueException;

final class Document implements BSON, IteratorAggregate, Serializable
{
    private function __construct(private CData $bson)
    {
    }

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

        $instance = new self($bsonObject);

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
    }

    final public function get(string $key): mixed
    {
    }

    final public function getIterator(): Iterator
    {
    }

    final public function has(string $key): bool
    {
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
        return LibBson::bson_get_data($this->bson);
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
