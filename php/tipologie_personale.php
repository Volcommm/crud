<?php
session_start();
ob_start();

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

include_once("connection.php");

// Handle form submission for adding new tipologia
if (isset($_POST['add'])) {
    $tipologia = mysqli_real_escape_string($mysqli, $_POST['tipologia']);
    $paga = (float) $_POST['paga'];

    if (empty($tipologia) || empty($paga)) {
        $errorMessages = "Tutti i campi sono obbligatori.";
    } else {
        $insertQuery = "INSERT INTO tipologiepersonale (tipologia, paga) VALUES ('$tipologia', '$paga')";
        $result = mysqli_query($mysqli, $insertQuery);

        if ($result) {
            header("Location: tipologie_personale.php?msg=success");
            exit();
        } else {
            $errorMessages = "Errore nell'inserimento: " . mysqli_error($mysqli);
        }
    }
}

// Handle form submission for updating tipologia
if (isset($_POST['update'])) {
    $idtipologia = (int) $_POST['id'];
    $tipologia = mysqli_real_escape_string($mysqli, $_POST['tipologia']);
    $paga = (float) $_POST['paga'];

    if (empty($tipologia) || empty($paga)) {
        $errorMessages = "Tutti i campi sono obbligatori.";
    } else {
        $updateQuery = "UPDATE tipologiepersonale SET tipologia='$tipologia', paga='$paga' WHERE idtipologia=$idtipologia";
        $result = mysqli_query($mysqli, $updateQuery);

        if ($result) {
            header("Location: tipologie_personale.php?msg=update_success");
            exit();
        } else {
            $errorMessages = "Errore nell'aggiornamento: " . mysqli_error($mysqli);
        }
    }
}

// Handle deletion of tipologia
if (isset($_GET['delete_id'])) {
    $idtipologia = (int) $_GET['delete_id'];
    $deleteQuery = "DELETE FROM tipologiepersonale WHERE idtipologia = $idtipologia";
    $result = mysqli_query($mysqli, $deleteQuery);

    if ($result) {
        header("Location: tipologie_personale.php?msg=delete_success");
        exit();
    } else {
        $errorMessages = "Errore nella cancellazione: " . mysqli_error($mysqli);
    }
}

// Fetch distinct tipologie for filter
$tipologiaQuery = "SELECT DISTINCT tipologia FROM tipologiepersonale";
$tipologiaResult = mysqli_query($mysqli, $tipologiaQuery);

// Fetch distinct paga for filter
$pagaQuery = "SELECT DISTINCT paga FROM tipologiepersonale";
$pagaResult = mysqli_query($mysqli, $pagaQuery);

// Construct the query to fetch tipologia records
$query = "SELECT * FROM tipologiepersonale";

// Add search filter if provided
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']);
    $query .= " WHERE (tipologia LIKE '%$searchTerm%' OR paga LIKE '%$searchTerm%')";
}

// Add filter by tipologia
if (!empty($_GET['tipologia'])) {
    $tipologie = $_GET['tipologia'];
    $tipologiaFilter = implode("','", array_map(function($tipologia) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $tipologia);
    }, $tipologie));
    $query .= !empty($_GET['search']) ? " AND tipologia IN ('$tipologiaFilter')" : " WHERE tipologia IN ('$tipologiaFilter')";
}

// Add filter by paga
if (!empty($_GET['paga'])) {
    $pagaValues = $_GET['paga'];
    $pagaFilter = implode(",", array_map('floatval', $pagaValues)); // Ensure it's a float
    $query .= (!empty($_GET['search']) || !empty($_GET['tipologia'])) ? " AND paga IN ($pagaFilter)" : " WHERE paga IN ($pagaFilter)";
}

$query .= " ORDER BY idtipologia DESC";

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
    <title>Gestione Personale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>

<body id="gestione-personale">
    <div class="container">
	 <h1 class="text-center display-4 mb-4">Gestione Tipologie di Personale</h1>
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-user-plus"></i> Aggiungi Tipologia</button>
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

        <!-- Form for searching tipologie -->
        <form action="tipologie_personale.php" method="GET" class="mb-3 text-center">
            <div class="input-group w-50 mx-auto">
                <input type="text" name="search" class="form-control" id="search" placeholder="Inserisci termine da cercare">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <!-- Form for filtering tipologie -->
        <form id="filterForm" action="tipologie_personale.php" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label for="tipologia" class="form-label">Filtra per Tipologia</label>
                    <select name="tipologia[]" class="form-select" multiple id="tipologiaSelect">
                        <option value="" data-select-all>Seleziona tutto</option>
                        <?php while ($tipologiaRow = mysqli_fetch_assoc($tipologiaResult)) { ?>
                            <option value="<?php echo htmlspecialchars($tipologiaRow['tipologia']); ?>">
                                <?php echo htmlspecialchars($tipologiaRow['tipologia']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="paga" class="form-label">Filtra per Paga</label>
                    <select name="paga[]" class="form-select" multiple id="pagaSelect">
                        <option value="" data-select-all>Seleziona tutto</option>
                        <?php while ($pagaRow = mysqli_fetch_assoc($pagaResult)) { ?>
                            <option value="<?php echo htmlspecialchars($pagaRow['paga']); ?>">
                                <?php echo htmlspecialchars($pagaRow['paga']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-filter"></i> Applica Filtri</button>
        </form>

        <!-- Table to display tipologie -->
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th onclick="sortTable(0, this)">Tipologia <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(1, this)">Paga <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['tipologia']); ?></td>
                        <td><?php echo htmlspecialchars($row['paga']); ?></td>
                        <td class="action-column">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['idtipologia']; ?>"><i class="fas fa-edit"></i></button>
                                <a href="tipologie_personale.php?delete_id=<?php echo $row['idtipologia']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questa tipologia?');"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Modal for each tipologia -->
                    <div class="modal fade" id="editModal<?php echo $row['idtipologia']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Modifica Tipologia</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="tipologie_personale.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $row['idtipologia']; ?>">
                                        <div class="mb-3">
                                            <label for="tipologia" class="form-label">Tipologia</label>
                                            <input type="text" name="tipologia" class="form-control" id="tipologia" value="<?php echo htmlspecialchars($row['tipologia']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="paga" class="form-label">Paga Oraria</label>
                                            <input type="number" name="paga" class="form-control" id="paga" step="0.01" value="<?php echo htmlspecialchars($row['paga']); ?>" required>
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

    <!-- Add Modal for adding new tipologia -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Aggiungi nuova Tipologia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="tipologie_personale.php" method="POST">
                        <div class="mb-3">
                            <label for="tipologia" class="form-label">Tipologia</label>
                            <input type="text" name="tipologia" class="form-control" id="tipologia" required>
                        </div>
                        <div class="mb-3">
                            <label for="paga" class="form-label">Paga Oraria</label>
                            <input type="number" name="paga" class="form-control" id="paga" step="0.01" required>
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
    var filename = "tipologie_filtrate_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".xlsx";
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
            if (data.column.index === 2) data.cell.text = '';  // Rimuovi l'azione dalla colonna
        },
        columns: [
            { header: 'Tipologia', dataKey: 'tipologia' },
            { header: 'Paga', dataKey: 'paga' }
        ],
        body: Array.from(table.querySelectorAll('tbody tr')).map(row => {
            return { tipologia: row.cells[0].innerText, paga: row.cells[1].innerText };
        })
    });

    var filename = "tipologie_filtrate_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf";
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
                // Resetta i filtri selezionati
                filterForm.reset();

                // Deseleziona manualmente tutte le opzioni multiple
                const selectElements = filterForm.querySelectorAll('select[multiple]');
                selectElements.forEach(select => {
                    Array.from(select.options).forEach(option => option.selected = false);
                });
            }
        }

        // Aggiungi event listener per il form di ricerca
        const searchForm = document.querySelector('form[action="tipologie_personale.php"]');
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

		const tipologiaSelect = document.getElementById('tipologiaSelect');
		const tipologiaSelectAllOption = tipologiaSelect.querySelector('[data-select-all]');
		tipologiaSelect.addEventListener('change', function () {
			if (tipologiaSelectAllOption.selected) {
				for (let i = 0; i < tipologiaSelect.options.length; i++) {
					tipologiaSelect.options[i].selected = true;
				}
				tipologiaSelectAllOption.selected = false; // Deselect "Seleziona tutto" for the next click
			}
		});
		
		const pagaSelect = document.getElementById('pagaSelect');
		const pagaSelectAllOption = pagaSelect.querySelector('[data-select-all]');
		pagaSelect.addEventListener('change', function () {
			if (pagaSelectAllOption.selected) {
				for (let i = 0; i < pagaSelect.options.length; i++) {
					pagaSelect.options[i].selected = true;
				}
				pagaSelectAllOption.selected = false; // Deselect "Seleziona tutto" for the next click
			}
		});

    });
</script>
</body>
</html>
