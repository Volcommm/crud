<?php
session_start();
if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

// Configurazione del database
$host = "crud-db-1"; // Nome del servizio MySQL in Docker Compose (di solito 'db' in un setup Docker standard)
$user = "root";
$pass = "toor";
$dbname = "crud_with_login"; // Nome del database corretto

// Nome del file di dump
$backup_file = "/tmp/" . $dbname . "_dump_" . date("Y-m-d_H-i-s") . ".sql";

// Comando mysqldump con opzioni aggiuntive per assicurare il dump completo
$command = "mysqldump --user=$user --password=$pass --host=$host --routines --triggers --databases $dbname > $backup_file";

// Esegui il comando mysqldump nel container PHP
exec($command . ' 2>&1', $output, $return_var);

// Verifica se il comando mysqldump è stato eseguito correttamente
if ($return_var !== 0) {
    echo "<pre>";
    echo "Errore durante l'esecuzione di mysqldump:\n";
    print_r($output); // Mostra l'output dell'errore
    echo "</pre>";
    exit;
} else {
    // Scarica il file se esiste
    if (file_exists($backup_file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($backup_file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backup_file));
        readfile($backup_file);

        // Elimina il file dopo il download
        unlink($backup_file);
        exit;
    } else {
        echo "Errore: impossibile generare il dump del database.";
    }
}
?>
