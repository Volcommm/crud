<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Make sure to exit after redirection
}

// Including the database connection file
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

// Add search filter if provided
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']); // Escape the search term
    $query .= " AND (name LIKE '%$searchTerm%' OR qty LIKE '%$searchTerm%' OR price LIKE '%$searchTerm%')";
}

if (!empty($_GET['name'])) {
    $names = $_GET['name'];
    $nameFilter = implode("','", array_map(function($name) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $name); // Escape each name
    }, $names));
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
        return mysqli_real_escape_string($mysqli, $price); // Escape each price
    }, $prices));
    $query .= " AND price IN ('$priceFilter')";
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($mysqli, $query);

// Handle form submission for adding new product
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $qty = $_POST['qty'];
    $price = $_POST['price'];
    $loginId = $_SESSION['id'];
    
    // Check for empty fields
    if (empty($name) || empty($qty) || empty($price)) {
        $errorMessages = "All fields are required.";
    } else {
        // Validate price format
        if (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
            $errorMessages = "Price must be a valid number with up to two decimals.";
        } else {
            // Insert data into the database
            $result = mysqli_query($mysqli, "INSERT INTO products(name, qty, price, login_id) VALUES('$name', '$qty', '$price', '$loginId')");

            if ($result) {
                header("Location: view.php?msg=success");
                exit();
            } else {
                $errorMessages = mysqli_error($mysqli);
            }
        }
    }
}

// Handle form submission for updating product
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $qty = $_POST['qty'];
    $price = $_POST['price'];

    // Check for empty fields
    if (empty($name) || empty($qty) || empty($price)) {
        $errorMessages = "All fields are required.";
    } else {
        // Validate price format
        if (!preg_match("/^\d+(\.\d{1,2})?$/", $price)) {
            $errorMessages = "Price must be a valid number with up to two decimals.";
        } else {
            // Update the database
            $result = mysqli_query($mysqli, "UPDATE products SET name='$name', qty='$qty', price='$price' WHERE id=$id");

            if ($result) {
                header("Location: view.php?msg=update_success");
                exit();
            } else {
                $errorMessages = mysqli_error($mysqli);
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
    <title>Product Management</title>
    <!-- Adding Bootstrap 5 CSS for modern style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #e9ecef;
        }

        .container {
            margin-top: 50px;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn-edit, .btn-delete {
            margin: 5px;
        }

        .modal-header {
            background-color: #343a40;
            color: white;
        }

        .modal-footer .btn {
            flex: 1;
        }

        .error-message {
            color: red;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-primary">Home</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Add New Data</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Form for searching products -->
        <form action="view.php" method="GET" class="mb-3 text-center">
            <div class="mb-3">
                <input type="text" name="search" class="form-control w-50 mx-auto" id="search" placeholder="Enter search term">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Form for filtering products -->
        <form action="view.php" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="name" class="form-label">Filter by Name</label>
                    <select name="name[]" class="form-select" multiple id="nameSelect">
                        <option value="">Select All</option>
                        <?php while ($nameRow = mysqli_fetch_assoc($nameResult)) { ?>
                            <option value="<?php echo htmlspecialchars($nameRow['name']); ?>">
                                <?php echo htmlspecialchars($nameRow['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="qty" class="form-label">Filter by Quantity</label>
                    <select name="qty[]" class="form-select" multiple id="qtySelect">
                        <option value="">Select All</option>
                        <?php while ($qtyRow = mysqli_fetch_assoc($qtyResult)) { ?>
                            <option value="<?php echo htmlspecialchars($qtyRow['qty']); ?>">
                                <?php echo htmlspecialchars($qtyRow['qty']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="price" class="form-label">Filter by Price</label>
                    <select name="price[]" class="form-select" multiple id="priceSelect">
                        <option value="">Select All</option>
                        <?php while ($priceRow = mysqli_fetch_assoc($priceResult)) { ?>
                            <option value="<?php echo htmlspecialchars($priceRow['price']); ?>">
                                <?php echo htmlspecialchars($priceRow['price']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Apply Filters</button>
        </form>
        
        <script>
            // Aggiungi un evento di change a tutti i select
            document.querySelectorAll('select[multiple]').forEach(select => {
                select.addEventListener('change', function() {
                    const options = select.options;
                    const isSelectAll = options[0].selected;
        
                    if (isSelectAll) {
                        // Se "Select All" è selezionato, seleziona tutte le altre opzioni
                        for (let i = 1; i < options.length; i++) {
                            options[i].selected = true;
                        }
                    } else {
                        // Se tutte le opzioni sono selezionate, seleziona anche "Select All"
                        const allSelected = Array.from(options).slice(1).every(option => option.selected);
                        options[0].selected = allSelected;
                    }
                });
            });
        </script>

        <!-- Display error messages -->
        <?php if (isset($errorMessages)): ?>
            <div class="error-message"><?php echo $errorMessages; ?></div>
        <?php endif; ?>

        <!-- Table to display products -->
        <table class="table table-hover table-bordered">
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
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['qty']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td>
                            <button class="btn btn-edit btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-delete btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="view.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" id="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="qty" class="form-label">Quantity</label>
                                            <input type="number" name="qty" class="form-control" id="qty" value="<?php echo htmlspecialchars($row['qty']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price</label>
                                            <input type="text" name="price" class="form-control" id="price" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                                        </div>
                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding New Product -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="view.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label">Quantity</label>
                            <input type="number" name="qty" class="form-control" id="qty" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="text" name="price" class="form-control" id="price" required>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS for modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
