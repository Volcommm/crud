<?php
session_start();
ob_start(); // Avvia il buffer di output

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Assicurati di uscire dopo il reindirizzamento
}

// including the database connection file
include_once("connection.php");

// Fetch unique product names for filter
$nameQuery = "SELECT DISTINCT name FROM products WHERE login_id=" . $_SESSION['id'];
$nameResult = mysqli_query($mysqli, $nameQuery);

// Fetch unique quantities for filter
$qtyQuery = "SELECT DISTINCT qty FROM products WHERE login_id=" . $_SESSION['id'];
$qtyResult = mysqli_query($mysqli, $qtyQuery);

// Fetch unique prices for filter
$priceQuery = "SELECT DISTINCT price FROM products WHERE login_id=" . $_SESSION['id'];
$priceResult = mysqli_query($mysqli, $priceQuery);

// Construct the query with filters
$query = "SELECT * FROM products WHERE login_id=" . $_SESSION['id'];

if (!empty($_GET['name'])) {
    $names = $_GET['name'];
    $nameFilter = implode("','", array_map(function($name) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $name); // Passa anche la connessione
    }, $names)); // Escape each name
    $query .= " AND name IN ('$nameFilter')";
}

if (!empty($_GET['qty'])) {
    $quantities = $_GET['qty'];
    $qtyFilter = implode(",", array_map('intval', $quantities)); // Ensure it's an integer
    $query .= " AND qty IN ($qtyFilter)";
}

if (!empty($_GET['price'])) {
    $prices = $_GET['price'];
    $priceFilter = implode("','", array_map(function($price) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $price); // Passa anche la connessione
    }, $prices)); // Escape each price
    $query .= " AND price IN ('$priceFilter')";
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($mysqli, $query);

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

        <!-- Form for filtering products -->
        <div class="mb-3">
            <form action="view.php" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="name">Filter by Name</label>
                    <select name="name[]" class="form-control" multiple id="nameSelect">
                        <option value="">Select All</option>
                        <?php while ($nameRow = mysqli_fetch_assoc($nameResult)) { ?>
                            <option value="<?php echo htmlspecialchars($nameRow['name']); ?>">
                                <?php echo htmlspecialchars($nameRow['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="qty">Filter by Quantity</label>
                    <select name="qty[]" class="form-control" multiple id="qtySelect">
                        <option value="">Select All</option>
                        <?php while ($qtyRow = mysqli_fetch_assoc($qtyResult)) { ?>
                            <option value="<?php echo htmlspecialchars($qtyRow['qty']); ?>">
                                <?php echo htmlspecialchars($qtyRow['qty']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="price">Filter by Price</label>
                    <select name="price[]" class="form-control" multiple id="priceSelect">
                        <option value="">Select All</option>
                        <?php while ($priceRow = mysqli_fetch_assoc($priceResult)) { ?>
                            <option value="<?php echo htmlspecialchars($priceRow['price']); ?>">
                                <?php echo htmlspecialchars($priceRow['price']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['qty']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td>
                            <button class="btn-edit" data-toggle="modal" data-target="#editModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" data-qty="<?php echo htmlspecialchars($row['qty']); ?>" data-price="<?php echo htmlspecialchars($row['price']); ?>">Edit</button>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn-delete">Delete</a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="view.php" method="post">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="qty">Quantity</label>
                                <input type="number" class="form-control" name="qty" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="text" class="form-control" name="price" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" name="add" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="view.php" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="editProductId">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" name="name" id="editProductName" required>
                            </div>
                            <div class="form-group">
                                <label for="qty">Quantity</label>
                                <input type="number" class="form-control" name="qty" id="editProductQty" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="text" class="form-control" name="price" id="editProductPrice" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" name="update" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- jQuery, Bootstrap JS for modal -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            // When the edit button is clicked
            $('#editModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var name = button.data('name');
                var qty = button.data('qty');
                var price = button.data('price');

                var modal = $(this);
                modal.find('#editProductId').val(id);
                modal.find('#editProductName').val(name);
                modal.find('#editProductQty').val(qty);
                modal.find('#editProductPrice').val(price);
            });

            // Select all logic
            $('#nameSelect').change(function () {
                if ($(this).find('option[value=""]').is(':selected')) {
                    $(this).find('option').prop('selected', true);
                    $(this).find('option[value=""]').prop('selected', false);
                }
            });

            $('#qtySelect').change(function () {
                if ($(this).find('option[value=""]').is(':selected')) {
                    $(this).find('option').prop('selected', true);
                    $(this).find('option[value=""]').prop('selected', false);
                }
            });

            $('#priceSelect').change(function () {
                if ($(this).find('option[value=""]').is(':selected')) {
                    $(this).find('option').prop('selected', true);
                    $(this).find('option[value=""]').prop('selected', false);
                }
            });
        });
    </script>
</body>
</html>
