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
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 100px;
        }
        #header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="header">
            <h1>Accesso a Commessa</h1>
            <a href="index.php" class="btn btn-link">Home</a>
        </div>

        <?php
        if (isset($_POST['submit'])) {
            $user = mysqli_real_escape_string($mysqli, $_POST['username']);
            $pass = mysqli_real_escape_string($mysqli, $_POST['password']);

            if ($user == "" || $pass == "") {
                echo "<div class='alert alert-danger' role='alert'>Either username or password field is empty.</div>";
                echo "<a href='login.php' class='btn btn-primary'>Go back</a>";
            } else {
                $result = mysqli_query($mysqli, "SELECT * FROM login WHERE username='$user' AND password=md5('$pass')")
                            or die("Could not execute the select query.");
                
                $row = mysqli_fetch_assoc($result);
                
                if (is_array($row) && !empty($row)) {
                    $validuser = $row['username'];
                    $_SESSION['valid'] = $validuser;
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['id'] = $row['id'];

                    // Redirect to index.php if login is successful
                    header('Location: index.php');
                    exit(); // Assicurati di terminare lo script dopo il redirect
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Invalid username or password.</div>";
                    echo "<a href='login.php' class='btn btn-primary'>Go back</a>";
                }
            }
        } else {
        ?>
            <form name="form1" method="post" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
ob_end_flush(); // Invio dell'output finale
?>
