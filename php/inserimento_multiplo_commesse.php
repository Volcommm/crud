<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

include_once("connection.php"); // Connessione al database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idpersonale = $_POST["personale"]; // Sarà un array
    $idcommessa = $_POST["commessa"];   // Sarà un array
    $ore = (int)$_POST["ore"];
    $datains = mysqli_real_escape_string($mysqli, $_POST["data"]);

    if (!empty($idpersonale) && !empty($idcommessa) && $ore != "") {
        foreach ($idpersonale as $personale) {
            foreach ($idcommessa as $commessa) {
                $insertQuery = "INSERT INTO comm_personale (idpersonale, datains, ore, idcommessa)
                                VALUES ('$personale', '$datains', '$ore', '$commessa')";
                $result = mysqli_query($mysqli, $insertQuery);
                if (!$result) {
                    $errorMessages = "Errore nell'inserimento della lavorazione: " . mysqli_error($mysqli);
                    break 2; // Esci dai due cicli annidati in caso di errore
                }
            }
        }
        if (!isset($errorMessages)) {
            $successMessage = "Lavorazione inserita con successo!";
        }
    } else {
        $errorMessages = "Tutti i campi sono obbligatori.";
    }
}

// Query per recuperare i dati necessari per i selettori
$personaleQuery = "SELECT idpersonale, nome FROM personale";
$personaleResult = mysqli_query($mysqli, $personaleQuery);

$commessaQuery = "SELECT idcommessa, numero FROM commesse WHERE LOWER(stato) = 'aperta' ORDER BY numero DESC";
$commessaResult = mysqli_query($mysqli, $commessaQuery);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserimento Multiplo Commesse</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
	<!-- Include il CSS di Select2 -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
	
	<style>
		.form-control, .form-select {
			background-color: #f8f9fa; /* Colore di sfondo chiaro */
			color: #495057; /* Colore del testo */
			border: 1px solid #ced4da; /* Bordo leggermente più scuro */
			padding: 8px; /* Ridotto il padding */
			font-size: 13px; /* Font più piccolo */
			border-radius: 5px; /* Angoli leggermente arrotondati */
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Leggera ombra per un effetto moderno */
			transition: all 0.3s ease; /* Transizione per l'hover */
		}
		.form-control:hover, .form-select:hover {
			background-color: #ffffff; /* Colore di sfondo leggermente più chiaro all'hover */
			border-color: #80bdff; /* Bordo più marcato all'hover */
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Aumenta l'ombra */
		}
		.btn-primary {
			background-color: #007bff;
			border-color: #007bff;
			color: white;
			padding: 10px 20px;
			font-size: 14px;
			border-radius: 25px;
			transition: background-color 0.3s ease, box-shadow 0.3s ease;
		}
		
		.btn-primary:hover {
			background-color: #0056b3;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
		}
		.form-label {
			display: block;
			font-weight: bold;
			color: #495057;
			margin-bottom: 8px;
			text-align: center;
		}
		.row.mb-4 .col-md-4 {
			padding-left: 5px;
			padding-right: 5px;
		}
		.container {
			margin-top: 40px;
			padding: 20px;
			background-color: #ffffff;
			border-radius: 10px;
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Ombra più pronunciata */
		}
		.select2-container--default .select2-results__option {
			background-color: #f8f9fa; /* Colore di sfondo */
			color: #212529; /* Colore del testo */
		}
		
		.select2-container--default .select2-results__option--highlighted[aria-selected] {
			background-color: #007bff; /* Colore di sfondo per l'elemento selezionato */
			color: white; /* Colore del testo dell'elemento selezionato */
		}
		
		.select2-container--default .select2-dropdown {
			border: 1px solid #ced4da; /* Bordo del dropdown */
			border-radius: 5px; /* Bordo arrotondato */
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Ombra per effetto moderno */
		}
		.select2-container--default .select2-dropdown {
			width: auto; /* Adatta la larghezza automaticamente */
		}
		.select2-container--default .select2-results__option:hover {
			background-color: #e9ecef; /* Colore di sfondo quando si passa sopra con il mouse */
			color: #212529; /* Colore del testo all'hover */
		}
	</style>

</head>
<body id="gestione-multiple">
<div class="container">
    <h1 class="text-center display-4 mb-4">Inserimento Multiplo Commesse</h1>
    <div class="d-flex justify-content-between mb-4">
        <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
        <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Form migliorato -->
    <form action="inserimento_multiplo_commesse.php" method="POST">
        <!-- Riga per Data Intervento e Ore Lavorate -->
        <div class="row mb-4 justify-content-center">
            <div class="col-md-4">
                <label for="data" class="form-label">Data Intervento</label>
                <input type="date" name="data" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="ore" class="form-label">Ore Lavorate</label>
                <input type="number" name="ore" class="form-control" required min="0" step="1">
            </div>
        </div>
    
        <!-- Riga per Personale e Commessa -->
        <div class="row mb-4 justify-content-center">
            <div class="col-md-4">
                <label for="personale" class="form-label">Personale</label>
                <select name="personale[]" class="form-select" id="personaleSelect" multiple="multiple" required>
                    <?php while ($personaleRow = mysqli_fetch_assoc($personaleResult)) { ?>
                        <option value="<?php echo htmlspecialchars($personaleRow['idpersonale']); ?>">
                            <?php echo htmlspecialchars($personaleRow['nome']); ?>
                        </option>
                    <?php } ?>
                </select>

            </div>
            <div class="col-md-4">
                <label for="commessa" class="form-label">Commessa</label>
                <select name="commessa[]" class="form-select" id="commessaSelect" multiple="multiple" required>
                    <?php while ($commessaRow = mysqli_fetch_assoc($commessaResult)) { ?>
                        <option value="<?php echo htmlspecialchars($commessaRow['idcommessa']); ?>">
                            <?php echo htmlspecialchars($commessaRow['numero']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="text-center mb-4">
            <button type="submit" class="btn btn-primary px-5">Inserisci Lavorazione</button>
        </div>

        <!-- Messaggi di errore o successo -->
        <?php if (isset($errorMessages)): ?>
            <div class="alert alert-danger text-center">
                <?php echo $errorMessages; ?>
            </div>
        <?php elseif (isset($successMessage)): ?>
            <div class="alert alert-success text-center">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
    </form>
</div>
    <!-- Include Bootstrap, e jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Include il JavaScript di Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inizializza Select2 per il campo "personale" con un placeholder specifico
    $('#personaleSelect').select2({
        placeholder: "Seleziona personale", // Placeholder specifico per il campo personale
        allowClear: true, // Abilita il pulsante di cancellazione
        width: '100%'  // Assicurati che il campo riempia tutto lo spazio disponibile
    });

    // Inizializza Select2 per il campo "commessa" con un placeholder specifico
    $('#commessaSelect').select2({
        placeholder: "Seleziona commessa", // Placeholder specifico per il campo commessa
        allowClear: true, // Abilita il pulsante di cancellazione
        width: '100%'  // Assicurati che il campo riempia tutto lo spazio disponibile
    });

    // Forza il reset del placeholder quando si rimuove una selezione nel campo "commessa"
    $('#commessaSelect').on('select2:unselect', function (e) {
        $(this).trigger('change'); // Forza l'aggiornamento
    });

    // Forza il reset del placeholder quando si rimuove una selezione nel campo "personale"
    $('#personaleSelect').on('select2:unselect', function (e) {
        $(this).trigger('change'); // Forza l'aggiornamento
    });
});
</script>


</body>
</html>
