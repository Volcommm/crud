<?php
session_start();
ob_start();

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

include_once("connection.php");

// Handle form submission for adding new cliente
if (isset($_POST['add'])) {
    $cliente = mysqli_real_escape_string($mysqli, $_POST['cliente']);

    if (empty($cliente)) {
        $errorMessages = "Tutti i campi sono obbligatori.";
    } else {
        $insertQuery = "INSERT INTO clienti (cliente) VALUES ('$cliente')";
        $result = mysqli_query($mysqli, $insertQuery);

        if ($result) {
            header("Location: clienti.php?msg=success");
            exit();
        } else {
            $errorMessages = "Errore nell'inserimento: " . mysqli_error($mysqli);
        }
    }
}

// Handle form submission for updating cliente
if (isset($_POST['update'])) {
    $idcliente = (int) $_POST['id'];
    $cliente = mysqli_real_escape_string($mysqli, $_POST['cliente']);

    if (empty($cliente)) {
        $errorMessages = "Tutti i campi sono obbligatori.";
    } else {
        $updateQuery = "UPDATE clienti SET cliente='$cliente' WHERE idcliente=$idcliente";
        $result = mysqli_query($mysqli, $updateQuery);

        if ($result) {
            header("Location: clienti.php?msg=update_success");
            exit();
        } else {
            $errorMessages = "Errore nell'aggiornamento: " . mysqli_error($mysqli);
        }
    }
}

// Handle deletion of cliente
if (isset($_GET['delete_id'])) {
    $idcliente = (int) $_GET['delete_id'];
    $deleteQuery = "DELETE FROM clienti WHERE idcliente = $idcliente";
    $result = mysqli_query($mysqli, $deleteQuery);

    if ($result) {
        header("Location: clienti.php?msg=delete_success");
        exit();
    } else {
        $errorMessages = "Errore nella cancellazione: " . mysqli_error($mysqli);
    }
}

// Fetch distinct clienti for filter
$clienteQuery = "SELECT DISTINCT cliente FROM clienti";
$clienteResult = mysqli_query($mysqli, $clienteQuery);

// Construct the query to fetch cliente records
$query = "SELECT * FROM clienti";

// Add search filter if provided
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']);
    $query .= " WHERE cliente LIKE '%$searchTerm%'";
}

// Add filter by cliente
if (!empty($_GET['cliente'])) {
    $clienti = $_GET['cliente'];
    $clienteFilter = implode("','", array_map(function($cliente) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $cliente);
    }, $clienti));
    $query .= !empty($_GET['search']) ? " AND cliente IN ('$clienteFilter')" : " WHERE cliente IN ('$clienteFilter')";
}

$query .= " ORDER BY idcliente DESC";

$result = mysqli_query($mysqli, $query);

if (!$result) {
    die("Errore nella query: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione clienti</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>

<body id="gestione-fornitore">
    <div class="container">
	 <h1 class="text-center display-4 mb-4">Gestione clienti</h1>
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-user-plus"></i> Aggiungi cliente</button>
            <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
		<?php 
		// Ottieni i parametri della query string (filtri e ricerca attuali)
		$queryString = $_SERVER['QUERY_STRING'];
		?>
		<div class="mb-3 text-center">
			<a href="javascript:void(0)" class="btn btn-success" onclick="exportToExcel('<?php echo $queryString; ?>')"><i class="fas fa-file-excel"></i> Esporta in Excel</a>
			<a href="javascript:void(0)" class="btn btn-danger" onclick="exportToPDF('<?php echo $queryString; ?>')"><i class="fas fa-file-pdf"></i> Esporta in PDF</a>
		</div>

        <!-- Error messages -->
        <?php if (isset($errorMessages)): ?>
            <div class="alert alert-danger"><?php echo $errorMessages; ?></div>
        <?php endif; ?>

        <!-- Form for searching clienti -->
        <form action="clienti.php" method="GET" class="mb-3 text-center">
            <div class="input-group w-50 mx-auto">
                <input type="text" name="search" class="form-control" id="search" placeholder="Inserisci termine da cercare">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <!-- Form for filtering clienti -->
        <form id="filterForm" action="clienti.php" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label for="cliente" class="form-label">Filtra per cliente</label>
                    <select name="cliente[]" class="form-select" multiple id="clienteSelect">
                        <option value="" data-select-all>Seleziona tutto</option>
                        <?php while ($clienteRow = mysqli_fetch_assoc($clienteResult)) { ?>
                            <option value="<?php echo htmlspecialchars($clienteRow['cliente']); ?>">
                                <?php echo htmlspecialchars($clienteRow['cliente']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-filter"></i> Applica Filtri</button>
        </form>

        <!-- Table to display clienti -->
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th onclick="sortTable(0, this)">cliente <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                        <td class="action-column">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['idcliente']; ?>"><i class="fas fa-edit"></i></button>
                                <a href="clienti.php?delete_id=<?php echo $row['idcliente']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo cliente?');"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Modal for each cliente -->
                    <div class="modal fade" id="editModal<?php echo $row['idcliente']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Modifica cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="clienti.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $row['idcliente']; ?>">
                                        <div class="mb-3">
                                            <label for="cliente" class="form-label">cliente</label>
                                            <input type="text" name="cliente" class="form-control" id="cliente" value="<?php echo htmlspecialchars($row['cliente']); ?>" required>
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

    <!-- Add Modal for adding new cliente -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Aggiungi nuovo cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="clienti.php" method="POST">
                        <div class="mb-3">
                            <label for="cliente" class="form-label">cliente</label>
                            <input type="text" name="cliente" class="form-control" id="cliente" required>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Aggiungi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS for modal functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <script>
    // Funzioni per esportare in Excel e PDF
    function exportToExcel(queryString) {
        var table = document.querySelector("table");
        var rows = [];
        table.querySelectorAll("tr").forEach(function(row) {
            var rowData = [];
            row.querySelectorAll("td, th").forEach(function(cell, index) {
                if (index < row.cells.length - 1) rowData.push(cell.innerText);
            });
            rows.push(rowData);
        });

        var workbook = XLSX.utils.book_new();
        var worksheet = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet 1");
        var filename = "clienti_filtrati_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".xlsx";
        XLSX.writeFile(workbook, filename);
    }

    function exportToPDF(queryString) {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF();
        var table = document.querySelector("table");

        doc.autoTable({
            html: table,
            theme: 'striped',
            headStyles: { fillColor: [22, 160, 133] },
            didParseCell: function(data) {
                if (data.column.index === 1) data.cell.text = '';  // Rimuovi l'azione dalla colonna
            },
            columns: [
                { header: 'cliente', dataKey: 'cliente' }
            ],
            body: Array.from(table.querySelectorAll('tbody tr')).map(row => {
                return { cliente: row.cells[0].innerText };
            })
        });

        var filename = "clienti_filtrati_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf";
        doc.save(filename);
    }

    // Funzione per ordinamento
    function sortTable(columnIndex, headerElement) {
        var table = document.querySelector("table");
        var rows = Array.from(table.querySelectorAll("tbody tr"));
        var isAscending = headerElement.getAttribute("data-sort-order") === "asc";
        var multiplier = isAscending ? 1 : -1;

        rows.sort(function(rowA, rowB) {
            var cellA = rowA.querySelectorAll("td")[columnIndex].innerText.toLowerCase();
            var cellB = rowB.querySelectorAll("td")[columnIndex].innerText.toLowerCase();
            if (cellA < cellB) return -1 * multiplier;
            if (cellA > cellB) return 1 * multiplier;
            return 0;
        });

        var tbody = table.querySelector("tbody");
        tbody.innerHTML = "";
        rows.forEach(function(row) { tbody.appendChild(row); });

        headerElement.setAttribute("data-sort-order", isAscending ? "desc" : "asc");
        var sortIcon = headerElement.querySelector(".sort-icon");
        sortIcon.innerHTML = isAscending ? '<i class="fas fa-sort-down"></i>' : '<i class="fas fa-sort-up"></i>';
    }
    </script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Funzione per inviare i form via AJAX e nascondere i parametri dalla barra degli indirizzi
        function handleFormSubmit(event, form) {
            event.preventDefault(); // Prevenire il comportamento predefinito di submit

            // Ottieni i dati del form
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            // Aggiorna la tabella con i dati filtrati
            fetch(form.action + '?' + params.toString())
                .then(response => response.text())
                .then(html => {
                    // Aggiorna solo la tabella con i risultati filtrati
                    document.querySelector('table tbody').innerHTML = new DOMParser()
                        .parseFromString(html, 'text/html')
                        .querySelector('table tbody').innerHTML;

                    // Usa window.history.pushState per modificare l'URL senza ricaricare la pagina
                    const newUrl = window.location.pathname;
                    window.history.pushState({}, '', newUrl);

                    // Resetta i filtri
                    resetFilters();
                });
        }

        // Funzione per resettare i filtri
		function resetFilters() {
			const filterForm = document.getElementById('filterForm');
			if (filterForm) {
				// Resetta i campi del form, compresa la barra di ricerca
				filterForm.reset();
		
				// Resetta la barra di ricerca (se presente)
				const searchInput = document.querySelector('input[name="search"]');
				if (searchInput) {
					searchInput.value = ''; // Resetta il campo di input della ricerca
				}
		
				// Deseleziona manualmente tutte le opzioni multiple
				const selectElements = filterForm.querySelectorAll('select[multiple]');
				selectElements.forEach(select => {
					Array.from(select.options).forEach(option => option.selected = false);
				});
		
				
			}
		}

        // Aggiungi event listener per il form di ricerca
        const searchForm = document.querySelector('form[action="clienti.php"]');
        searchForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, searchForm);

            // Resetta i filtri quando viene eseguita una ricerca
            resetFilters();
        });

        // Aggiungi event listener per il form dei filtri
        const filterForm = document.getElementById('filterForm'); // Usa l'id per selezionare il form dei filtri
        filterForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, filterForm);
        });

        // Funzione per gestire la selezione di "Seleziona tutto" per cliente
        const clienteSelect = document.getElementById('clienteSelect');
        const clienteSelectAllOption = clienteSelect.querySelector('[data-select-all]');
        clienteSelect.addEventListener('change', function () {
            if (clienteSelectAllOption.selected) {
                for (let i = 0; i < clienteSelect.options.length; i++) {
                    clienteSelect.options[i].selected = true;
                }
                clienteSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
            }
        });
    });
</script>

</body>
</html>
