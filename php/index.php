<?php
session_start();
if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amministrazione Software Commessa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sfondo principale */
        body {
            background-color: #f4f6f9;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Stile del header */
        #header {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 30px;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Contenitore principale */
        .container {
            margin-top: 50px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* Messaggio di benvenuto */
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
        }

        .welcome-message .btn-logout {
            margin-left: 10px;
            font-size: 14px;
            padding: 5px 15px;
        }

        /* Stile per ogni sezione */
        .section {
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #007bff;
            cursor: pointer;
            position: relative;
            transition: color 0.5s ease;
        }

        .section-title:hover {
            color: #0056b3;
        }

        /* Effetto animato per aprire la cartella */
        .section-content {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 1s ease, opacity 0.5s ease;
            margin-top: 10px;
            padding-left: 20px;
        }

        .section:hover .section-content {
            max-height: 500px; /* Altezza massima, abbastanza alta per contenere tutto */
            opacity: 1;
        }

        /* Link stile cartella */
        .section ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .section ul li {
            margin-bottom: 10px;
        }

        .section ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 16px;
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid #007bff;
            border-radius: 5px;
            transition: background-color 0.4s, color 0.4s, box-shadow 0.3s;
        }

        .section ul li a:hover {
            background-color: #007bff;
            color: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Footer */
        #footer {
            background-color: #343a40;
            color: white;
            padding: 15px;
            font-size: 14px;
            text-align: center;
            margin-top: 50px;
            border-radius: 0 0 12px 12px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div id="header">
        Amministrazione Software Commessa
    </div>

    <!-- Messaggio di benvenuto e logout -->
    <div class="container">
        <?php if (isset($_SESSION['valid'])) { ?>
            <div class="welcome-message">
                Benvenuto, <?php echo htmlspecialchars($_SESSION['name']); ?>!
                <a href='logout.php' class="btn btn-danger btn-sm">Logout</a>
            </div>
        <?php } ?>

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

    <!-- Footer -->
    <div id="footer">
        <p>Created by Lutech Team</p>
    </div>

    <!-- Bootstrap 5 JS for functionality (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
