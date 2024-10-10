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

        body {
            background-color: #1d1f27;
            color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .table {
            background-color: #2b2e38;
        }
        .table th, .table td {
            vertical-align: middle;
            color: #f8f9fa;
        }
		.table-hover tbody tr:hover td {
		background-color: #3e424d; /* Sfondo scuro */
		color: #f8f9fa !important; /* Mantieni il testo bianco */
		}


        .btn-primary, .btn-success, .btn-danger, .btn-warning {
            margin: 5px;
        }
        .modal-content {
            background-color: #2b2e38;
            color: #f8f9fa;
            border: 1px solid #343a40;
        }
        .modal-header {
            background-color: #343a40;
            color: white;
        }
        .modal-body {
            background-color: #2b2e38;
            color: #f8f9fa;
        }
        .modal-footer {
            background-color: #343a40;
        }
        .form-control, .form-select {
            background-color: #343a40;
            color: #f8f9fa;
            border: 1px solid #495057;
        }
        .form-control::placeholder {
            color: #adb5bd;
        }
        .btn-edit {
            background-color: #ffc107;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
		/* Effetto hover sui pulsanti Modifica e Cancella */
		.btn-edit:hover {
			background-color: #e0a800; /* Colore leggermente più chiaro per l'hover */
			color: #fff; /* Testo bianco */
		}
		
		.btn-delete:hover {
			background-color: #c82333; /* Colore leggermente più chiaro per l'hover */
			color: #fff; /* Testo bianco */
		}
		.input-group .form-control {
			height: 48px; /* Altezza coerente per input */
			margin: 0; /* Elimina i margini superiori e inferiori */
			border-right: 0; /* Rimuovi il bordo destro per un'unione fluida con il pulsante */
			box-sizing: border-box;
			display: flex;
			align-items: center;
		}
		
		.input-group .btn {
			height: 48px; /* Altezza coerente per il pulsante */
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 0 20px;
			margin: 0; /* Elimina i margini verticali */
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
        <form action="personale.php" method="GET" class="mb-3">
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
        // Seleziona tutto per il filtro Nome
        const nomeSelect = document.getElementById('nomeSelect');
        const nomeSelectAllOption = nomeSelect.querySelector('[data-select-all]');

        nomeSelect.addEventListener('change', function () {
            if (nomeSelectAllOption.selected) {
                for (let i = 0; i < nomeSelect.options.length; i++) {
                    nomeSelect.options[i].selected = true;
                }
            }
        });

        // Seleziona tutto per il filtro Tipologia
        const tipologiaSelect = document.getElementById('tipologiaSelect');
        const tipologiaSelectAllOption = tipologiaSelect.querySelector('[data-select-all]');

        tipologiaSelect.addEventListener('change', function () {
            if (tipologiaSelectAllOption.selected) {
                for (let i = 0; i < tipologiaSelect.options.length; i++) {
                    tipologiaSelect.options[i].selected = true;
                }
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
			columnStyles: {
				3: { cellWidth: 0 },  // Escludi la quarta colonna (indice 3), ovvero "Azione"
			},
			didParseCell: function(data) {
				// Evita la colonna "Azione" (indice 3, ultima colonna)
				if (data.column.index === 3) {
					data.cell.text = '';  // Rimuovi il testo della cella
				}
			}
		});
	
		// Aggiungi i filtri alla query string per il nome del file
		var filename = "personale_filtrato_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf";
	
		// Salva il PDF con il nome che include i filtri
		doc.save(filename);
	}



</script>

</body>
</html>
