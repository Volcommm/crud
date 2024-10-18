<?php
session_start();
ob_start(); // Inizio dell'output buffering
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Sfondo chiaro */
            color: #212529; /* Testo scuro */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            position: relative;
            overflow: hidden;
        }

        .container {
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 1.5s ease-in-out forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Titolo con barre animate */
        h1.text-center.display-4 {
            position: relative;
            font-weight: bold !important;
            color: #34495e !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            margin-bottom: 30px !important;
            padding-bottom: 10px !important;
            background: none !important;
            overflow: hidden;
            text-align: center;
        }

        h1.text-center.display-4::before, h1.text-center.display-4::after {
            content: '';
            position: absolute;
            height: 2px;
            width: 0;
            bottom: 0;
            background-color: #2980b9;
            z-index: 1;
            animation: move-bar 2s ease infinite alternate;
        }

        h1.text-center.display-4::before {
            left: 0;
            background-color: #2980b9;
        }

        h1.text-center.display-4::after {
            right: 0;
            background-color: #e74c3c;
        }

        @keyframes move-bar {
            0% {
                width: 0;
            }
            100% {
                width: 100%;
            }
        }

        /* Animazione input */
        .form-control {
            background-color: #ffffff;
            border: none;
            border-bottom: 2px solid #2980b9;
            color: #212529;
            transition: all 0.4s ease;
            padding: 10px;
        }

        .form-control:focus {
            border-bottom-color: #e74c3c;
            box-shadow: none;
        }

        /* Bottoni */
        .btn-primary {
            background-color: #2980b9;
            border-color: #2980b9;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.4s ease;
            display: block;
            width: 100%;
            margin: 20px 0;
        }

        .btn-primary:hover {
            background-color: #e74c3c;
            border-color: #e74c3c;
            transform: scale(1.05);
        }

        .alert {
            background-color: #e74c3c;
            color: white;
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            animation: slideDown 0.6s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center display-4">Accesso a Commessa</h1>

        <?php
        if (isset($_POST['submit'])) {
            $user = mysqli_real_escape_string($mysqli, $_POST['username']);
            $pass = mysqli_real_escape_string($mysqli, $_POST['password']);

            if ($user == "" || $pass == "") {
                echo "<div class='alert alert-danger' role='alert'>Il campo username o password è vuoto.</div>";
            } else {
                $result = mysqli_query($mysqli, "SELECT * FROM login WHERE username='$user' AND password=md5('$pass')") or die("Could not execute the select query.");
                
                $row = mysqli_fetch_assoc($result);
                
                if (is_array($row) && !empty($row)) {
                    $_SESSION['valid'] = $row['username'];
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['id'] = $row['id'];

                    header('Location: index.php');
                    exit();
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Username o password non validi.</div>";
                }
            }
        } else {
        ?>
            <form name="form1" method="post" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="username" required placeholder="Inserisci username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required placeholder="Inserisci password">
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Invia</button>
            </form>
        <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
ob_end_flush();
?>
