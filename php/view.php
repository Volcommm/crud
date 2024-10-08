<?php
session_start();
ob_start(); // Avvia il buffer di output
?>

<?php
if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Assicurati di uscire dopo il reindirizzamento
}
?>

<?php
// including the database connection file
include_once("connection.php");

// fetching data in descending order (latest entry first)
$result = mysqli_query($mysqli, "SELECT * FROM products WHERE login_id=" . $_SESSION['id'] . " ORDER BY id DESC");

// handling form submission (from modal)
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $qty = $_POST['qty'];
    $price = $_POST['price'];
    $loginId = $_SESSION['id'];

    // checking empty fields
    if (empty($name) || empty($qty) || empty($price)) {
        // handle errors with empty fields
        $errorMessages = "";
        if (empty($name)) $errorMessages .= "<font color='red'>Name field is empty.</font><br/>";
        if (empty($qty)) $errorMessages .= "<font color='red'>Quantity field is empty.</font><br/>";
        if (empty($price)) $errorMessages .= "<font color='red'>Price field is empty.</font><br/>";

        echo $errorMessages; // Show error messages
    } else {
        // Validate price format
        if (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
            $errorMessages .= "<font color='red'>Il prezzo deve essere un numero valido con al massimo due decimali.</font><br/>";
            echo $errorMessages; // Show error messages
        } else {
            // insert data into the database
            $result = mysqli_query($mysqli, "INSERT INTO products(name, qty, price, login_id) VALUES('$name', '$qty', '$price', '$loginId')");

            if ($result) {
                header("Location: view.php?msg=success");
                exit(); // Assicurati di uscire dopo il reindirizzamento
            } else {
                header("Location: view.php?msg=error&error=" . urlencode(mysqli_error($mysqli)));
                exit(); // Assicurati di uscire dopo il reindirizzamento
            }
        }
    }
}

// Gestione della sottomissione del modulo per l'aggiornamento
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $qty = $_POST['qty'];
    $price = $_POST['price'];

    // Controllo dei campi vuoti
    if (empty($name) || empty($qty) || empty($price)) {
        // Gestione degli errori per campi vuoti
        $errorMessages = "";
        if (empty($name)) $errorMessages .= "<font color='red'>Il campo Nome è vuoto.</font><br/>";
        if (empty($qty)) $errorMessages .= "<font color='red'>Il campo Quantità è vuoto.</font><br/>";
        if (empty($price)) $errorMessages .= "<font color='red'>Il campo Prezzo è vuoto.</font><br/>";

        echo $errorMessages; // Mostra i messaggi di errore
    } else {
        // Validate price format
        if (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
            $errorMessages .= "<font color='red'>Il prezzo deve essere un numero valido con al massimo due decimali.</font><br/>";
            echo $errorMessages; // Show error messages
        } else {
            // Aggiornamento della tabella
            $result = mysqli_query($mysqli, "UPDATE products SET name='$name', qty='$qty', price='$price' WHERE id=$id");

            if ($result) {
                header("Location: view.php?msg=update_success");
                exit(); // Assicurati di uscire dopo il reindirizzamento
            } else {
                header("Location: view.php?msg=error&error=" . urlencode(mysqli_error($mysqli)));
                exit(); // Assicurati di uscire dopo il reindirizzamento
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>

    <!-- Adding Bootstrap CSS for modern style -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS for additional styling -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: center;
        }

        table th {
            background-color: #343a40;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .btn-edit {
            color: #fff;
            background-color: #007bff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-delete {
            color: #fff;
            background-color: #dc3545;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-primary">Home</a>
            <!-- Trigger the modal -->
            <button class="btn btn-success" data-toggle="modal" data-target="#addModal">Add New Data</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Check for success or error messages -->
        <?php if (isset($_GET['msg'])): ?>
            <script>
                <?php if ($_GET['msg'] === 'success'): ?>
                    alert('Product added successfully!');
                <?php elseif ($_GET['msg'] === 'update_success'): ?>
                    alert('Product updated successfully!');
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    alert('Error adding product: <?php echo htmlspecialchars($_GET['error']); ?>');
                <?php endif; ?>
            </script>
        <?php endif; ?>

        <!-- Table displaying product data -->
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price (euro)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($res = mysqli_fetch_array($result)) { ?>
                    <tr>
                        <td><?php echo $res['name']; ?></td>
                        <td><?php echo $res['qty']; ?></td>
                        <td><?php echo $res['price']; ?></td>
                        <td>
                            <button class="btn-edit" data-toggle="modal" data-target="#editModal" data-id="<?php echo $res['id']; ?>" data-name="<?php echo $res['name']; ?>" data-qty="<?php echo $res['qty']; ?>" data-price="<?php echo $res['price']; ?>">Edit</button>
                            <a href="delete.php?id=<?php echo $res['id']; ?>" class="btn-delete" onClick="return confirm('Are you sure you want to delete?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for adding new product -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="view.php" method="post">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="qty">Quantity:</label>
                            <input type="number" class="form-control" name="qty" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (euro):</label>
                            <input type="text" class="form-control" name="price" required pattern="^\d+(\.\d{1,2})?$" title="Inserisci un numero valido, ad esempio: 12.34">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" name="add" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for editing product -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="view.php" method="post">
                        <input type="hidden" name="id" id="editId">
                        <div class="form-group">
                            <label for="editName">Name:</label>
                            <input type="text" class="form-control" name="name" id="editName" required>
                        </div>
                        <div class="form-group">
                            <label for="editQty">Quantity:</label>
                            <input type="number" class="form-control" name="qty" id="editQty" required>
                        </div>
                        <div class="form-group">
                            <label for="editPrice">Price (euro):</label>
                            <input type="text" class="form-control" name="price" id="editPrice" required pattern="^\d+(\.\d{1,2})?$" title="Inserisci un numero valido, ad esempio: 12.34">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" name="update" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Including jQuery and Bootstrap JS for functionality -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Populating the edit modal with current product data
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var name = button.data('name');
            var qty = button.data('qty');
            var price = button.data('price');

            var modal = $(this);
            modal.find('#editId').val(id);
            modal.find('#editName').val(name);
            modal.find('#editQty').val(qty);
            modal.find('#editPrice').val(price);
        });
    </script>
</body>
</html>
