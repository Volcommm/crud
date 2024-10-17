<?php
session_start();
if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

include_once("connection.php");

// Query per ottenere commesse con Utile % su Offerta inferiore al 50%
$commesseSotto50Query = "SELECT numero, (costooffertauscita - (costo_tot_forn_prev + costo_tot_pers_prev)) / costooffertauscita * 100 AS utile_percentuale
                         FROM commesse
                         WHERE (costooffertauscita - (costo_tot_forn_prev + costo_tot_pers_prev)) / costooffertauscita * 100 < 50";
$commesseSotto50Result = mysqli_query($mysqli, $commesseSotto50Query);

$commesseSotto50 = [];
while ($row = mysqli_fetch_assoc($commesseSotto50Result)) {
    $commesseSotto50[] = $row['numero'];
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
                <span>Benvenuto, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                <a href='logout.php' class="btn btn-danger btn-sm btn-logout">Logout</a>
            </div>
        <?php } ?>
		<?php if (count($commesseSotto50) > 0) { ?>
			<div class="alert alert-warning alert-dismissible fade show text-center" role="alert" id="commesse-banner">
				<strong>Attenzione!</strong> Le seguenti commesse hanno un Utile % su Offerta inferiore al 50%: 
				<?php echo implode(', ', $commesseSotto50); ?>
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
