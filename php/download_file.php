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

        // Costruisci il nome del file
        $fileName = "{$commessa}-{$descr_rif}-{$rif}.pdf"; // Sostituisci .pdf con l'estensione corretta se diversa

        // Imposta gli header per il download
        header('Content-Type: application/pdf'); // Cambia il tipo di contenuto se necessario
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

