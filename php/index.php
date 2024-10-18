<?php
session_start();
if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

include_once("connection.php");

$commesseSottoPercAlertQuery = "
    SELECT commesse.numero,
           commesse.costooffertauscita,
           (COALESCE((SELECT SUM(cf.importo) 
                      FROM comm_fornitore cf 
                      WHERE cf.idcommessa = commesse.idcommessa), 0) 
            + COALESCE((SELECT SUM(cp.ore * tp.paga)
                        FROM comm_personale cp
                        JOIN personale p ON p.idpersonale = cp.idpersonale
                        JOIN tipologiepersonale tp ON p.idtipologia = tp.idtipologia
                        WHERE cp.idcommessa = commesse.idcommessa), 0)) AS spesa_totale,
           (commesse.costooffertauscita - 
           (COALESCE((SELECT SUM(cf.importo) 
                      FROM comm_fornitore cf 
                      WHERE cf.idcommessa = commesse.idcommessa), 0) 
            + COALESCE((SELECT SUM(cp.ore * tp.paga)
                        FROM comm_personale cp
                        JOIN personale p ON p.idpersonale = cp.idpersonale
                        JOIN tipologiepersonale tp ON p.idtipologia = tp.idtipologia
                        WHERE cp.idcommessa = commesse.idcommessa), 0))) AS utile_commessa,
           ROUND(((commesse.costooffertauscita - 
                   (COALESCE((SELECT SUM(cf.importo) 
                              FROM comm_fornitore cf 
                              WHERE cf.idcommessa = commesse.idcommessa), 0) 
                    + COALESCE((SELECT SUM(cp.ore * tp.paga)
                                FROM comm_personale cp
                                JOIN personale p ON p.idpersonale = cp.idpersonale
                                JOIN tipologiepersonale tp ON p.idtipologia = tp.idtipologia
                                WHERE cp.idcommessa = commesse.idcommessa), 0))) / commesse.costooffertauscita) * 100, 2) AS utile_percentuale,
           commesse.percalert
    FROM commesse
    WHERE commesse.costooffertauscita > 0  -- Solo se esiste un'offerta valida
	AND LOWER(commesse.stato) = 'aperta'  -- Solo commesse con stato 'aperta' o 'Aperta'
    HAVING utile_percentuale < commesse.percalert";


$commesseSottoPercAlertResult = mysqli_query($mysqli, $commesseSottoPercAlertQuery);

// Array to store commesse details

$commesseSottoPercAlert = [];
while ($row = mysqli_fetch_assoc($commesseSottoPercAlertResult)) {
    // Aggiungi ogni numero di commessa al tuo array
    $commesseSottoPercAlert[] = $row['numero'];
}






?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amministrazione Software Taceservice Commessa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="style_index.css">
<style>
		#commesse-banner {
			margin-bottom: 20px;
			padding: 15px;
			background-color: #f0ad4e; /* Colore arancione per attirare l'attenzione */
			color: white;
			font-weight: bold;
			border-radius: 5px;
		}
		/* Stile per il pulsante di Dump DB */
		/* Pulsante di Dump DB */
		.welcome-message .btn-dump-db {
			font-size: 14px;
			padding: 10px 20px;
			background-color: #3498db;
			color: white;
			border-radius: 20px;
			transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
			box-shadow: 0 4px 10px rgba(52, 152, 219, 0.2);
			position: absolute;
			left: 0; /* Posiziona il pulsante a sinistra */
			top: 50%;
			transform: translateY(-50%);
		}
		
		.welcome-message .btn-dump-db:hover {
			background-color: #2980b9;
			box-shadow: 0 6px 12px rgba(52, 152, 219, 0.3);
			transform: translateY(-50%) scale(1.05);
		}
</style>
</head>

<body>
    <!-- Header -->
    <div id="header">
        Amministrazione Software Taceservice Commessa
    </div>

<!-- Messaggio di benvenuto e logout -->
<div class="container">
    <?php if (isset($_SESSION['valid'])) { ?>
        <div class="welcome-message">
            <a href='db_dump.php' class="btn btn-info btn-sm btn-dump-db">Esegui Dump DB</a>
            <span>Benvenuto, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            <a href='logout.php' class="btn btn-danger btn-sm btn-logout">Logout</a>
        </div>
    <?php } ?>

    <?php if (count($commesseSottoPercAlert) > 0) { ?>
        <div class="alert alert-warning alert-dismissible fade show text-center" role="alert" id="commesse-banner">
            <strong>Attenzione!</strong> Le seguenti commesse hanno una % di utile inferiore alla loro soglia di percentuale : 
            <?php echo implode(', ', $commesseSottoPercAlert); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

        <!-- Contenitore delle sezioni -->
        <div class="sections-container">
            <!-- Sezione 1: Gestione Dati Commesse -->
            <div class="section">
                <h2 class="section-title">Gestione Dati Commesse</h2>
                <div class="section-content">
                    <ul>
                        <li><a href="personale.php">Personale</a></li>
                        <li><a href="tipologie_personale.php">Tipologie Personale</a></li>
                        <li><a href="fornitori.php">Fornitori</a></li>
                        <li><a href="clienti.php">Clienti</a></li>
                        <li><a href="commesse.php">Commesse</a></li>
						<li><a href="tipologie_riferimenti.php">Tipologie Riferimenti</a></li>
                    </ul>
                </div>
            </div>

            <!-- Sezione 2: Gestione Lavorazione Commesse -->
            <div class="section">
                <h2 class="section-title">Gestione Lavorazione Commesse</h2>
                <div class="section-content">
                    <ul>
                        <li><a href="lavorazione_commesse_fornitori.php">Lavorazione Commesse Fornitori</a></li>
                        <li><a href="lavorazione_commesse_personale.php">Lavorazione Commesse Personale</a></li>
                        <li><a href="inserimento_multiplo_commesse.php">Inserimento Multiplo Commesse</a></li>
                    </ul>
                </div>
            </div>

            <!-- Sezione 3: Statistiche e controllo Commesse -->
            <div class="section">
                <h2 class="section-title">Statistiche e controllo Commesse</h2>
                <div class="section-content">
                    <ul>
                        <li><a href="statistiche_commesse_personale.php">Statistiche Commesse per Personale</a></li>
                        <li><a href="statistiche_commesse_fornitori.php">Statistiche Commesse per Fornitore</a></li>
                        <li><a href="statistiche_commesse_totali.php">Statistiche Commesse Totali</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer">
        <p>Created by Lutech Team</p>
    </div>

    <!-- Bootstrap 5 JS for functionality (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
