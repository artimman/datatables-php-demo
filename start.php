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

use App\Core\Scripts\AssetPublisher;

// === Functions ===
function autoloadRegister(): void
{
    spl_autoload_register(function (string $class) {
        $prefixes = [
            'App\\' => __DIR__ . '/src/',
            'App\\Core\\' => __DIR__ . '/application/',
            'Dbm\\DataTables\\Src\\' => __DIR__ . '/libraries/dbm/datatables/src/',
            'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/', // TEMP!
            'Psr\\SimpleCache\\' => __DIR__ . '/vendor/psr/simple-cache/src/', // TEMP! Required for PhpSpreadsheet.
            'Composer\\Pcre\\' => __DIR__ . '/vendor/composer/pcre/src/', // TEMP! Required for PhpSpreadsheet.
        ];

        foreach ($prefixes as $prefix => $baseDir) {
            if (str_starts_with($class, $prefix)) {
                $relative = substr($class, strlen($prefix));
                $path = $baseDir . str_replace('\\', '/', $relative) . '.php';

                if (file_exists($path)) {
                    require_once $path;
                    return;
                }
            }
        }
    });
}

function publishAssetsIfMissing(): ?string
{
    $sources = [
        __DIR__ . '/libraries/dbm/datatables/assets',
        __DIR__ . '/vendor/dbm/datatables/assets',
    ];
    $destination = __DIR__ . '/public/assets/datatables';
    $requiredFiles = [
        $destination . '/js/dbm-datatables.js',
        $destination . '/js/dbm-datatables-ajax.js',
    ];

    // Jeśli wszystkie wymagane pliki istnieją → nic nie rób
    $allExist = array_reduce($requiredFiles, fn ($carry, $file) => $carry && file_exists($file), true);
    if ($allExist) {
        return null;
    }

    // Szukaj pierwszego istniejącego źródła
    foreach ($sources as $source) {
        if (is_dir($source)) {
            return AssetPublisher::publish($source, $destination);
        }
    }

    return 'No source of assets.';
}
