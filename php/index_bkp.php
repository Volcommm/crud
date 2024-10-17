<?php
session_start();
if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
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
