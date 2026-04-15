<?php
// CLI script to swap n_serie_ini and n_serie_fin in table `venta` safely.
// Usage: php swap_series.php confirm
// It will create a backup table named venta_backup_YYYYMMDD_HHMMSS before swapping.

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

if (!isset($argv[1]) || $argv[1] !== 'confirm') {
    echo "To run the swap you must pass the literal argument 'confirm'.\n";
    echo "Example: php swap_series.php confirm\n";
    exit(1);
}

$host = 'localhost';
$user = 'root';
$pass = 'Segma1@@';
$db   = 'ingreso_ventas_bd';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "DB connect failed: " . $mysqli->connect_error . "\n";
    exit(1);
}
$mysqli->set_charset('utf8');

$ts = date('Ymd_His');
$backupTable = "venta_backup_{$ts}";

echo "Creating backup table {$backupTable}...\n";
$sql = "CREATE TABLE `{$backupTable}` AS SELECT * FROM `venta`";
if (!$mysqli->query($sql)) {
    echo "Backup failed: " . $mysqli->error . "\n";
    exit(1);
}

echo "Backup created. Starting swap...\n";

// Try to perform swap using a temporary column to be safe
$queries = [
    "ALTER TABLE `venta` ADD COLUMN `n_serie_tmp` TEXT",
    "UPDATE `venta` SET `n_serie_tmp` = `n_serie_ini`",
    "UPDATE `venta` SET `n_serie_ini` = `n_serie_fin`",
    "UPDATE `venta` SET `n_serie_fin` = `n_serie_tmp`",
    "ALTER TABLE `venta` DROP COLUMN `n_serie_tmp`"
];

foreach ($queries as $q) {
    echo "-> Executing: " . strtok($q, "\n") . "\n";
    if (!$mysqli->query($q)) {
        echo "Query failed: " . $mysqli->error . "\n";
        echo "Manual recovery hint: you can restore from backup table '{$backupTable}'.\n";
        exit(1);
    }
}

echo "Swap completed successfully.\n";
echo "If you need to rollback, you can drop `venta` and rename `{$backupTable}` back to `venta` after inspection.\n";

$mysqli->close();

?>
