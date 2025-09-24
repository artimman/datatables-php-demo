<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Core\Classes;

class Request
{
    public function getQueryParams(): array
    {
        return $_GET;
    }

    public function getQuery(string $param, mixed $default = null): mixed
    {
        return $_GET[$param] ?? $default;
    }

    public function getPost(string $param, $default = null): mixed
    {
        return $_POST[$param] ?? $default;
    }

    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getServerParams(): array
    {
        return [
            'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
            'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? null,
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
            'HTTPS' => $_SERVER['HTTPS'] ?? null,
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
            'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? null,
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
    }
}
