<?php

namespace Mongodb\LibbsonFfi;

interface BSON
{
    static public function fromPHP(array $value): static;

    public function getIterator(): Iterator;

    public function toPHP(?array $typeMap = null): array|object;

    public function __toString(): string;
}
