<?php
require '../config/database.php';

$backupDir = __DIR__ . '/../backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$backupFile = $backupDir . 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
$mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe'; // Убедитесь, что путь к mysqldump правильный

$command = "{$mysqldumpPath} --user={$user} --password={$pass} --host={$host} {$db} > {$backupFile} 2>&1";
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "Backup successful.";
} else {
    echo "Backup failed. Error: " . implode("\n", $output);
}
?>
