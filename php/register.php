<?php
session_start();
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9; /* Sfondo chiaro */
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 100px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        #header {
            text-align: center;
            margin-bottom: 30px;
        }
        .alert {
            background-color: #f8d7da; /* Colore dello sfondo dell'alert */
            color: #721c24; /* Colore del testo dell'alert */
        }
        .form-control {
            background-color: #f8f9fa; /* Sfondo input chiaro */
            color: #495057; /* Colore del testo input */
            border: 1px solid #ced4da; /* Bordo dell'input */
        }
        .form-control::placeholder {
            color: #adb5bd; /* Colore del placeholder */
        }
        .btn-primary {
            background-color: #007bff; /* Colore del bottone primario */
            border-color: #007bff;
        }
        .btn-primary:hover {
            opacity: 0.9; /* Effetto hover */
        }
        .btn-link {
            font-size: 14px;
            color: #007bff;
        }
        .btn-link:hover {
            text-decoration: underline;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="header">
            <h1>Registrazione</h1>
        </div>

        <?php
        if (isset($_POST['submit'])) {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $user = trim($_POST['username']);
            $pass = trim($_POST['password']);

            // Check if all fields are filled
            if (empty($name) || empty($email) || empty($user) || empty($pass)) {
                echo "<div class='alert alert-danger' role='alert'>Tutti i campi devono essere compilati.</div>";
                echo "<a href='register.php' class='btn btn-primary'>Torna indietro</a>";
            } else {
                // Prepare the insert statement
                $stmt = $mysqli->prepare("INSERT INTO login (name, email, username, password) VALUES (?, ?, ?, md5(?))");
                $stmt->bind_param("ssss", $name, $email, $user, $pass);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success' role='alert'>Registrazione avvenuta con successo</div>";
                    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Impossibile eseguire la query di inserimento.</div>";
                }

                $stmt->close();
            }
        } else {
        ?>
            <form name="form1" method="post" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Nome Completo</label>
                    <input type="text" name="name" class="form-control" id="name" required placeholder="Inserisci il tuo nome">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="email" required placeholder="Inserisci la tua email">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="username" required placeholder="Inserisci il tuo username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required placeholder="Inserisci la tua password">
                </div>
                <div class="text-center">
                    <button type="submit" name="submit" class="btn btn-primary">Registrati</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="btn btn-link">Hai già un account? Accedi</a>
            </div>
        <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
