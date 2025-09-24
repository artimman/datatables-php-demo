<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Core\Interfaces;

interface DatabaseInterface
{
    public function queryExecute(string $sql, array $params = []): int;

    public function rowCount(): int;

    public function fetch(string $fetch = 'assoc'): array;

    public function fetchAll(string $fetch = 'assoc'): array;

    public function fetchObject(): ?object;

    public function fetchAllObject(): array;
}
