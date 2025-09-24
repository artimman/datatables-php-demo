<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Core\Classes;

use App\Core\Interfaces\DatabaseInterface;
use PDO;
use PDOException;
use PDOStatement;

class Database implements DatabaseInterface
{
    private PDO $pdo;
    private PDOStatement $stmt;

    public function __construct(
        string $dsn = 'mysql:host=localhost;dbname=dbm_dev;charset=utf8',
        string $user = 'root',
        string $pass = ''
    ) {
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $this->checkConnection();
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public function queryExecute(string $sql, array $params = []): int
    {
        $this->stmt = $this->pdo->prepare($sql);

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $param = is_string($key) ? $key : $key + 1;

                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                } else {
                    $type = PDO::PARAM_STR;
                }

                $this->stmt->bindValue($param, $value, $type);
            }
        }

        $this->stmt->execute();
        return $this->stmt->rowCount();
    }

    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    public function fetch(string $fetch = 'assoc'): array
    {
        if ($fetch == 'assoc') {
            return $this->stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $this->stmt->fetch();
    }

    public function fetchAll(string $fetch = 'assoc'): array
    {
        if ($fetch == 'assoc') {
            return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->stmt->fetchAll();
    }

    public function fetchObject(): ?object
    {
        $row = $this->stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function fetchAllObject(): array
    {
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function checkConnection(): void
    {
        try {
            $this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            die('Database connection lost: ' . $e->getMessage());
        }
    }
}
