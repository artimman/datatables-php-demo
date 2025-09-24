<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Controller;

use App\Core\Classes\Database;
use App\Core\Classes\Request;
use App\Datatable\BlogConfigDataTable;
use App\Datatable\DatabaseAdapter;
use App\Repository\BlogRepository;
use Dbm\DataTables\Src\Classes\DataTableParams;
use Dbm\DataTables\Src\Classes\DataTableService;
use DateTime;

class DemoController
{
    private Database $database;
    private DataTableService $dataTable;
    private BlogConfigDataTable $configDataTable;
    private BlogRepository $repository;
    private DataTableParams $datatableParams;
    private Request $request;

    public function __construct()
    {
        $this->database = new Database();
        $this->repository = new BlogRepository($this->database);
        $this->request = new Request();

        $adapterDB = new DatabaseAdapter($this->database);
        $this->dataTable = new DataTableService($adapterDB);
        $this->configDataTable = new BlogConfigDataTable($adapterDB);
        $this->datatableParams = new DataTableParams();
    }

    public function run(): array
    {
        // Params
        $params = $this->request->getQueryParams();

        // Update statusu
        $actionParams = array_diff_key($params, array_flip(['page', 'per_page', 'sort', 'dir', 'filter', 'search']));
        if (isset($actionParams['id'], $actionParams['status'])) {
            $this->makeUpdateStatus($actionParams);
        }

        // Datatables
        $dtParams = $this->datatableParams->fromRequest($params);
        $dtResult = $this->dataTable
            ->withParams($dtParams)
            ->paginate($this->configDataTable);

        return [
            'dt_records' => $dtResult->records,
            'dt_sider' => $dtResult->sider,
            'dt_config' => $this->configDataTable,
            'dt_mode' => $this->configDataTable->getMode(),
            'dt_url' => $this->configDataTable->getUrl(),
            'dt_query' => $this->configDataTable->getLastBuiltQuery(),
        ];
    }

    // INFO: Można przenieść do serwisu.
    private function makeUpdateStatus(array $data): bool
    {
        $data['modified'] = (new DateTime())->format('Y-m-d H:i:s');
        return $this->repository->updateArticleStatus($data);
    }
}
