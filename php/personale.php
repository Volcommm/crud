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

		/* Sfondo chiaro */
		body {
			background-color: #f8f9fa; /* Sfondo chiaro */
			color: #212529; /* Testo scuro */
		}
		
		/* Contenitore generale con dimensioni compatte */
		.container {
			margin-top: 40px;
			padding: 15px;
			background-color: #ffffff;
			border-radius: 6px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
		}
		
		/* Tabelle con dimensioni compatte */
		.table {
			background-color: #ffffff;
			border: 1px solid #dee2e6;
			border-radius: 4px;
		}
		
		.table th, .table td {
			vertical-align: middle;
			color: #212529;
			padding: 10px; /* Ridotto il padding */
			font-size: 14px; /* Font ridotto */
		}
		
		.table-hover tbody tr:hover td {
			background-color: #f1f3f5; /* Sfondo chiaro all'hover */
		}
		/* Stile per l'intestazione della tabella */
		.table thead th {
			background-color: #f08080; /* Colore di sfondo blu */
			color: #ffffff; /* Colore del testo bianco */
			text-align: left; /* Allinea il testo al centro */
			font-weight: bold; /* Grassetto per il testo */
		}
		
		/* Per aggiungere un effetto al passaggio del mouse sulle righe dell'intestazione */
		.table thead th:hover {
			background-color: #cd5c5c; /* Colore di sfondo più scuro al passaggio del mouse */
		}

		
		/* Stile pulsanti più compatti */
		.btn {
			border-radius: 20px;
			padding: 8px 16px; /* Ridotto il padding */
			font-size: 13px; /* Ridotto il font */
			font-weight: 500; /* Font meno pesante */
			text-transform: uppercase;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); /* Ombra più leggera */
		}
		
		.btn-primary {
			background-color: #007bff;
			border-color: #007bff;
			color: #fff;
		}
		
		.btn-primary:hover {
			background-color: #0056b3;
			border-color: #004085;
		}
		
		.btn-outline-light {
			border-color: #007bff;
			color: #007bff;
		}
		
		.btn-outline-light:hover {
			background-color: #007bff;
			color: #fff;
		}
		
		/* Modale compatto */
		.modal-content {
			background-color: #ffffff;
			border-radius: 6px;
		}
		
		.modal-header, .modal-footer {
			background-color: #f1f3f5;
		}
		
		.modal-body {
			padding: 15px; /* Ridotto il padding */
		}
		
		.modal-title {
			font-size: 16px; /* Font più piccolo */
		}
		
		/* Campi form più compatti */
		.form-control, .form-select {
			background-color: #ffffff;
			color: #212529;
			border: 1px solid #ced4da;
			padding: 8px; /* Ridotto il padding */
			font-size: 13px; /* Font più piccolo */
			border-radius: 4px;
		}
		
		.form-control::placeholder {
			color: #6c757d;
		}

		/* Allarga solo i filtri per Nome e Tipologia */
		#nomeSelect, #tipologiaSelect {
			width: 100%;
		}
		
		/* Mantieni la larghezza originale della barra di ricerca */
		.input-group.w-50 {
			width: 50% !important; /* Mantiene la dimensione della barra di ricerca */
		}
		
		/* Distribuisce uniformemente le colonne dei filtri */
		.row > .col-md-4 {
			flex: 1;
		}
		/* Assicura che i filtri siano ben allineati e centrati */
		.form-label {
			display: block;
			text-align: center;
		}
		/* Centra solo il pulsante Applica Filtri */
		#filterForm .btn-primary {
			display: block;
			margin: 0 auto; /* Centra il pulsante */
			width: 25%; /* Puoi modificare la larghezza a piacimento */
		}

		/* Stile compatto per la barra di navigazione */
		.d-flex .btn {
			margin-right: 8px;
			padding: 8px 16px;
			font-size: 13px;
			border-radius: 20px;
		}
		/* Definisci una larghezza fissa per la colonna Azione */
		.action-column {
			width: 150px; /* Imposta una larghezza fissa */
			text-align: right; /* Allinea il contenuto a destra */
		}
		.action-column .btn {
			margin-right: 5px;
		}


    </style>
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-user-plus"></i> Aggiungi Personale</button>
            <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
		<?php 
		// Ottieni i parametri della query string (filtri e ricerca attuali)
		$queryString = $_SERVER['QUERY_STRING'];
		?>
		<div class="mb-3 text-center">
			<a href="javascript:void(0)" class="btn btn-success" onclick="exportToExcel('<?php echo $queryString; ?>')">Esporta in Excel</a>
			<a href="javascript:void(0)" class="btn btn-danger" onclick="exportToPDF('<?php echo $queryString; ?>')">Esporta in PDF</a>
		</div>



        <!-- Form for searching personale -->
        <form action="personale.php" method="GET" class="mb-3 text-center">
            <div class="input-group w-50 mx-auto">
                <input type="text" name="search" class="form-control" id="search" placeholder="Inserisci termine da cercare">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cerca</button>
            </div>
        </form>

        <!-- Form for filtering personale -->
		<form id="filterForm" action="personale.php" method="GET" class="mb-3">
			<div class="row">
				<div class="col-md-4">
					<label for="nome" class="form-label">Filtra per Nome</label>
					<select name="nome[]" class="form-select" multiple id="nomeSelect">
						<option value="" data-select-all>Seleziona tutto</option>
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
						<option value="" data-select-all>Seleziona tutto</option>
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
            <div class="alert alert-danger text-center" role="alert">
                <?php echo $errorMessages; ?>
            </div>
        <?php endif; ?>

        <!-- Table to display personale -->
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipologia</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipologia']); ?></td>
                        <td class="action-column">
							<div class="d-flex justify-content-end">
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Funzione per nascondere i parametri dalla barra degli indirizzi e inviare i form via AJAX
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

                    // Resetta il form dei filtri dopo che i risultati sono stati mostrati
                    form.reset();
                });
        }

        // Aggiungi event listener per il form di ricerca
        const searchForm = document.querySelector('form[action="personale.php"]');
        searchForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, searchForm);
        });

        // Aggiungi event listener per il form dei filtri
        const filterForm = document.getElementById('filterForm'); // Usa l'id per selezionare il form dei filtri
        filterForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, filterForm);
        });

        // Funzione per gestire la selezione di "Seleziona tutto" per Nome e Tipologia
        const nomeSelect = document.getElementById('nomeSelect');
        const nomeSelectAllOption = nomeSelect.querySelector('[data-select-all]');
        nomeSelect.addEventListener('change', function () {
            if (nomeSelectAllOption.selected) {
                for (let i = 0; i < nomeSelect.options.length; i++) {
                    nomeSelect.options[i].selected = true;
                }
                nomeSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
            }
        });

        const tipologiaSelect = document.getElementById('tipologiaSelect');
        const tipologiaSelectAllOption = tipologiaSelect.querySelector('[data-select-all]');
        tipologiaSelect.addEventListener('change', function () {
            if (tipologiaSelectAllOption.selected) {
                for (let i = 0; i < tipologiaSelect.options.length; i++) {
                    tipologiaSelect.options[i].selected = true;
                }
                tipologiaSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
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
			var headers = [];
			table.querySelectorAll("tr").forEach(function(row, index) {
				var rowData = [];
				row.querySelectorAll("td, th").forEach(function(cell, cellIndex) {
					// Escludi la colonna "Azione" (ultima colonna)
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
		
			// Aggiungi i filtri alla query string per il nome del file
			var filename = "personale_filtrato_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".xlsx";
		
			// Esporta in formato Excel
			XLSX.writeFile(workbook, filename);
		}


		function exportToPDF(queryString) {
			var { jsPDF } = window.jspdf;
			var doc = new jsPDF();
		
			// Seleziona solo la tabella visibile (dopo i filtri o la ricerca)
			var table = document.querySelector("table");
		
			// Usa jsPDF AutoTable per generare la tabella nel PDF escludendo l'ultima colonna
			doc.autoTable({
				html: table,
				theme: 'striped',
				headStyles: { fillColor: [22, 160, 133] },
				didParseCell: function(data) {
					// Evita la colonna "Azione" (indice 2, terza colonna)
					if (data.column.index === 2) {
						data.cell.text = '';  // Rimuovi il testo della cella
					}
				},
				// Specifica le colonne da includere (0: Nome, 1: Tipologia)
				columns: [
					{ header: 'Nome', dataKey: 'nome' },
					{ header: 'Tipologia', dataKey: 'tipologia' }
				],
				// Imposta i dati da includere nel PDF
				body: Array.from(table.querySelectorAll('tbody tr')).map(row => {
					return {
						nome: row.cells[0].innerText, // Nome
						tipologia: row.cells[1].innerText // Tipologia
					};
				})
			});
		
			// Aggiungi i filtri alla query string per il nome del file
			var filename = "personale_filtrato_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf";
		
			// Salva il PDF con il nome che include i filtri
			doc.save(filename);
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
        const searchForm = document.querySelector('form[action="personale.php"]');
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

        // Funzione per gestire la selezione di "Seleziona tutto" per Nome e Tipologia
        const nomeSelect = document.getElementById('nomeSelect');
        const nomeSelectAllOption = nomeSelect.querySelector('[data-select-all]');
        nomeSelect.addEventListener('change', function () {
            if (nomeSelectAllOption.selected) {
                for (let i = 0; i < nomeSelect.options.length; i++) {
                    nomeSelect.options[i].selected = true;
                }
                nomeSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
            }
        });

        const tipologiaSelect = document.getElementById('tipologiaSelect');
        const tipologiaSelectAllOption = tipologiaSelect.querySelector('[data-select-all]');
        tipologiaSelect.addEventListener('change', function () {
            if (tipologiaSelectAllOption.selected) {
                for (let i = 0; i < tipologiaSelect.options.length; i++) {
                    tipologiaSelect.options[i].selected = true;
                }
                tipologiaSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
            }
        });
    });
</script>
</body>
</html>
