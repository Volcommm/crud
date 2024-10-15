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
    <style>
	
		/* Stile aggiornato per il titolo principale #header */
		#header {
			position: relative;
			font-weight: bold;
			color: #34495e;
			text-transform: uppercase;
			letter-spacing: 1px;
			margin-bottom: 30px;
			padding-bottom: 10px;
			text-align: center;
			background: none;
			overflow: hidden;
			font-size: 2.5rem; /* Aggiunto: dimensione del testo per renderlo più grande */
		}
		
		#header::before, #header::after {
			content: '';
			position: absolute;
			height: 2px;
			width: 50%;
			bottom: 0;
			background-color: #2980b9;
			transition: all 0.4s ease;
			z-index: 1;
		}
		
		#header::before {
			left: 0;
			background-color: #2980b9;
		}
		
		#header::after {
			right: 0;
			background-color: #e74c3c;
		}
		
		#header:hover::before, #header:hover::after {
			width: 100%;
			background-color: #2ecc71;
		}
		
		#header::before, #header::after {
			animation: move-bar 2s ease infinite alternate;
		}
		
		@keyframes move-bar {
			0% {
				width: 0;
			}
			100% {
				width: 100%;
			}
		}


        /* Sfondo principale */
        body {
            background-color: #f8f9fc;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.5s ease;
        }


        /* Contenitore principale */
        .container {
            margin-top: 60px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Messaggio di benvenuto */
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
            font-weight: 600;
            color: #007bff;
        }

        .welcome-message .btn-logout {
            margin-left: 10px;
            font-size: 14px;
            padding: 8px 20px;
            background-color: #dc3545;
            color: white;
            border-radius: 20px;
            transition: background-color 0.3s ease;
        }

        .welcome-message .btn-logout:hover {
            background-color: #c82333;
        }

        /* Contenitore per le sezioni */
        .sections-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        /* Stile per ogni sezione */
        .section {
            flex: 1;
            margin-bottom: 40px;
            background-color: #f1f1f1;
            padding: 20px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #007bff;
            cursor: pointer;
            position: relative;
            transition: color 0.5s ease;
        }

        .section-title:hover {
            color: #0056b3;
        }

        .section-content {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 1s ease, opacity 0.5s ease;
            margin-top: 10px;
            padding-left: 20px;
        }

        .section:hover .section-content {
            max-height: 500px;
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
            color: #007bff;
            font-weight: 500;
            font-size: 16px;
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid transparent;
            border-radius: 30px;
            transition: background-color 0.4s, color 0.4s, box-shadow 0.3s;
        }

        .section ul li a:hover {
            background-color: #007bff;
            color: white;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            border: 1px solid #007bff;
        }

        /* Footer spettacolare */
        #footer {
            background: linear-gradient(135deg, #6dd5ed, #2193b0);
            color: white;
            padding: 20px;
            font-size: 16px;
            text-align: center;
            margin-top: 50px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #footer p {
            margin: 0;
            font-weight: 600;
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
                Benvenuto, <?php echo htmlspecialchars($_SESSION['name']); ?>!
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
