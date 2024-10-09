<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Make sure to exit after redirection
}

// Including the database connection file
include_once("connection.php");

// Handle form submission for adding new personale
if (isset($_POST['add'])) {
    $nome = mysqli_real_escape_string($mysqli, $_POST['nome']);
    $idtipologia = (int) $_POST['idtipologia']; // Convert to integer
    
    // Check for empty fields
    if (empty($nome) || empty($idtipologia)) {
        $errorMessages = "Tutti i campi sono obbligatori.";
    } else {
        // Insert data into the database
        $insertQuery = "INSERT INTO personale (nome, idtipologia) VALUES ('$nome', '$idtipologia')";
        $result = mysqli_query($mysqli, $insertQuery);

        if ($result) {
            header("Location: personale.php?msg=success"); // Redirect after success
            exit();
        } else {
            $errorMessages = "Errore nell'inserimento: " . mysqli_error($mysqli);
        }
    }
}

// Handle form submission for updating personale
if (isset($_POST['update'])) {
    $idpersonale = (int) $_POST['id'];
    $nome = mysqli_real_escape_string($mysqli, $_POST['nome']);
    $idtipologia = (int) $_POST['idtipologia'];

    if (empty($nome) || empty($idtipologia)) {
        $errorMessages = "Tutti i campi sono obbligatori.";
    } else {
        // Update the database
        $updateQuery = "UPDATE personale SET nome='$nome', idtipologia='$idtipologia' WHERE idpersonale=$idpersonale";
        $result = mysqli_query($mysqli, $updateQuery);

        if ($result) {
            header("Location: personale.php?msg=update_success"); // Redirect after success
            exit();
        } else {
            $errorMessages = "Errore nell'aggiornamento: " . mysqli_error($mysqli);
        }
    }
}

// Handle deletion of personale
if (isset($_GET['delete_id'])) {
    $idpersonale = (int) $_GET['delete_id'];
    $deleteQuery = "DELETE FROM personale WHERE idpersonale = $idpersonale";
    $result = mysqli_query($mysqli, $deleteQuery);

    if ($result) {
        header("Location: personale.php?msg=delete_success"); // Redirect after success
        exit();
    } else {
        $errorMessages = "Errore nella cancellazione: " . mysqli_error($mysqli);
    }
}

// Fetch unique names for filter
$nameQuery = "SELECT DISTINCT nome FROM personale";
$nameResult = mysqli_query($mysqli, $nameQuery);

// Fetch unique idtipologia for filter
$tipologiaQuery = "SELECT DISTINCT p.idtipologia, t.tipologia FROM personale p
                   JOIN tipologiepersonale t ON p.idtipologia = t.idtipologia";
$tipologiaResult = mysqli_query($mysqli, $tipologiaQuery);

// Construct the query with JOIN to fetch tipologia
$query = "SELECT p.*, t.tipologia FROM personale p
          JOIN tipologiepersonale t ON p.idtipologia = t.idtipologia";

// Add search filter if provided
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']); // Escape the search term
    $query .= " WHERE (p.nome LIKE '%$searchTerm%' OR t.tipologia LIKE '%$searchTerm%')";
}

// Add filter by name
if (!empty($_GET['nome'])) {
    $names = $_GET['nome'];
    $nameFilter = implode("','", array_map(function($name) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $name); // Escape each name
    }, $names));
    $query .= !empty($_GET['search']) ? " AND p.nome IN ('$nameFilter')" : " WHERE p.nome IN ('$nameFilter')";
}

// Add filter by tipologia
if (!empty($_GET['idtipologia'])) {
    $tipologie = $_GET['idtipologia'];
    $tipologiaFilter = implode(",", array_map('intval', $tipologie)); // Ensure it's an integer
    $query .= (!empty($_GET['search']) || !empty($_GET['nome'])) ? " AND p.idtipologia IN ($tipologiaFilter)" : " WHERE p.idtipologia IN ($tipologiaFilter)";
}

$query .= " ORDER BY p.idpersonale DESC";

$result = mysqli_query($mysqli, $query);

// Controllo errori SQL
if (!$result) {
    die("Errore nella query: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Personale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #e9ecef; }
        .container { margin-top: 50px; }
        .table th, .table td { vertical-align: middle; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .btn-edit, .btn-delete { margin: 5px; }
        .modal-header { background-color: #343a40; color: white; }
        .modal-footer .btn { flex: 1; }
        .error-message { color: red; margin-top: 15px; }
    </style>
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-primary">Home</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Aggiungi Personale</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Form for searching personale -->
        <form action="personale.php" method="GET" class="mb-3 text-center">
            <div class="mb-3">
                <input type="text" name="search" class="form-control w-50 mx-auto" id="search" placeholder="Inserisci termine da cercare">
            </div>
            <button type="submit" class="btn btn-primary">Cerca</button>
        </form>

        <!-- Form for filtering personale -->
        <form action="personale.php" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="nome" class="form-label">Filtra per Nome</label>
                    <select name="nome[]" class="form-select" multiple id="nomeSelect">
                        <option value="">Seleziona tutto</option>
                        <?php while ($nameRow = mysqli_fetch_assoc($nameResult)) { ?>
                            <option value="<?php echo htmlspecialchars($nameRow['nome']); ?>">
                                <?php echo htmlspecialchars($nameRow['nome']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="idtipologia" class="form-label">Filtra per Tipologia</label>
                    <select name="idtipologia[]" class="form-select" multiple id="tipologiaSelect">
                        <option value="">Seleziona tutto</option>
                        <?php while ($tipologiaRow = mysqli_fetch_assoc($tipologiaResult)) { ?>
                            <option value="<?php echo htmlspecialchars($tipologiaRow['idtipologia']); ?>">
                                <?php echo htmlspecialchars($tipologiaRow['tipologia']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Applica Filtri</button>
        </form>

        <!-- Display error messages -->
        <?php if (isset($errorMessages)): ?>
            <div class="error-message"><?php echo $errorMessages; ?></div>
        <?php endif; ?>

        <!-- Table to display personale -->
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipologia</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['idpersonale']; ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipologia']); ?></td>
                        <td>
                            <button class="btn btn-edit btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['idpersonale']; ?>">Modifica</button>
                            <a href="personale.php?delete_id=<?php echo $row['idpersonale']; ?>" class="btn btn-delete btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo personale?');">Cancella</a>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $row['idpersonale']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Modifica Personale</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="personale.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $row['idpersonale']; ?>">
                                        <div class="mb-3">
                                            <label for="nome" class="form-label">Nome</label>
                                            <input type="text" name="nome" class="form-control" id="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="idtipologia" class="form-label">Tipologia</label>
                                            <select name="idtipologia" class="form-select" required>
                                                <?php 
                                                $tipologiaQuery = "SELECT * FROM tipologiepersonale";
                                                $tipologiaResult = mysqli_query($mysqli, $tipologiaQuery);
                                                while ($tipologiaRow = mysqli_fetch_assoc($tipologiaResult)) { ?>
                                                    <option value="<?php echo $tipologiaRow['idtipologia']; ?>" <?php if($row['idtipologia'] == $tipologiaRow['idtipologia']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($tipologiaRow['tipologia']); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <button type="submit" name="update" class="btn btn-primary">Modifica</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for Adding New Personale -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Aggiungi nuovo Personale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="personale.php" method="POST">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" id="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="idtipologia" class="form-label">Tipologia</label>
                            <select name="idtipologia" class="form-select" required>
                                <?php 
                                $tipologiaQuery = "SELECT * FROM tipologiepersonale";
                                $tipologiaResult = mysqli_query($mysqli, $tipologiaQuery);
                                while ($tipologiaRow = mysqli_fetch_assoc($tipologiaResult)) { ?>
                                    <option value="<?php echo $tipologiaRow['idtipologia']; ?>">
                                        <?php echo htmlspecialchars($tipologiaRow['tipologia']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Aggiungi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS for modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
