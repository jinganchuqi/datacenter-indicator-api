<?php

namespace App\Library\ClickHouse;

interface ClickHouseClientInterface
{
    public function ping(): bool;
    public function query(string $sql, array $bindings = []): array;
    public function write(string $sql, array $bindings = []);
}