<?php
include_once("connection.php");

if (isset($_GET['id'])) {
    $idcomm_fornitore = (int) $_GET['id'];

    // Prepara la query per recuperare il file e i campi necessari
    $query = "
        SELECT cf.fileallegatoriferimento, cf.rif, t.descr_rif, c.numero AS commessa
        FROM comm_fornitore cf
        LEFT JOIN commesse c ON cf.idcommessa = c.idcommessa
        LEFT JOIN fornitori f ON cf.idfornitore = f.idfornitore
        LEFT JOIN tipologieriferimenti t ON cf.idtipologia_rif = t.idtipologia_rif
        WHERE cf.idcomm_fornitore = ?";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $idcomm_fornitore);
    $stmt->execute();
    $stmt->store_result();

    // Controlla se il record esiste
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($fileAllegato, $rif, $descr_rif, $commessa);
        $stmt->fetch();

        // Determina l'estensione del file (qui è un esempio, personalizzalo in base alle tue necessità)
        $extension = 'pdf'; // Valore di default
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileAllegato);
        
        // Imposta l'estensione corretta in base al tipo MIME
        switch ($mimeType) {
            case 'application/pdf':
                $extension = 'pdf';
                break;
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            // Aggiungi altri tipi MIME e relative estensioni se necessario
            default:
                // Se non riconosciuto, puoi decidere come gestirlo
                // Ad esempio, mantenere l'estensione di default o generare un errore
                $extension = 'bin'; // oppure un errore
                break;
        }

        // Costruisci il nome del file
        $fileName = "{$commessa}-{$descr_rif}-{$rif}.$extension"; // Usa l'estensione corretta

        // Imposta gli header per il download
        header('Content-Type: ' . $mimeType); // Cambia il tipo di contenuto
        header('Content-Disposition: attachment; filename="' . $fileName . '"'); // Utilizza il valore costruito per il nome del file
        header('Content-Length: ' . strlen($fileAllegato));

        // Stampa il contenuto del file
        echo $fileAllegato;
    } else {
        echo "File non trovato.";
    }

    $stmt->close();
} else {
    echo "ID non valido.";
}
?>

