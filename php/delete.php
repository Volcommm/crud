<?php
session_start();
ob_start(); // Avvia il buffering di output

if(!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Assicurati di uscire dopo il reindirizzamento
}

// including the database connection file
include("connection.php");

//getting id of the data from url
$id = $_GET['id'];

//deleting the row from table
$result = mysqli_query($mysqli, "DELETE FROM products WHERE id=$id");

//redirecting to the display page (view.php in our case)
header("Location: view.php");
exit(); // Assicurati di uscire dopo il reindirizzamento

ob_end_flush(); // Termina il buffering di output
?>
