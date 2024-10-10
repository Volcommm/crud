<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1d1f27; /* Sfondo scuro */
            color: #f8f9fa; /* Colore del testo chiaro */
        }
        #header, #footer {
            background-color: #343a40; /* Colore di sfondo del header e footer */
            color: white; /* Colore del testo */
            text-align: center;
            padding: 10px 0;
        }
        .container {
            margin-top: 50px;
        }
        .alert {
            background-color: #2b2e38; /* Colore dello sfondo dell'alert */
            color: #f8f9fa; /* Colore del testo dell'alert */
        }
        .btn-primary {
            background-color: #007bff; /* Colore del bottone primario */
            border-color: #007bff; /* Colore del bordo del bottone primario */
        }
        .btn-danger {
            background-color: #dc3545; /* Colore del bottone per logout */
            border-color: #dc3545; /* Colore del bordo del bottone per logout */
        }
        .btn-secondary {
            background-color: #6c757d; /* Colore del bottone secondario per registrazione */
            border-color: #6c757d; /* Colore del bordo del bottone secondario */
        }
        .btn-primary:hover, .btn-danger:hover, .btn-secondary:hover {
            opacity: 0.9; /* Leggero effetto hover */
        }
    </style>
</head>

<body>
    <div id="header">
        <h1>Benvenuto in Commessa 2.0</h1>
    </div>
    
    <div class="container">
        <?php
        if (isset($_SESSION['valid'])) {			
            include("connection.php");					
            $result = mysqli_query($mysqli, "SELECT * FROM login");
        ?>
            <div class="alert alert-success" role="alert">
                Benvenuto, <?php echo htmlspecialchars($_SESSION['name']); ?>! 
                <a href='logout.php' class="btn btn-danger btn-sm">Logout</a>
            </div>
            <div class="text-center">
                <a href='personale.php' class="btn btn-primary">Visualizza Personale</a>
            </div>
        <?php	
        } else {
            echo "<div class='alert alert-warning' role='alert'>Devi essere loggato per visualizzare questa pagina.</div>";
            echo "<div class='text-center'>
                    <a href='login.php' class='btn btn-primary'>Login</a> | 
                    <a href='register.php' class='btn btn-secondary'>Registrati</a>
                  </div>";
        }
        ?>
    </div>

    <div id="footer">
        <p>Created by Lutech Team</p>
    </div>

    <!-- Bootstrap 5 JS for functionality (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
