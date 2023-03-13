<?php

namespace Mongodb\LibbsonFfi;

use FFI;
use FFI\CData;
use LogicException;
use MongoDB\BSON\MaxKey;
use MongoDB\BSON\MinKey;
use MongoDB\BSON\ObjectId;
use Mongodb\LibbsonFfi\FFI\LibBson;
use RuntimeException;
use UnexpectedValueException;
use function MongoDB\BSON\toPHP;

final class Iterator implements \Iterator
{
    private CData $iterator;

    private bool $isArray = false;
    private int $key = 0;
    private bool $valid;
    private mixed $current;

    public function __construct(BSON $bson)
    {
        $this->iterator = LibBson::new('bson_iter_t');

        if (!LibBson::bson_iter_init($this->getIteratorAddr(), $bson->cdata)) {
            throw new RuntimeException('Could not initialize BSON iterator.');
        }

        $this->valid = LibBson::bson_iter_next($this->getIteratorAddr());
    }

    final public function current(): mixed
    {
        if (!$this->valid) {
            throw new LogicException('Cannot call current() on an exhausted iterator');
        }

        return $this->current ?? $this->buildCurrent();
    }

    final public function key(): string|int
    {
        if (!$this->valid) {
            throw new LogicException('Cannot call key() on an exhausted iterator');
        }

        if ($this->isArray) {
            return $this->key;
        }

        $key = LibBson::bson_iter_key($this->getIteratorAddr());
        if (!LibBson::bson_utf8_validate($key, strlen($key), false)) {
            throw new UnexpectedValueException(sprintf('Detected corrupt BSON data at offset %d', $this->iterator->off));
        }

        return $key;
    }

    final public function next(): void
    {
        $this->valid = LibBson::bson_iter_next($this->getIteratorAddr());
        $this->key++;
        $this->current = null;
    }

    final public function rewind(): void {}

    final public function valid(): bool
    {
        return $this->valid;
    }

    final public function __wakeup(): void {}

    private function buildCurrent(): mixed
    {
        return $this->current = LibBson::bson_iter_to_php($this->getIteratorAddr());
    }

    private function getIteratorAddr(): CData
    {
        return FFI::addr($this->iterator);
    }
}
