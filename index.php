<?php
/**
 * Demo: DbM DataTables PHP
 * 
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

// === Strict typing ===
declare(strict_types=1);

// === Functions ===
require_once __DIR__ . '/start.php';
// Autoload
autoloadRegister();
// Assets
$message = publishAssetsIfMissing();

// === Use Classes ===
use App\Controller\DemoController;
use Dbm\DataTables\Src\Classes\DataTableRenderer;
use Dbm\DataTables\Src\Utility\Translator;

// === Run ===
// To run the demo, make the correct configuration in the file: ...ConfigDataTable.php
$demo = new DemoController();
extract($demo->run());

// === Render ===
$datatableRenderer = new DataTableRenderer();
$renderView = $datatableRenderer->renderDataTable(
    $dt_records,
    $dt_sider,
    $dt_config
);

// Data
$locale = Translator::$locale;
$i18n = Translator::$translations[Translator::$locale];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Design by Malina">
    <title>DbM DataTables PHP optional AJAX or API</title>
	<link href="./favicon.ico" rel="shortcut icon" type="image/x-icon">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
	<style>
		.dt-header { background: linear-gradient(90deg, #cf00c5, #0300cf); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
	</style>
</head>
<body class="container mt-5">
    <h1 class="mx-2 my-4 text-primary dt-header">Demo – Datatables PHP</h1>
	<?php if (!empty($message)): ?>
		<div class="alert alert-info"><?= $message ?></div>
	<?php endif; ?>

	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h5 class="card-title mb-0">
				<a href="./" class="link-dark text-decoration-none"><i class="bi bi-card-list me-2"></i>Lista artykułów</a>
			</h5>
			<a href="#add" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Dodaj</a>
		</div>
		<div class="card-body">
			<!-- DbM DataTables -->
			<?= $renderView ?>

		</div>
	</div>
	<div class="mx-2 my-4">
        <fieldset class="border rounded-3 px-3 bg-light">
            <legend class="float-none w-auto px-3">Query</legend>
            <pre class="text-break" style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars(trim($dt_query->sql ?? '')) ?></pre>
        </fieldset>
    </div>
	<div class="text-center">
        <p class="text-secondary small m-0">Copyright &copy; 2025 <a href="https://dbm.org.pl/" class="link-secondary text-decoration-none">Design by Malina</a></p>
    </div>

	<!-- JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script>
		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
		var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
			return new bootstrap.Tooltip(tooltipTriggerEl)
		})
	</script>

	<!-- JS for DbM Datatables - Available in the AJAX or API mode -->
	<!-- Modal Delete compatible with DataTables * Set DELETE URL in data-delete-url="" -->
	<div id="deleteModal" class="modal fade" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-delete-url="#not-available">
		<div class="modal-dialog">
        	<div class="modal-content">
            	<div class="modal-header border-0">
					<h5 class="modal-title"><i class="bi bi-trash me-2 text-danger"></i>Potwierdź usunięcie rekordu</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            	</div>
            	<div class="modal-body">
					<p class="font-weight-bold mb-2">Czy na pewno chcesz usunąć?</p>
				</div>
            	<div class="modal-footer border-0">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
					<button type="button" class="btn btn-danger" id="recordDelete">Usuń</button>
            	</div>
        	</div>
    	</div>
	</div>
	<!-- JS for Modal Delete - PHP mode only -->
	<script src="./public/assets/datatables/js/delete-record.js"></script>
</body>
</html>
