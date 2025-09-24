<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Datatable;

use App\Core\Interfaces\DatabaseInterface as AppDb;
use Dbm\DataTables\Src\Interfaces\DatabaseInterface as DataTableDb;

/**
 * Adapter class bridging the application's DatabaseInterface (AppDb)
 * with the DataTables library DatabaseInterface (DataTableDb).
 *
 * Converts the method signatures and return formats so that
 * the DataTables library can work with any database layer
 * implemented in the host application.
 */
class DatabaseAdapter implements DataTableDb
{
    public function __construct(private AppDb $db)
    {
    }

    public function query(string $sql, array $params = []): array
    {
        $this->db->queryExecute($sql, $params);
        return $this->db->fetchAll('assoc');
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $this->db->queryExecute($sql, $params);
        return $this->db->fetch('assoc') ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $this->db->queryExecute($sql, $params);
        return $this->db->fetchAll('assoc');
    }
}
