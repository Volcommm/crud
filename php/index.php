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
            background-color: #f8f9fa;
        }
        #header, #footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
        .container {
            margin-top: 50px;
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
            echo "<div class='alert alert-warning' role='alert'>You must be logged in to view this page.</div>";
            echo "<div class='text-center'>
                    <a href='login.php' class='btn btn-primary'>Login</a> | 
                    <a href='register.php' class='btn btn-secondary'>Register</a>
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
