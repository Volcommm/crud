<?php
session_start();
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            <h1>Register</h1>
            <a href="index.php" class="btn btn-link">Home</a>
        </div>

        <?php
        if (isset($_POST['submit'])) {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $user = trim($_POST['username']);
            $pass = trim($_POST['password']);

            // Check if all fields are filled
            if (empty($name) || empty($email) || empty($user) || empty($pass)) {
                echo "<div class='alert alert-danger' role='alert'>All fields should be filled. Either one or many fields are empty.</div>";
                echo "<a href='register.php' class='btn btn-primary'>Go back</a>";
            } else {
                // Prepare the insert statement
                $stmt = $mysqli->prepare("INSERT INTO login (name, email, username, password) VALUES (?, ?, ?, md5(?))");
                $stmt->bind_param("ssss", $name, $email, $user, $pass);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success' role='alert'>Registration successful</div>";
                    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Could not execute the insert query.</div>";
                }

                $stmt->close();
            }
        } else {
        ?>
            <form name="form1" method="post" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" id="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
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
