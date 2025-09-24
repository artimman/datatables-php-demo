# DbM DataTables PHP optional AJAX or API - TODO! Dokumantacja do aktualizacji

Wszystkie prawa autorskie zastrzeżone przez **Design by Malina (DbM)**

## Wprowadzenie
Ta biblioteka rozszerza możliwości generowania tabel z paginacją i AJAX w aplikacjach PHP. Obsługuje dwa tryby:
- **PHP mode** – cała tabela renderowana jest na serwerze i zwracana jako HTML.
- **AJAX mode** – dane i widoki (thead, tbody, paginacja) są zwracane jako JSON, a następnie renderowane przez JS.

Pozwala to na elastyczne i wydajne zarządzanie dużymi zestawami danych.

---

## Instrukcja uruchomienia - demo

### Import bazy demo  
W katalogu `_Documents/Database` znajduje się gotowy plik SQL.  
Zaimportuj go do swojej instancji MySQL/MariaDB:  

```bash
mysql -u root -p dbm_datatables < _Documents/Database/dbm_datatables.sql
```

### Konfiguracja adaptera  
W katalogu `src/Datatable` znajduje się plik `DatabaseAdapter.php`.  
Adapter mapuje `App\Core\Interfaces\DatabaseInterface` na interfejs biblioteki DataTables.  

W katalogu `src/Core/Classes` znajduje się klasa `Database`, która implementuje interfejs `DatabaseInterface` i odpowiada za konfigurację bazy (DSN, użytkownik, hasło).  

Upewnij się, że Twoja klasa AppDb jest poprawnie skonfigurowana (połączenie z bazą).  

```php
$appDb = new Database(/* host, user, pass, db */);
$dbAdapter = new DatabaseAdapter($appDb);
```

### Konfiguracja DataTable  
W katalogu `src/Datatable` znajduje się plik BlogConfigDataTable.php.  
To on definiuje źródło danych (tabela dbm_article) oraz mapowania kolumn.  

```php
$config = new BlogConfigDataTable($dbAdapter);
```

Wszystkie ustawienia potrzebne do działania DataTables PHP znajdują się w pliku `ConfigDataTable`.

### Uruchomienie w aplikacji (przykład z kontrolerem)  
W przykładowej aplikacji DataTables można osadzić w kontrolerze.  
W katalogu `src/Controller` znajduje się `DemoController`, którego metoda `run()` przygotowuje dane dla widoku.  

**index.php**:  
```php
use App\Controller\DemoController;
use Dbm\DataTables\Src\Renderers\DataTableRenderer;

// === Run ===
$demo = new DemoController();
extract($demo->run());

// === Render ===
$datatableRenderer = new DataTableRenderer();
echo $datatableRenderer->renderDataTable(
    $dt_records,
    $dt_sider,
    $dt_config
);
```

**DemoController.php - Metoda uruchomienia DataTables PHP w zewnętrznej aplikacji**

```php
public function run(): array
{
    $params = $this->request->getQueryParams();
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
```

Dzięki temu logika (pobranie danych, paginacja, filtrowanie) jest w kontrolerze, a renderowanie w pliku aplikacji i szablonie.

---

## Architektura
System składa się z następujących elementów:

- **ConfigDataTable** (np. `BlogConfigDataTable`)  
  Definiuje źródło danych, kolumny, sortowanie, filtrowanie oraz opcjonalny template wierszy.

- **DataTableService**  
  Realizuje zapytania SQL, nakłada paginację, sortowanie i zwraca dane w postaci obiektu `DataTableResult`.

- **DataTableRenderer**  
  Renderuje tabelę do HTML (dla trybu PHP) lub do JSON (dla trybu AJAX). Obsługuje także wstawki customowe (np. dodatkowe wiersze sum, komunikaty).

- **Kontrolery**  
  - *PanelBlogController* – widok panelu (tryb PHP lub AJAX).  
  - *PanelBlogApiController* – API do obsługi AJAX (GET, POST, PUT, DELETE).  
  - *BaseApiController* – klasa bazowa ułatwiająca budowanie API (json, success, error, paginated).

- **JavaScript (`dbm-datatable.js`)**  
  Obsługuje inicjalizację tabel, fetch danych, zdarzenia (paginacja, filtry, wyszukiwanie, sortowanie), integrację z Bootstrap (tooltipy).

---

## Tryby działania

### Obsługa wyszukiwania (`q` / `query`)

```text
┌───────────────┐
│   Frontend    │
│  (formularz)  │
└───────┬───────┘
        │
        ▼
  input name="q"
        │
        ▼
┌────────────────────────────┐
│        JavaScript          │
│ - zbiera dane z #dtSearch  │
│ - w buildUrl() normalizuje │
│   query → q                │
└─────────┬──────────────────┘
          │
          ▼
   URL / API Request
   ?q=Lorem
          │
          ▼
┌────────────────────────────┐
│     Backend (fromRequest)  │
│                            │
│ if (q) use q               │
│ else if (query) use query  │
│                            │
│ filters['query'] = "Lorem" │
└─────────┬──────────────────┘
          │
          ▼
┌────────────────────────────┐
│   DataTableService / SQL   │
│   WHERE col LIKE :_q_0     │
└─────────┬──────────────────┘
          │
          ▼
   Wyniki z bazy
```

### 1. PHP Mode
- Serwer renderuje całą tabelę (HTML: thead, tbody, paginacja).  
- Najprostsze w integracji, dobre dla mniejszych tabel.

```php
$html = DataTableRenderer::renderDataTable(
    rows: $records,
    columns: $columns,
    pager: $pager,
    filters: $filters,
    actions: $actions,
    mode: 'PHP',
    url: null,
    template: null
);
echo $html;
```

## SQL - opcje wywołania

### Typowe zapytanie

```php
$dtParams = DataTableParams::fromRequest($params);
$dtResult = $this->dataTable
  ->withParams($dtParams)
  ->paginate($this->configDataTable);
```

### Nietypowe zapytanie RAW – opcjonalny pełny ciąg SQL

```php
  $sql = $this->configDataTable->getSql();
  $maps = $this->configDataTable->getMaps();
  $dtParams = DataTableParams::fromRequest($params);
  $dtResult = $this->dataTable
    ->withParams($dtParams)
    ->paginateRaw($sql, $maps);
```

### 2. AJAX Mode
- Serwer zwraca dane w JSON, a widok jest renderowany w JS.
- Wydajniejsze przy większej liczbie rekordów.

**PHP – kontroler API**:

```php
public function list(Request $request): ResponseInterface
{
    $params = $request->getQueryParams();
    $dtParams = DataTableParams::fromRequest($params);
    $dtResult = $this->dataTable->withParams($dtParams)->paginate($this->configDataTable);

    return $this->success(DataTableRenderer::renderDataTableJson(
        $dtResult->records,
        $this->configDataTable->getColumns(),
        $dtResult->pager,
        $this->configDataTable->getTemplate()
    ));
}
```

**PHP (HTML) – inicjalizacja**:

```php + js
$this->datatableRender($dt_records, $dt_sider, $dt_schema, $dt_filters, $dt_actions, $dt_mode, $dt_url, $dt_template);

if (isset($dt_mode) && ($dt_mode === 'AJAX')) {
	echo '<script src="datatables/js/dbm-datatable.js"></script>';
}
```

**JS – inicjalizacja**:

```html TODO!
<div class="datatableContainer" data-dt-url="/api/articles" data-dt-mode="AJAX"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initDataTable('datatableContainer');
    });
</script>
```

---

## Konfiguracja kolumn

W `PanelBlogConfigDataTable::getColumns()` definiujesz kolumny:

```php
return [
    ['field' => 'id', 'label' => '#', 'name' => 'a.id', 'sortable' => true, 'class' => 'fw-bold'],
    ['field' => 'page_header', 'label' => 'Tytuł', 'sortable' => true, 'class' => 'fw-bold'],
    ['field' => 'category_name', 'label' => 'Kategoria', 'sortable' => true],
    ['field' => 'fullname', 'label' => 'Użytkownik', 'sortable' => true],
    ['field' => 'image_thumb', 'label' => 'Obraz', 'sortable' => false, 'class' => 'text-center', 'tag' => 'cell_image',
        'tag_options' => [
            'row_name'  => 'image',
            'src_dir' => '../images/blog/thumb/',
            'alt_field' => 'page_header',
            'width' => 20,
        ],
    ],
];
```

---

## Customowe wiersze

Możesz wstawić dodatkowe wiersze np. sumy, komunikaty, total:

```php
public static function getCustomRows(array $rows, array $columns): array
{
    return [
        [
            '_tag' => 'sum_row',
            'position' => 3,
            'sum' => array_sum(array_column($rows, 'visit')),
            'colspan' => count($columns),
        ],
        [
            '_tag' => 'notice_row',
            'position' => 5,
            'message' => 'To jest specjalny komunikat!',
        ],
    ];
}
```

---

## API – przykładowe endpointy

- `GET /api/articles` → lista artykułów (AJAX DataTable)  
- `GET /api/articles/{id}` → pojedynczy artykuł  
- `POST /api/articles` → dodanie artykułu  
- `PUT /api/articles/{id}` → aktualizacja artykułu  
- `DELETE /api/articles/{id}` → usunięcie artykułu  

---

## Struktura JSON (AJAX)

```json
{
  "success": true,
  "pager": {
    "page": 1,
    "perPage": 20,
    "total": 5,
    "pages": 1,
    "sort": "id",
    "dir": "DESC"
  },
  "columns": [
    { "field": "id", "title": "#", "sortable": true },
    { "field": "page_header", "title": "Tytuł", "sortable": true },
    { "field": "category_name", "title": "Kategoria", "sortable": true }
  ],
  "rows": [
    {
      "id": 5,
      "page_header": "Praesent euismod...",
      "category_name": "Web Design",
      "fullname": "Arthur Malinowski",
      "image": "post-idea.jpg",
      "status": "active",
      "visit": 190,
      "created": "2021-01-01 16:00",
      "modified": "2025-09-01 21:25"
    }
  ]
}
```

---

## JavaScript – rendering

### Nagłówki

```js
thead.innerHTML = `
  <tr>
    ${data.columns.map(col => `<th>${col.title}</th>`).join('')}
  </tr>
`;
```

### Wiersze

```js
tbody.innerHTML = data.rows.map(row => `
  <tr>
    ${data.columns.map(col => `<td>${row[col.field] ?? ''}</td>`).join('')}
  </tr>
`).join('');
```

### Formatery komórek (opcjonalne)

```js
function formatCell(value, col) {
  switch (col.formatter) {
    case "statusBadge":
      return value === "active"
        ? `<span class="badge bg-success">Aktywny</span>`
        : `<span class="badge bg-danger">Nieaktywny</span>`;
    default:
      return value ?? '';
  }
}
```

---

## Najlepsze praktyki

- Rozszerzając kod do indywidualnych potrzeb dokumentuj kolumny i customowe wiersze, aby uniknąć błędów.  

---

## TODO / Plany rozwoju

- Dodać gotowe komponenty JS (np. w Vanilla/Vue/React), które komunikują się z backendem.
