<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 *
 * Sposób użycia:
 * Response::json($response, 200, true);
 * albo po prostu: Response::json($response) - w trybie produkcyjnym
 */

declare(strict_types=1);

namespace App\Core\Classes;

class Response
{
    public static function json(array $data, int $status = 200, bool $pretty = false): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        echo json_encode($data, $flags);
        exit;
    }
}
