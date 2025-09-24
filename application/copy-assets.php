<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

// require composer autoload if present, otherwise fall back to your autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../autoload.php')) {
    require __DIR__ . '/../autoload.php';
} else {
    fwrite(STDERR, "No autoloader (vendor/autoload.php or autoload.php). Run composer install or add your own autoloader.\n");
    exit(1);
}

use App\Core\Scripts\AssetPublisher;

$opts = getopt('', ['src::', 'dest::', 'clear::', 'help::']);

if (isset($opts['help'])) {
    echo "Usage:\n";
    echo "  php application/copy-assets.php --src=vendor/your/package/assets --dest=public/assets/pkg --clear=1\n";
    exit(0);
}

$src = $opts['src'] ?? __DIR__ . '/../vendor/dbm/datatables/assets';
$dest = $opts['dest'] ?? __DIR__ . '/../public/assets/datatables';
$clear = isset($opts['clear']) ? (bool) filter_var($opts['clear'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : true;

try {
    AssetPublisher::publish($src, $dest, $clear);
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
