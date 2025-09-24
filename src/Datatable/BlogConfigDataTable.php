<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Datatable;

use Dbm\DataTables\Src\Classes\JoinedRepository;
use Dbm\DataTables\Src\Interfaces\ConfigDataTableInterface;
use Dbm\DataTables\Src\Interfaces\DatabaseInterface;
use Dbm\DataTables\Src\Utility\Translator;

class BlogConfigDataTable extends JoinedRepository implements ConfigDataTableInterface
{
    private const DEFAULT_MODE = 'PHP'; // You can change defalut 'PHP' to 'AJAX' or 'API' (in development)
    private const DEFAULT_URL = ''; // URL is required for options 'AJAX' and 'API'

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct(
            database: $database,
            table: $this->getTable(),
            primaryKey: $this->getPrimaryKey(),
            joins: $this->getJoins(),
            selectMap: $this->getSelectMap(),
            sortableMap: $this->getSortableMap(forRawSql: false),
            filterableMap: $this->getFilterableMap(forRawSql: false),
            searchable: $this->getSearchable()
        );
    }

    // --- Core config (SQL) ---

    private function getTable(): string
    {
        return 'dbm_article a';
    }

    private function getPrimaryKey(): string
    {
        return 'a.id';
    }

    private function getJoins(): array
    {
        return [
            'INNER JOIN dbm_article_categories c ON c.id = a.category_id',
            'INNER JOIN dbm_user_details u ON u.id = a.user_id',
        ];
    }

    /* Example WHERE:
     protected function getBaseWhere(): array {
        return ['a.status = :status', [':status' => 'active']];
    } */

    // --- Core config (Maps) ---

    public function getSelectMap(): array
    {
        return [
            'id' => 'a.id AS id',
            'page_header' => 'a.page_header AS page_header',
            'image_thumb' => 'a.image_thumb AS image_thumb',
            'status' => 'a.status AS status',
            'visit' => 'a.visit AS visit',
            'created' => 'a.created AS created',
            'modified'  => 'a.modified AS modified',
            'category_name' => 'c.category_name AS category_name',
            'fullname' => 'u.fullname AS fullname',
        ];
    }

    // --- Methods used by default and in the Raw SQL alternative option ---

    /**
     * Sort maps:
     * - forRawSql=false => in the repo we use table aliases (a./c./u.)
     * - forRawSql=true => in RAW we use aliases from SELECT (without prefixes)
     */
    private function getSortableMap(bool $forRawSql = false): array
    {
        if ($forRawSql) { // Optional alternative Raw SQL
            return [
                'id' => 'id',
                'page_header' => 'page_header',
                'visit' => 'visit',
                'created' => 'created',
                'modified' => 'modified',
                'category_name' => 'category_name',
                'fullname' => 'fullname',
            ];
        }

        return [
            'id' => 'a.id',
            'page_header' => 'a.page_header',
            'visit' => 'a.visit',
            'created' => 'a.created',
            'modified' => 'a.modified',
            'category_name' => 'c.category_name',
            'fullname' => 'u.fullname',
        ];
    }

    /**
     * Filter maps:
     * - forRawSql=false => repo: table aliases
     * - forRawSql=true => RAW: aliases from SELECT (without prefixes)
     */
    private function getFilterableMap(bool $forRawSql = false): array
    {
        if ($forRawSql) { // Optional alternative Raw SQL
            return [
                'status' => 'status',
                'category' => 'category_id',
                'user' => 'user_id',
            ];
        }

        return [
            'status' => 'a.status',
            'category' => 'a.category_id',
            'user' => 'a.user_id',
        ];
    }

    private function getSearchable(): array
    {
        return ['page_header', 'category_name', 'fullname'];
    }

    // --- Optional alternative Raw SQL + maps (when not using JoinedRepository) ---

    public function getSql(): string
    {
        return "
            SELECT 
                a.id             AS id,
                a.page_header    AS page_header,
                a.image_thumb    AS image_thumb,
                a.status         AS status,
                a.visit          AS visit,
                a.created        AS created,
                a.modified       AS modified,
                c.category_name  AS category_name,
                u.fullname       AS fullname,
                a.category_id    AS category_id,
                a.user_id        AS user_id
            FROM dbm_article a
            INNER JOIN dbm_article_categories c ON c.id = a.category_id 
            INNER JOIN dbm_user_details u ON u.id = a.user_id 
        ";
    }

    public function getMaps(): array
    {
        return [
            'sortable' => $this->getSortableMap(forRawSql: true),
            'filterable' => $this->getFilterableMap(forRawSql: true),
            'searchable' => $this->getSearchable(),
        ];
    }

    // --- UI config ---

    public function getTableConfig(): array
    {
        return [
            [
                'field' => 'lp',
                'label' => '#',
                'virtual' => true,
                'formatter' => function (array $row, int $lp) {
                    return '<span class="fw-bold" title="ID: ' . $row['id'] . '">' . $lp . '</span>';
                }
            ],
            ['field' => 'id', 'label' => 'ID', 'name' => 'a.id', 'sortable' => true, 'hidden' => true],
            ['field' => 'page_header', 'label' => 'Tytuł', 'sortable' => true, 'class' => 'fw-bold'],
            ['field' => 'category_name', 'label' => 'Kategoria', 'sortable' => true],
            ['field' => 'fullname', 'label' => 'Użytkownik', 'sortable' => true],
            ['field' => 'image_thumb', 'label' => 'Obraz', 'sortable' => false, 'class' => 'text-center', 'tag' => 'cell_image',
                'tag_options' => [
                    'row_name'  => 'image',
                    'src_dir' => './public/images/blog/thumb/', // TODO! In case of JS it should also be set in window.DBM_CONFIG ?
                    'alt_field' => 'page_header',
                    'width' => 20,
                ],
            ],
            ['field' => 'status', 'label' => 'Status', 'sortable' => false, 'tag' => 'cell_change_status'],
            ['field' => 'visit', 'label' => 'Wizyty', 'sortable' => true],
            [
                'field' => 'created',
                'label' => 'Data utworzenia',
                'sortable' => true,
                'class' => 'text-nowrap',
                'formatter' => function (array $row) {
                    return !empty($row['created']) ? date('Y-m-d H:i', strtotime($row['created'])) : null;
                }
            ],
            [
                'field' => 'modified',
                'label' => 'Data modyfikacji',
                'sortable' => true,
                'formatter' => function (array $row) {
                    return !empty($row['modified']) ? date('Y-m-d H:i', strtotime($row['modified'])) : null;
                }
            ],
            /* [
                'field' => 'total', // Example: field virtual = true with formatter.
                'label' => 'A&B',
                'virtual' => true,
                'formatter' => function (array $row) {
                    return $this->customFieldABC($row);
                }
            ], */
            ['field' => 'action', 'label' => 'Akcja', 'sortable' => false, 'class' => 'text-end', 'tag' => 'cell_action',
                'tag_options' => [
                    'actions' => [
                        [
                            'type'  => 'link',
                            'url' => '#create-edit?id={id}',
                            'label' => 'Edytuj',
                            'icon'  => 'bi bi-pencil-square',
                            'class' => 'text-primary',
                        ],
                        [
                            'type' => 'button',
                            'label' => 'Usuń',
                            'icon' => 'bi bi-trash',
                            'class' => 'text-danger deleteRecord',
                            'attrs' => [
                                'data-id' => '{id}',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getFilters(): array
    {
        $categories = $this->getAllCategories();
        $users = $this->getAllUsers();

        return [
            'category' => [
                'label' => Translator::trans('filter_category'),
                'options' => array_map(fn ($row) => [
                    'value' => (string)$row['id'],
                    'label' => (string)$row['category_name'],
                ], $categories),
            ],
            'user' => [
                'label' => Translator::trans('filter_user'),
                'options' => array_map(fn ($row) => [
                    'value' => (string)$row['id'],
                    'label' => $row['fullname'] ?: "[{$row['login']}]",
                ], $users),
            ],
            'status' => [
                'label' => Translator::trans('filter_status'),
                'options' => [
                    ['value' => 'active', 'label' => Translator::trans('active')],
                    ['value' => 'inactive', 'label' => Translator::trans('inactive')],
                ],
            ],
        ];
    }

    public function getButtons(): array
    {
        return [
            [
                'label' => 'Eksport',
                'url' => '#export-available-in-api',
                'target' => '_blank',
                'icon' => 'bi bi-file-earmark-spreadsheet',
                'class' => 'btn btn-sm btn-outline-success',
            ],
        ];
    }

    // --- Static methods ---

    /**
     * Configuration mode
     */
    public static function getMode(?string $mode = null): string
    {
        $allowed = ['PHP', 'AJAX', 'API'];
        $mode = $mode ? strtoupper($mode) : self::DEFAULT_MODE; // or global setting: ConstantConfig::DATATABLES_MODE

        return in_array($mode, $allowed, true) ? $mode : 'PHP';
    }

    /**
     * In the AJAX option, the URL for the dbm-datatables.js file is required
     */
    public static function getUrl(?string $url = null): ?string
    {
        return $url ?: self::DEFAULT_URL;
    }

    /**
     * Examples for custom rows
     *
     * Built-in methods '_tag': notice_row, custom_html
     */
    public static function getCustomRows(array $rows, array $columns): array
    {
        $custom = [];

        /* Example: message after 3 rows
        $position = 3;

        if (count($rows) > $position) {
            $custom[$position] = [
                '_tag' => 'notice_row',
                'position' => $position,
                'message' => "To jest specjalny komunikat po {$position} wierszu!",
            ];
        } */

        return $custom;
    }

    // --- Custom query helpers for filters ---

    private function getAllCategories(): array
    {
        $sql = "SELECT id, category_name FROM dbm_article_categories ORDER BY id DESC";
        return $this->database->fetchAll($sql);
    }

    private function getAllUsers(): array
    {
        $sql = "SELECT u.id, u.login, d.fullname FROM dbm_user u 
            JOIN dbm_user_details d ON d.user_id = u.id
            ORDER BY u.id DESC";

        return $this->database->fetchAll($sql);
    }

    // --- Custom TableConfig Methods: Example ---

    private function customFieldABC(array $row): int
    {
        $a = (int) $row['id'] ?? 0;
        $b = (int) $row['visit'] ?? 0;
        return $a + $b;
    }
}
