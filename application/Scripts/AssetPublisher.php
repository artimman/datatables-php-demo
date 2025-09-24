<?php
/**
 * Demo: DbM DataTables PHP
 * @author Artur Malinowski
 */

declare(strict_types=1);

namespace App\Core\Scripts;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

/**
 * Bezpiecznie kopiuje cały katalog "assets" do katalogu public/
 */
class AssetPublisher
{
    /**
     * Publikuj zasoby z $source do $destination.
     * Jeśli $clearDestination === true — zawartość docelowa jest usuwana przed kopiowaniem.
     *
     * @throws RuntimeException
     */
    public static function publish(string $source, string $destination, bool $clearDestination = false): ?string
    {
        if (!is_dir($source)) {
            throw new RuntimeException("Source does not exist: $source");
        }

        $source = rtrim($source, DIRECTORY_SEPARATOR);
        $destination = rtrim($destination, DIRECTORY_SEPARATOR);

        if ($clearDestination) {
            self::clearDirectory($destination);
        }

        $count = self::copyDirectory($source, $destination);

        if ($count > 0) {
            return "Resources copied, files: $count. From: $source To: $destination";
        }

        return null; // brak zmian
    }

    /**
     * Rekurencyjne kopiowanie plików.
     * Używamy bezpośrednio $file->getPathname() i obliczamy ścieżkę względną względem $source
     * (bez używania getSubPathName(), które czasami bywa niejednoznaczne w analizatorach).
     */
    public static function copyDirectory(string $source, string $destination): int
    {
        if (!is_dir($source)) {
            throw new RuntimeException("Source not found: $source");
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $sourceReal = realpath($source);
        if ($sourceReal === false) {
            throw new RuntimeException("Could not read source realpath: $source");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceReal, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $copied = 0;

        foreach ($iterator as $item) {
            $pathName = $item->getPathname();
            $relativePath = substr($pathName, strlen($sourceReal) + 1);
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }
                continue;
            }

            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            // Kopiuj tylko jeśli brak pliku lub jest starszy niż źródło
            if (!file_exists($targetPath) || filemtime($targetPath) < filemtime($pathName)) {
                if (copy($pathName, $targetPath)) {
                    $copied++;
                }
            }
        }

        return $copied;
    }

    /**
     * Czyści zawartość katalogu rekursywnie (nie usuwa katalogu, chyba że nie istnieje potem).
     * Zawiera prostą ochronę: nie pozwoli czyścić katalogu poza root projektu.
     */
    public static function clearDirectory(string $directory): void
    {
        if ($directory === '' || !is_dir($directory)) {
            return; // nic do czyszczenia
        }

        $real = realpath($directory);
        if ($real === false) {
            return;
        }

        // Ustal root projektu (dwa poziomy wyżej od application/)
        $projectRoot = realpath(__DIR__ . '/../../');
        if ($projectRoot === false) {
            throw new RuntimeException('Unable to determine project root.');
        }

        // Bezpieczeństwo: upewnij się, że katalog docelowy jest wewnątrz projektu
        if (strpos($real, $projectRoot) !== 0) {
            throw new RuntimeException("[Denied] The directory $directory is outside the project root.");
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($it as $file) {
            try {
                if ($file->isDir()) {
                    @rmdir($file->getPathname());
                } else {
                    @unlink($file->getPathname());
                }
            } catch (Throwable $e) {
                // ignoruj błędy, idź dalej
            }
        }

        // Upewnij się, że katalog istnieje (może zostać usunięty przez rmdir)
        if (!is_dir($real)) {
            mkdir($real, 0777, true);
        }
    }
}

// -----------------------------
// Example CLI script (put in project root as application/copy-assets.php)
// -----------------------------

/*
<?php
declare(strict_types=1);

// require composer autoload if present, otherwise fall back to your autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../autoload.php')) {
    require __DIR__ . '/../autoload.php';
} else {
    fwrite(STDERR, "Brak autoloadera (vendor/autoload.php lub autoload.php). Uruchom composer install lub dodaj własny autoloader.\n");
    exit(1);
}

use App\Core\Scripts\AssetPublisher;

$opts = getopt('', ['src::', 'dest::', 'clear::', 'help::']);

if (isset($opts['help'])) {
    echo "Usage:\n";
    echo "  php scripts/publish-assets.php --src=vendor/your/package/assets --dest=public/assets/pkg --clear=1\n";
    exit(0);
}

$src = $opts['src'] ?? __DIR__ . '/../vendor/dbm/datatables/assets';
$dest = $opts['dest'] ?? __DIR__ . '/../public/assets/dbm-datatables';
$clear = isset($opts['clear']) ? (bool) filter_var($opts['clear'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : true;

try {
    AssetPublisher::publish($src, $dest, $clear);
} catch (Throwable $e) {
    fwrite(STDERR, "Błąd: " . $e->getMessage() . "\n");
    exit(1);
}
*/

// -----------------------------
// composer.json snippet (add to your project composer.json scripts)
// -----------------------------

/*
"scripts": {
    "post-install-cmd": [
        "php application/copy-assets.php --src=vendor/dbm/datatables/assets --dest=public/assets/datatables --clear=1"
    ],
    "post-update-cmd": [
        "php application/copy-assets.php --src=vendor/dbm/datatables/assets --dest=public/assets/datatables --clear=1"
    ]
}
*/
