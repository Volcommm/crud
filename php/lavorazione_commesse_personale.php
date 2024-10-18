<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
include_once("connection.php");

// Handle deletion
if (isset($_GET['delete_id'])) {
    $idcomm_personale = (int)$_GET['delete_id'];
    $deleteQuery = "DELETE FROM comm_personale WHERE idcomm_personale = $idcomm_personale";
    $result = mysqli_query($mysqli, $deleteQuery);

    if ($result) {
        header("Location: lavorazione_commesse_personale.php?msg=delete_success");
        exit();
    } else {
        $errorMessages = "Errore nella cancellazione: " . mysqli_error($mysqli);
    }
}

// Handle addition
if (isset($_POST['add'])) {
    $datains = mysqli_real_escape_string($mysqli, $_POST['datains']);
    $idcommessa = (int)$_POST['idcommessa'];
    $idpersonale = (int)$_POST['idpersonale'];
    $ore = mysqli_real_escape_string($mysqli, $_POST['ore']);

    if ($idpersonale == 0) {
        echo "Errore: Personale non valido o non selezionato.";
        exit();
    }

    $addQuery = "INSERT INTO comm_personale (datains, idcommessa, idpersonale, ore) 
                 VALUES ('$datains', '$idcommessa', '$idpersonale', '$ore')";
    $result = mysqli_query($mysqli, $addQuery);

    if ($result) {
        header("Location: lavorazione_commesse_personale.php?msg=add_success");
        exit();
    } else {
        echo "Errore nell'inserimento: " . mysqli_error($mysqli);
        exit();
    }
}

// Handle update
if (isset($_POST['update'])) {
    $idcomm_personale = (int)$_POST['id'];
    $datains = mysqli_real_escape_string($mysqli, $_POST['datains']);
    $idcommessa = (int)$_POST['idcommessa'];
    $idpersonale = (int)$_POST['idpersonale'];
    $ore = mysqli_real_escape_string($mysqli, $_POST['ore']);

    $updateQuery = "UPDATE comm_personale 
                    SET datains = '$datains', idcommessa = '$idcommessa', idpersonale = '$idpersonale', ore = '$ore'
                    WHERE idcomm_personale = $idcomm_personale";
    $result = mysqli_query($mysqli, $updateQuery);

    if ($result) {
        header("Location: lavorazione_commesse_personale.php?msg=update_success");
        exit();
    } else {
        $errorMessages = "Errore nella modifica: " . mysqli_error($mysqli);
    }
}

// Fetch data for filters
$personaleQuery = "SELECT DISTINCT nome FROM personale ORDER BY nome ASC";
$personaleResult = mysqli_query($mysqli, $personaleQuery);

$commessaQuery = "SELECT DISTINCT c.idcommessa, c.numero 
                  FROM commesse c
                  WHERE LOWER(c.stato) = 'aperta'
                  ORDER BY c.numero DESC";
$commessaResult = mysqli_query($mysqli, $commessaQuery);

// Main query
$query = "SELECT cp.idcomm_personale, c.numero AS commessa, p.nome AS personale, DATE_FORMAT(cp.datains, '%d/%m/%Y') as datai, cp.ore 
          FROM comm_personale cp
          LEFT JOIN commesse c ON cp.idcommessa = c.idcommessa
          LEFT JOIN personale p ON cp.idpersonale = p.idpersonale";

// Add search and filter options
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']);
    $query .= " WHERE (c.numero LIKE '%$searchTerm%' OR p.nome LIKE '%$searchTerm%')";
}

if (!empty($_GET['personale'])) {
    $personali = $_GET['personale'];
    $personaleFilter = implode("','", array_map(function($personale) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $personale);
    }, $personali));
    $query .= (!empty($_GET['search']) ? " AND " : " WHERE ") . "p.nome IN ('$personaleFilter')";
}

if (!empty($_GET['idcommessa'])) {
    $commesse = $_GET['idcommessa'];
    $commessaFilter = implode(",", array_map('intval', $commesse));
    $query .= (!empty($_GET['search']) || !empty($_GET['personale']) ? " AND " : " WHERE ") . "cp.idcommessa IN ($commessaFilter)";
}

$query .= " ORDER BY cp.idcomm_personale DESC";
$result = mysqli_query($mysqli, $query);

if (!$result) {
    die("Errore nella query: " . mysqli_error($mysqli));
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavorazione Commesse Personale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body id="gestione-personale">
    <div class="container">
        <h1 class="text-center display-4 mb-4">Lavorazione Commesse Personale</h1>

        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-user-plus"></i> Aggiungi Commessa-Personale</button>
            <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Export buttons -->
        <div class="mb-3 text-center">
            <a href="javascript:void(0)" class="btn btn-success" onclick="exportToExcel('<?php echo $_SERVER['QUERY_STRING']; ?>')"><i class="fas fa-file-excel"></i> Esporta in Excel</a>
            <a href="javascript:void(0)" class="btn btn-danger" onclick="exportToPDF('<?php echo $_SERVER['QUERY_STRING']; ?>')"><i class="fas fa-file-pdf"></i> Esporta in PDF</a>
        </div>

        <!-- Error messages -->
        <?php if (isset($errorMessages)): ?>
            <div class="alert alert-danger text-center">
                <?php echo $errorMessages; ?>
            </div>
        <?php endif; ?>

        <!-- Search form -->
        <form action="lavorazione_commesse_personale.php" method="GET" class="mb-3 text-center">
            <div class="input-group w-50 mx-auto">
                <input type="text" name="search" class="form-control" placeholder="Cerca personale o commessa">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <!-- Filter form -->
        <form id="filterForm" action="lavorazione_commesse_personale.php" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="personale" class="form-label">Filtra per Personale</label>
                    <select name="personale[]" class="form-select" multiple>
                        <option value="" data-select-all>Seleziona tutto</option>
                        <?php while ($personaleRow = mysqli_fetch_assoc($personaleResult)) { ?>
                            <option value="<?php echo htmlspecialchars($personaleRow['nome']); ?>">
                                <?php echo htmlspecialchars($personaleRow['nome']); ?>
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

        <!-- Table -->
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th onclick="sortTable(0, this)">Data Inserimento <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(1, this)">Commessa <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(2, this)">Personale <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(3, this)">Ore <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th class="action-column">Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['datai']); ?></td>
                        <td><?php echo htmlspecialchars($row['commessa']); ?></td>
                        <td><?php echo htmlspecialchars($row['personale']); ?></td>
                        <td><?php echo htmlspecialchars($row['ore']); ?></td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-edit btn-warning ms-2" data-id="<?php echo $row['idcomm_personale']; ?>" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fas fa-edit"></i></button>
                                <a href="lavorazione_commesse_personale.php?delete_id=<?php echo $row['idcomm_personale']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questa commessa?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

<!-- Modal for Adding New comm_personale -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Aggiungi nuova Commessa-Personale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="lavorazione_commesse_personale.php" method="POST">
                    <!-- Data Inserimento -->
                    <div class="mb-3">
                        <label for="datains" class="form-label">Data Inserimento</label>
                        <input type="date" name="datains" class="form-control" required>
                    </div>

                    <!-- Query for commesse -->

                    <?php
						$commessaResult = mysqli_query($mysqli, "SELECT idcommessa, numero FROM commesse WHERE LOWER(stato) = 'aperta' ORDER BY numero DESC");
					?>
                    <div class="mb-3">
                        <label for="idcommessa" class="form-label">Commessa</label>
                        <select name="idcommessa" class="form-select" required>
                            <option value="">Seleziona una commessa</option>
                            <?php while ($commessaRow = mysqli_fetch_assoc($commessaResult)) { ?>
                                <option value="<?php echo htmlspecialchars($commessaRow['idcommessa']); ?>">
                                    <?php echo htmlspecialchars($commessaRow['numero']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Query for personale -->
                    <?php
                    $personaleResult = mysqli_query($mysqli, "SELECT idpersonale, nome FROM personale ORDER BY nome ASC");
                    ?>
                    <div class="mb-3">
                        <label for="idpersonale" class="form-label">Personale</label>
                        <select name="idpersonale" class="form-select" required>
                            <option value="">Seleziona personale</option>
                            <?php while ($personaleRow = mysqli_fetch_assoc($personaleResult)) { ?>
                                <option value="<?php echo htmlspecialchars($personaleRow['idpersonale']); ?>">
                                    <?php echo htmlspecialchars($personaleRow['nome']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Ore Lavorate -->
                    <div class="mb-3">
                        <label for="ore" class="form-label">Ore Lavorate</label>
                        <input type="number" step="0.01" name="ore" class="form-control" required>
                    </div>

                    <button type="submit" name="add" class="btn btn-primary">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Modal Container for Edit -->
    <div id="modalContainer"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <!-- JavaScript for exporting to Excel -->
    <script>
        function exportToExcel(queryString) {
            var table = document.querySelector("table");
            var rows = [];
            table.querySelectorAll("tr").forEach(function(row) {
                var rowData = [];
                row.querySelectorAll("td, th").forEach(function(cell, cellIndex) {
                    if (cellIndex < row.cells.length - 1) {
                        rowData.push(cell.innerText);
                    }
                });
                rows.push(rowData);
            });

            var workbook = XLSX.utils.book_new();
            var worksheet = XLSX.utils.aoa_to_sheet(rows);
            XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet 1");
            XLSX.writeFile(workbook, "commesse_personale_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".xlsx");
        }
    </script>

    <!-- JavaScript for exporting to PDF -->
    <script>
        function exportToPDF(queryString) {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF();

            doc.autoTable({
                html: "table",
                theme: 'striped',
                headStyles: { fillColor: [22, 160, 133] },
                didParseCell: function(data) {
                    if (data.column.index === 4) {
                        data.cell.text = '';
                    }
                }
            });

            doc.save("commesse_personale_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf");
        }
    </script>

    <!-- JavaScript for Edit Modal -->
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
				fetch('fetch_commessa_personale_modal.php?id=' + id + '&nocache=' + new Date().getTime())
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
	let sortDirection = {};
	
	function sortTable(columnIndex, thElement) {
		let table = thElement.closest('table');
		let rows = Array.from(table.querySelectorAll('tbody tr'));
		let isAscending = sortDirection[columnIndex] !== 'asc'; // Se già ascendente, sarà discendente, altrimenti ascendente
		
		rows.sort((rowA, rowB) => {
			let cellA = rowA.cells[columnIndex].innerText.trim();
			let cellB = rowB.cells[columnIndex].innerText.trim();
	
			if (columnIndex === 0) { // Supponendo che la data sia nella colonna 0
				// Convertiamo la data da "DD/MM/YYYY" a "YYYY-MM-DD" per confrontare correttamente le date
				let [dayA, monthA, yearA] = cellA.split('/').map(Number);
				let [dayB, monthB, yearB] = cellB.split('/').map(Number);
	
				// Creiamo l'oggetto Date con il formato corretto (anno, mese -1, giorno)
				let formattedDateA = new Date(yearA, monthA - 1, dayA);
				let formattedDateB = new Date(yearB, monthB - 1, dayB);
	
				return isAscending ? formattedDateA - formattedDateB : formattedDateB - formattedDateA;
			}
	
			// Gestione delle colonne numeriche (ad esempio, la colonna delle ore)
			else if (columnIndex === 3) { // Supponendo che la colonna delle ore sia la 3
				let numA = parseFloat(cellA.replace(/[^0-9,.-]+/g, "").replace(".", "").replace(",", "."));
				let numB = parseFloat(cellB.replace(/[^0-9,.-]+/g, "").replace(".", "").replace(",", "."));
				return isAscending ? numA - numB : numB - numA;
			}
			// Ordinamento testuale (ad esempio, commessa o personale)
			else {
				return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
			}
		});
	
		// Aggiorna la tabella con le righe ordinate
		let tbody = table.querySelector('tbody');
		rows.forEach(row => tbody.appendChild(row));
	
		// Aggiorna l'icona dell'ordinamento
		table.querySelectorAll('th .sort-icon').forEach(icon => {
			icon.innerHTML = '<i class="fas fa-sort"></i>'; // Reset icone
		});
		thElement.querySelector('.sort-icon').innerHTML = isAscending ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
	
		// Memorizza la direzione di ordinamento per la colonna
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
    const searchForm = document.querySelector('form[action="lavorazione_commesse_personale.php"]');
    searchForm.addEventListener('submit', function (event) {
        handleFormSubmit(event, searchForm);
    });

    // Aggiungi event listener per il form dei filtri
    const filterForm = document.getElementById('filterForm'); // Usa l'id per selezionare il form dei filtri
    filterForm.addEventListener('submit', function (event) {
        handleFormSubmit(event, filterForm);
    });

    // Funzione per gestire la selezione di "Seleziona tutto" nei filtri
    const personaleSelect = document.querySelector('select[name="personale[]"]');
    const commessaSelect = document.querySelector('select[name="idcommessa[]"]');
    
    // Gestione "Seleziona tutto" per fornitori
    personaleSelect.addEventListener('change', function () {
        const selectAllOption = personaleSelect.querySelector('option[data-select-all]');
        if (selectAllOption.selected) {
            for (let i = 0; i < personaleSelect.options.length; i++) {
                personaleSelect.options[i].selected = true;
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
</script>
</body>
</html>
