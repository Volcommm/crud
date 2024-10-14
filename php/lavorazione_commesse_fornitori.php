<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Make sure to exit after redirection
}

// Including the database connection file
include_once("connection.php");

// Handle deletion
if (isset($_GET['delete_id'])) {
    $idcomm_fornitore = (int) $_GET['delete_id'];
    $deleteQuery = "DELETE FROM comm_fornitore WHERE idcomm_fornitore = $idcomm_fornitore";
    $result = mysqli_query($mysqli, $deleteQuery);

    if ($result) {
        header("Location: lavorazione_commesse_fornitori.php?msg=delete_success"); // Redirect after success
        exit();
    } else {
        $errorMessages = "Errore nella cancellazione: " . mysqli_error($mysqli);
    }
}
// Handle addition of new comm_fornitore
if (isset($_POST['add'])) {
    // Sanifica e recupera i dati dal form
    $datains = mysqli_real_escape_string($mysqli, $_POST['datains']);
    $idcommessa = (int) $_POST['idcommessa'];
    $idfornitore = (int) $_POST['idfornitore']; // Assicurati che sia un numero intero
    $importo = mysqli_real_escape_string($mysqli, $_POST['importo']);
    $rif = mysqli_real_escape_string($mysqli, $_POST['rif']);

    // Verifica se l'ID del fornitore è valido
    if ($idfornitore == 0) {
        echo "Errore: Fornitore non valido o non selezionato.";
        exit();
    }

    // Costruzione della query SQL per l'inserimento
    $addQuery = "INSERT INTO comm_fornitore (datains, idcommessa, idfornitore, importo, rif) 
                 VALUES ('$datains', '$idcommessa', '$idfornitore', '$importo', '$rif')";

    // Esegui la query
    $result = mysqli_query($mysqli, $addQuery);

    // Verifica se l'inserimento è avvenuto con successo
    if ($result) {
        // Reindirizza l'utente alla pagina con un messaggio di successo
        header("Location: lavorazione_commesse_fornitori.php?msg=add_success");
        exit();
    } else {
        // Mostra l'errore SQL se l'inserimento fallisce
        echo "Errore nell'inserimento: " . mysqli_error($mysqli);
        exit();
    }
}




// Handle updating of comm_fornitore
if (isset($_POST['update'])) {
    $idcomm_fornitore = (int) $_POST['id'];
    $datains = mysqli_real_escape_string($mysqli, $_POST['datains']);
    $idcommessa = (int) $_POST['idcommessa'];
    $idfornitore = (int) $_POST['idfornitore'];
    $importo = mysqli_real_escape_string($mysqli, $_POST['importo']);
    $rif = mysqli_real_escape_string($mysqli, $_POST['rif']);

    $updateQuery = "UPDATE comm_fornitore 
                    SET datains = '$datains', idcommessa = '$idcommessa', idfornitore = '$idfornitore', 
                        importo = '$importo', rif = '$rif'
                    WHERE idcomm_fornitore = $idcomm_fornitore";

    $result = mysqli_query($mysqli, $updateQuery);

    if ($result) {
        header("Location: lavorazione_commesse_fornitori.php?msg=update_success");
        exit();
    } else {
        $errorMessages = "Errore nella modifica: " . mysqli_error($mysqli);
    }
	
}

// Fetch unique names for filter (using fornitori)
$fornitoreQuery = "SELECT DISTINCT fornitore FROM fornitori";
$fornitoreResult = mysqli_query($mysqli, $fornitoreQuery);

// Fetch unique idcommessa for filter
$commessaQuery = "SELECT DISTINCT c.idcommessa, c.numero FROM commesse c";
$commessaResult = mysqli_query($mysqli, $commessaQuery);



// Construct the query with JOIN to fetch commesse, fornitori, and related data
$query = "SELECT cf.idcomm_fornitore, c.numero AS commessa, f.fornitore, DATE_FORMAT(cf.datains, '%d/%m/%Y') as datai, cf.importo, cf.rif 
          FROM comm_fornitore cf
          LEFT JOIN commesse c ON cf.idcommessa = c.idcommessa
          LEFT JOIN fornitori f ON cf.idfornitore = f.idfornitore";


// Add search filter if provided
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']); // Escape the search term
    $query .= " WHERE (c.numero LIKE '%$searchTerm%' OR f.fornitore LIKE '%$searchTerm%')";
}

// Add filter by fornitore
if (!empty($_GET['fornitore'])) {
    $fornitori = $_GET['fornitore'];
    $fornitoreFilter = implode("','", array_map(function($fornitore) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $fornitore); // Escape each fornitore
    }, $fornitori));
    $query .= !empty($_GET['search']) ? " AND f.fornitore IN ('$fornitoreFilter')" : " WHERE f.fornitore IN ('$fornitoreFilter')";
}

// Add filter by commessa
if (!empty($_GET['idcommessa'])) {
    $commesse = $_GET['idcommessa'];
    $commessaFilter = implode(",", array_map('intval', $commesse)); // Ensure it's an integer
    $query .= (!empty($_GET['search']) || !empty($_GET['fornitore'])) ? " AND cf.idcommessa IN ($commessaFilter)" : " WHERE cf.idcommessa IN ($commessaFilter)";
}

// Aggiungi il limite di righe e l'offset per la paginazione
$query .= " ORDER BY cf.idcomm_fornitore DESC";

// Esegui la query
$result = mysqli_query($mysqli, $query);

// Controllo errori SQL
if (!$result) {
    die("Errore nella query: " . mysqli_error($mysqli));
}

// Query per contare il numero totale di righe
$totalQuery = "SELECT COUNT(*) as total FROM comm_fornitore cf
               LEFT JOIN commesse c ON cf.idcommessa = c.idcommessa
               LEFT JOIN fornitori f ON cf.idfornitore = f.idfornitore";

// Aggiungi eventuali filtri di ricerca alla query del conteggio
if (!empty($_GET['search'])) {
    $totalQuery .= " WHERE (c.numero LIKE '%$searchTerm%' OR f.fornitore LIKE '%$searchTerm%')";
}
if (!empty($_GET['fornitore'])) {
    $totalQuery .= !empty($_GET['search']) ? " AND f.fornitore IN ('$fornitoreFilter')" : " WHERE f.fornitore IN ('$fornitoreFilter')";
}
if (!empty($_GET['idcommessa'])) {
    $totalQuery .= !empty($_GET['search']) || !empty($_GET['fornitore']) ? " AND cf.idcommessa IN ($commessaFilter)" : " WHERE cf.idcommessa IN ($commessaFilter)";
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavorazione Commesse Fornitori</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body id="gestione-personale">
    <div class="container">
	<h1 class="text-center display-4 mb-4">Lavorazione Commesse Fornitori</h1>
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-user-plus"></i> Aggiungi Commessa-Fornitore</button>
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

    <!-- Display error messages -->
    <?php if (isset($errorMessages)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?php echo $errorMessages; ?>
        </div>
    <?php endif; ?>

    <!-- Form for searching commesse -->
    <form action="lavorazione_commesse_fornitori.php" method="GET" class="mb-3 text-center">
        <div class="input-group w-50 mx-auto">
            <input type="text" name="search" class="form-control" placeholder="Inserisci termine da cercare">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </div>
    </form>

    <!-- Form for filtering commesse -->
    <form id="filterForm" action="lavorazione_commesse_fornitori.php" method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="fornitore" class="form-label">Filtra per Fornitore</label>
                <select name="fornitore[]" class="form-select" multiple>
                    <option value="" data-select-all>Seleziona tutto</option>
                    <?php while ($fornitoreRow = mysqli_fetch_assoc($fornitoreResult)) { ?>
                        <option value="<?php echo htmlspecialchars($fornitoreRow['fornitore']); ?>">
                            <?php echo htmlspecialchars($fornitoreRow['fornitore']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="idcommessa" class="form-label">Filtra per Commessa</label>
                <select name="idcommessa[]" class="form-select" multiple>
                    <option value="" data-select-all>Seleziona tutto</option>
                    <?php while ($commessaRow = mysqli_fetch_assoc($commessaResult)) { ?>
                        <option value="<?php echo htmlspecialchars($commessaRow['idcommessa']); ?>">
                            <?php echo htmlspecialchars($commessaRow['numero']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-filter"></i> Applica Filtri</button>
    </form>
<!-- Modal for Adding New comm_fornitore -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Aggiungi nuova Commessa-Fornitore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="lavorazione_commesse_fornitori.php" method="POST">
                    <!-- Data Inserimento (Insert Date) -->
                    <div class="mb-3">
                        <label for="datains" class="form-label">Data Inserimento</label>
                        <input type="date" name="datains" class="form-control" required>
                    </div>
                    <!-- Refetch Commessa Results -->
                    <?php
                    $commessaResult = mysqli_query($mysqli, $commessaQuery); // Refetch commessa results
                    ?>
                    <div class="mb-3">
                        <label for="idcommessa" class="form-label">Commessa</label>
                        <select name="idcommessa" class="form-select" required>
							<option value="">Seleziona una commessa</option> <!-- Opzione di default -->
                            <?php while ($commessaRow = mysqli_fetch_assoc($commessaResult)) { ?>
                                <option value="<?php echo htmlspecialchars($commessaRow['idcommessa']); ?>">
                                    <?php echo htmlspecialchars($commessaRow['numero']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
					<!-- Refetch Fornitore Results -->
					<?php
					// Esegui la query per ottenere fornitori
					$fornitoreQuery = "SELECT idfornitore, fornitore FROM fornitori";
					$fornitoreResult = mysqli_query($mysqli, $fornitoreQuery); // Refetch fornitore results
					?>
					<div class="mb-3">
						<label for="idfornitore" class="form-label">Fornitore</label>
						<select name="idfornitore" class="form-select" required>
							<option value="">Seleziona un fornitore</option> <!-- Opzione di default -->
							<?php while ($fornitoreRow = mysqli_fetch_assoc($fornitoreResult)) { ?>
								<option value="<?php echo htmlspecialchars($fornitoreRow['idfornitore']); ?>">
									<?php echo htmlspecialchars($fornitoreRow['fornitore']); ?>
								</option>
							<?php } ?>
						</select>
					</div>					
                    <!-- Importo -->
                    <div class="mb-3">
                        <label for="importo" class="form-label">Importo</label>
                        <input type="number" step="0.01" name="importo" class="form-control" required>
                    </div>
                    <!-- Riferimento -->
                    <div class="mb-3">
                        <label for="rif" class="form-label">Riferimento</label>
                        <input type="text" name="rif" class="form-control" required>
                    </div>
                    <button type="submit" name="add" class="btn btn-primary">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>


	
    <!-- Table to display commesse -->
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th onclick="sortTable(0, this)">Data Inserimento <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th onclick="sortTable(1, this)">Commessa <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th onclick="sortTable(2, this)">Fornitore <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th onclick="sortTable(3, this)">Importo <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th onclick="sortTable(4, this)">Riferimento <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                <th class="action-column">Azione</th>
            </tr>
        </thead>
        <tbody>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['datai']); ?></td>
        <td><?php echo htmlspecialchars($row['commessa']); ?></td>
        <td><?php echo htmlspecialchars($row['fornitore']); ?></td>
        <td><?php echo htmlspecialchars($row['importo']); ?></td>
        <td><?php echo htmlspecialchars($row['rif']); ?></td>
        <td>
            <div class="d-flex justify-content-end">
                <!-- Bottone per aprire il modal di modifica -->
		<button class="btn btn-edit btn-warning ms-2" data-id="<?php echo $row['idcomm_fornitore']; ?>" data-bs-toggle="modal" data-bs-target="#editModal">
			<i class="fas fa-edit"></i>
		</button>
                <a href="lavorazione_commesse_fornitori.php?delete_id=<?php echo $row['idcomm_fornitore']; ?>" class="btn btn-delete btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questa commessa?');">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </div>
        </td>
    </tr>

<div id="modalContainer"></div>



<?php } ?>




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Usa event delegation per gestire tutti i pulsanti con classe "btn-edit"
    document.querySelector('body').addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-edit')) {
            const id = event.target.getAttribute('data-id'); // Recupera l'id comm_fornitore

            // Svuota il contenuto precedente del modale solo se è aperto
            const modalContainer = document.getElementById('modalContainer');
            
            // Chiudi eventuali modali aperti prima di mostrarne uno nuovo
            var existingModal = document.getElementById('editModal');
            if (existingModal) {
                var modalInstance = bootstrap.Modal.getInstance(existingModal);
                if (modalInstance) {
                    modalInstance.hide(); // Chiude il modale precedente se aperto
                }
                modalContainer.innerHTML = ''; // Svuota il contenuto del modale precedente
            }

            // Effettua la richiesta AJAX per ottenere il contenuto del modale
            fetch('fetch_commessa_fornitore_modal.php?id=' + id + '&nocache=' + new Date().getTime())
                .then(response => response.text())
                .then(html => {
                    // Inserisce il nuovo contenuto del modale
                    modalContainer.innerHTML = html;

                    // Inizializza e mostra il nuovo modale
                    var modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();

                    // Aggiungi un event listener per svuotare il contenuto alla chiusura del modale
                    document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
                        modalContainer.innerHTML = ''; // Svuota il contenuto del modale alla chiusura
                    });
                })
                .catch(error => {
                    console.error('Errore durante il caricamento del modale:', error);
                });
        }
    });
});
</script>
<script>
    function exportToExcel(queryString) {
        // Ottieni la tabella attualmente visibile
        var table = document.querySelector("table");

        // Ottieni tutte le righe della tabella
        var rows = [];
        table.querySelectorAll("tr").forEach(function(row) {
            var rowData = [];
            row.querySelectorAll("td, th").forEach(function(cell, cellIndex) {
                // Escludi la colonna "Azione"
                if (cellIndex < row.cells.length - 1) {
                    rowData.push(cell.innerText);
                }
            });
            rows.push(rowData);
        });

        // Crea un nuovo foglio di lavoro
        var workbook = XLSX.utils.book_new();
        var worksheet = XLSX.utils.aoa_to_sheet(rows);

        // Aggiungi il foglio di lavoro al file
        XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet 1");

        // Esporta in formato Excel
        XLSX.writeFile(workbook, "commesse_fornitori_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".xlsx");
    }

    function exportToPDF(queryString) {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF();

        // Usa jsPDF AutoTable per generare la tabella nel PDF escludendo l'ultima colonna
        doc.autoTable({
            html: "table",
            theme: 'striped',
            headStyles: { fillColor: [22, 160, 133] },
            didParseCell: function(data) {
                // Evita la colonna "Azione" (ultima colonna)
                if (data.column.index === 5) {
                    data.cell.text = '';
                }
            }
        });

        // Salva il PDF con il nome che include i filtri
        doc.save("commesse_fornitori_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf");
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Funzione per inviare il form via AJAX
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
                });
        }

        // Aggiungi event listener per il form di ricerca
        const searchForm = document.querySelector('form[action="lavorazione_commesse_fornitori.php"]');
        searchForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, searchForm);
        });

        // Aggiungi event listener per il form dei filtri
        const filterForm = document.getElementById('filterForm');
        filterForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, filterForm);
        });
    });
</script>
<script>
let sortDirection = {};

function sortTable(columnIndex, thElement) {
    let table = thElement.closest('table');
    let rows = Array.from(table.querySelectorAll('tbody tr'));
    let isAscending = sortDirection[columnIndex] !== 'asc'; // Se già ascendente, sarà discendente, altrimenti ascendente
    
    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].innerText.trim();
        let cellB = rowB.cells[columnIndex].innerText.trim();

        // Gestione delle colonne in valuta (Offerta in Uscita, Costo Totale Previsto, ecc.)
        if (columnIndex === 6 || columnIndex >= 7 && columnIndex <= 10) {
            let numA = parseFloat(cellA.replace(/[^0-9.-]+/g,"")); // Rimuovi simboli non numerici
            let numB = parseFloat(cellB.replace(/[^0-9.-]+/g,""));
            return isAscending ? numA - numB : numB - numA;
        }
        // Gestione per testo (Cliente, Descrizione Lavoro, ecc.)
        else {
            return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        }
    });

    // Aggiorna la tabella
    let tbody = table.querySelector('tbody');
    rows.forEach(row => tbody.appendChild(row));

    // Aggiorna l'icona
    table.querySelectorAll('th .sort-icon').forEach(icon => {
        icon.innerHTML = '<i class="fas fa-sort"></i>'; // Resetta tutte le icone
    });
    thElement.querySelector('.sort-icon').innerHTML = isAscending ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';

    // Memorizza la direzione attuale per questa colonna
    sortDirection[columnIndex] = isAscending ? 'asc' : 'desc';
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
                
                // Esegui il reset dopo che i dati sono stati caricati
                resetFilters(form);
            });
    }

    // Funzione per resettare i filtri e i campi di ricerca
    function resetFilters(form) {
        // Resetta il form completamente
        form.reset();
    }

    // Aggiungi event listener per il form di ricerca
    const searchForm = document.querySelector('form[action="lavorazione_commesse_fornitori.php"]');
    searchForm.addEventListener('submit', function (event) {
        handleFormSubmit(event, searchForm);
    });

    // Aggiungi event listener per il form dei filtri
    const filterForm = document.getElementById('filterForm'); // Usa l'id per selezionare il form dei filtri
    filterForm.addEventListener('submit', function (event) {
        handleFormSubmit(event, filterForm);
    });

    // Funzione per gestire la selezione di "Seleziona tutto" nei filtri
    const fornitoreSelect = document.querySelector('select[name="fornitore[]"]');
    const commessaSelect = document.querySelector('select[name="idcommessa[]"]');
    
    // Gestione "Seleziona tutto" per fornitori
    fornitoreSelect.addEventListener('change', function () {
        const selectAllOption = fornitoreSelect.querySelector('option[data-select-all]');
        if (selectAllOption.selected) {
            for (let i = 0; i < fornitoreSelect.options.length; i++) {
                fornitoreSelect.options[i].selected = true;
            }
            selectAllOption.selected = false; // Deseleziona l'opzione "Seleziona tutto"
        }
    });

    // Gestione "Seleziona tutto" per commesse
    commessaSelect.addEventListener('change', function () {
        const selectAllOption = commessaSelect.querySelector('option[data-select-all]');
        if (selectAllOption.selected) {
            for (let i = 0; i < commessaSelect.options.length; i++) {
                commessaSelect.options[i].selected = true;
            }
            selectAllOption.selected = false; // Deseleziona l'opzione "Seleziona tutto"
        }
    });
});

</script>


</body>
</html>