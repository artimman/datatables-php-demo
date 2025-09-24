<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Repository;

use App\Core\Interfaces\DatabaseInterface;

class BlogRepository
{
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getAllCategories(bool $asTables = false): ?array
    {
        $query = "SELECT * FROM dbm_article_categories ORDER BY id DESC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $asTables
            ? $this->database->fetchAll()
            : $this->database->fetchAllObject();
    }

    public function getAllUsers(bool $asTables = false): ?array
    {
        $query = "SELECT user.id, user.login, details.fullname FROM dbm_user user"
            . " JOIN dbm_user_details details ON details.user_id = user.id"
            . " ORDER BY user.id DESC";

        $this->database->queryExecute($query);

        if ($this->database->rowCount() == 0) {
            return null;
        }

        return $asTables
            ? $this->database->fetchAll()
            : $this->database->fetchAllObject();
    }

    public function updateArticleStatus(array $data): bool
    {
        $query = "UPDATE dbm_article SET status = :status, modified = :modified WHERE id = :id";
        return $this->database->queryExecute($query, $data) > 0;
    }

    public function deleteArticle(int $id): bool
    {
        $query = "DELETE FROM dbm_article WHERE id = :id";
        return true; //$this->database->queryExecute($query, [':id' => $id]) > 0;
    }
}
