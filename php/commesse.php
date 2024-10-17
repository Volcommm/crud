<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Make sure to exit after redirection
}

// Including the database connection file
include_once("connection.php");

// Handle form submission for adding new commessa
if (isset($_POST['add'])) {
    $numero = mysqli_real_escape_string($mysqli, $_POST['numero']);
    $rifoff = mysqli_real_escape_string($mysqli, $_POST['rifoff']);
    $stato = mysqli_real_escape_string($mysqli, $_POST['stato']);
    $dataapertura = mysqli_real_escape_string($mysqli, $_POST['dataapertura']);
    $idcliente = (int) $_POST['idcliente'];
    $deslavoro = mysqli_real_escape_string($mysqli, $_POST['deslavoro']);
    $costooffertauscita = mysqli_real_escape_string($mysqli, $_POST['costooffertauscita']);
    $costo_tot_comm_prev = mysqli_real_escape_string($mysqli, $_POST['costo_tot_comm_prev']);
    $costo_tot_forn_prev = mysqli_real_escape_string($mysqli, $_POST['costo_tot_forn_prev']);
    $costo_tot_pers_prev = mysqli_real_escape_string($mysqli, $_POST['costo_tot_pers_prev']);
    $datachiusura = mysqli_real_escape_string($mysqli, $_POST['datachiusura']);
    $dataapertura = $_POST['dataapertura'];
    // Gestione dei campi di costo: se vuoti, impostali su 0
    $costooffertauscita = !empty($_POST['costooffertauscita']) ? mysqli_real_escape_string($mysqli, $_POST['costooffertauscita']) : 0;
    $costo_tot_comm_prev = !empty($_POST['costo_tot_comm_prev']) ? mysqli_real_escape_string($mysqli, $_POST['costo_tot_comm_prev']) : 0;
    $costo_tot_forn_prev = !empty($_POST['costo_tot_forn_prev']) ? mysqli_real_escape_string($mysqli, $_POST['costo_tot_forn_prev']) : 0;
    $costo_tot_pers_prev = !empty($_POST['costo_tot_pers_prev']) ? mysqli_real_escape_string($mysqli, $_POST['costo_tot_pers_prev']) : 0;
    // Gestione dei campi di data: se vuoti, impostali su NULL
    $dataapertura = !empty($_POST['dataapertura']) ? "'" . mysqli_real_escape_string($mysqli, $_POST['dataapertura']) . "'" : "NULL";
    $datachiusura = !empty($_POST['datachiusura']) ? "'" . mysqli_real_escape_string($mysqli, $_POST['datachiusura']) . "'" : "NULL";

    // Check for empty fields
    if (empty($numero)) {
        $errorMessages = "Tutti i campi obbligatori devono essere compilati.";
    } else {
        // Insert data into the database
        $insertQuery = "INSERT INTO commesse (numero, rifoff, stato, dataapertura, idcliente, deslavoro, costooffertauscita, costo_tot_comm_prev, costo_tot_forn_prev, costo_tot_pers_prev, datachiusura) 
                        VALUES ('$numero', '$rifoff', '$stato', $dataapertura, '$idcliente', '$deslavoro', '$costooffertauscita', '$costo_tot_comm_prev', '$costo_tot_forn_prev', '$costo_tot_pers_prev', $datachiusura)";
        $result = mysqli_query($mysqli, $insertQuery);

        if ($result) {
            header("Location: commesse.php?msg=success");
            exit();
        } else {
            $errorMessages = "Errore nell'inserimento: " . mysqli_error($mysqli);
        }
    }
}

// Handle form submission for updating commessa
if (isset($_POST['update'])) {
    $idcommessa = (int) $_POST['id'];
    $numero = mysqli_real_escape_string($mysqli, $_POST['numero']);
    $rifoff = mysqli_real_escape_string($mysqli, $_POST['rifoff']);
    $stato = mysqli_real_escape_string($mysqli, $_POST['stato']);
    $dataapertura = mysqli_real_escape_string($mysqli, $_POST['dataapertura']);
    $idcliente = (int) $_POST['idcliente'];
    $deslavoro = mysqli_real_escape_string($mysqli, $_POST['deslavoro']);
    $costooffertauscita = mysqli_real_escape_string($mysqli, $_POST['costooffertauscita']);
    $costo_tot_comm_prev = mysqli_real_escape_string($mysqli, $_POST['costo_tot_comm_prev']);
    $costo_tot_forn_prev = mysqli_real_escape_string($mysqli, $_POST['costo_tot_forn_prev']);
    $costo_tot_pers_prev = mysqli_real_escape_string($mysqli, $_POST['costo_tot_pers_prev']);
    $datachiusura = mysqli_real_escape_string($mysqli, $_POST['datachiusura']);
	$dataapertura = $_POST['dataapertura'];
    // Gestione dei campi di costo: se vuoti, impostali su 0
    $costooffertauscita = !empty($_POST['costooffertauscita']) ? mysqli_real_escape_string($mysqli, $_POST['costooffertauscita']) : 0;
    $costo_tot_comm_prev = !empty($_POST['costo_tot_comm_prev']) ? mysqli_real_escape_string($mysqli, $_POST['costo_tot_comm_prev']) : 0;
    $costo_tot_forn_prev = !empty($_POST['costo_tot_forn_prev']) ? mysqli_real_escape_string($mysqli, $_POST['costo_tot_forn_prev']) : 0;
    $costo_tot_pers_prev = !empty($_POST['costo_tot_pers_prev']) ? mysqli_real_escape_string($mysqli, $_POST['costo_tot_pers_prev']) : 0;

    // Gestione dei campi di data: se vuoti, impostali su NULL
    $dataapertura = !empty($_POST['dataapertura']) ? "'" . mysqli_real_escape_string($mysqli, $_POST['dataapertura']) . "'" : "NULL";
    $datachiusura = !empty($_POST['datachiusura']) ? "'" . mysqli_real_escape_string($mysqli, $_POST['datachiusura']) . "'" : "NULL";


    if (empty($numero)) {
        $errorMessages = "Tutti i campi obbligatori devono essere compilati.";
    } else {
        // Update the database
        $updateQuery = "UPDATE commesse SET numero='$numero', rifoff='$rifoff', stato='$stato', dataapertura=$dataapertura, idcliente='$idcliente', 
                        deslavoro='$deslavoro', costooffertauscita='$costooffertauscita', costo_tot_comm_prev='$costo_tot_comm_prev', 
                        costo_tot_forn_prev='$costo_tot_forn_prev', costo_tot_pers_prev='$costo_tot_pers_prev', datachiusura=$datachiusura
                        WHERE idcommessa=$idcommessa";
        $result = mysqli_query($mysqli, $updateQuery);

        if ($result) {
            header("Location: commesse.php?msg=update_success");
            exit();
        } else {
            $errorMessages = "Errore nell'aggiornamento: " . mysqli_error($mysqli);
        }
    }
}
// Handle restore of archived commessa
if (isset($_GET['restore_id'])) {
    $idcommessa = (int) $_GET['restore_id'];
    $restoreQuery = "UPDATE commesse SET stato = 'Attiva' WHERE idcommessa = $idcommessa";
    $result = mysqli_query($mysqli, $restoreQuery);

    if ($result) {
        header("Location: commesse.php?msg=restore_success");
        exit();
    } else {
        $errorMessages = "Errore nel ripristino: " . mysqli_error($mysqli);
    }
}

// Handle deletion of commessa
if (isset($_GET['delete_id'])) {
    $idcommessa = (int) $_GET['delete_id'];
    $deleteQuery = "DELETE FROM commesse WHERE idcommessa = $idcommessa";
    $result = mysqli_query($mysqli, $deleteQuery);

    if ($result) {
        header("Location: commesse.php?msg=delete_success");
        exit();
    } else {
        $errorMessages = "Errore nella cancellazione: " . mysqli_error($mysqli);
    }
}

// Fetch unique numbers for filter
$numeroQuery = "SELECT DISTINCT numero FROM commesse";
$numeroResult = mysqli_query($mysqli, $numeroQuery);

// Fetch unique clients for filter
$clienteQuery = "SELECT DISTINCT c.idcliente, c.cliente FROM commesse co
                   JOIN clienti c ON co.idcliente = c.idcliente";
$clienteResult = mysqli_query($mysqli, $clienteQuery);

// Modifica della query per filtrare le commesse
$query = "SELECT co.*, c.cliente FROM commesse co LEFT JOIN clienti c ON co.idcliente = c.idcliente WHERE co.stato != 'Archiviata'";

// Add search filter if provided
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($mysqli, $_GET['search']);
    $query .= " AND (co.numero LIKE '%$searchTerm%' OR c.cliente LIKE '%$searchTerm%')";
}

// Add filter by numero
if (!empty($_GET['numero'])) {
    $numeri = $_GET['numero'];
    $numeroFilter = implode("','", array_map(function($numero) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $numero);
    }, $numeri));
    $query .= " AND co.numero IN ('$numeroFilter')";
}

// Add filter by cliente
if (!empty($_GET['idcliente'])) {
    $clienti = $_GET['idcliente'];
    $clienteFilter = implode(",", array_map('intval', $clienti));
    $query .= " AND co.idcliente IN ($clienteFilter)";
}

// Add filter by stato
if (!empty($_GET['stato'])) {
    $stati = $_GET['stato'];
    $statoFilter = implode("','", array_map(function($stato) use ($mysqli) {
        return mysqli_real_escape_string($mysqli, $stato);
    }, $stati));
    $query .= " AND co.stato IN ('$statoFilter')";
}

$query .= " ORDER BY co.idcommessa DESC";
$result = mysqli_query($mysqli, $query);


// Controllo errori SQL
if (!$result) {
    die("Errore nella query: " . mysqli_error($mysqli));
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Commesse</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	<link rel="stylesheet" href="style.css">

</head>
<body id="gestione-commesse">
    <div class="container">
	<h1 class="text-center display-4 mb-4">Gestione Commesse</h1>
        <div class="d-flex justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-user-plus"></i> Aggiungi Commessa</button>
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

		<!-- Bottone per mostrare le commesse archiviate -->
		<div class="mb-3 text-center">
			<button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#archiviateModal">
				<i class="fas fa-archive"></i> Mostra Archiviati
			</button>
		</div>


        <!-- Form for searching commessa -->
        <form action="commesse.php" method="GET" class="mb-3 text-center">
            <div class="input-group w-50 mx-auto">
                <input type="text" name="search" class="form-control" id="search" placeholder="Inserisci termine da cercare">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>
		<!-- Form for filtering commesse -->
		<form id="filterForm" action="commesse.php" method="GET" class="mb-3">
			<div class="row">
				<div class="col-md-4">
					<label for="numero" class="form-label">Filtra per Numero Commessa</label>
					<select name="numero[]" class="form-select" multiple id="numeroSelect">
						<option value="" data-select-all>Seleziona tutto</option>
						<?php while ($numeroRow = mysqli_fetch_assoc($numeroResult)) { ?>
							<option value="<?php echo htmlspecialchars($numeroRow['numero']); ?>">
								<?php echo htmlspecialchars($numeroRow['numero']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-4">
					<label for="idcliente" class="form-label">Filtra per Cliente</label>
					<select name="idcliente[]" class="form-select" multiple id="clienteSelect">
						<option value="" data-select-all>Seleziona tutto</option>
						<?php while ($clienteRow = mysqli_fetch_assoc($clienteResult)) { ?>
							<option value="<?php echo htmlspecialchars($clienteRow['idcliente']); ?>">
								<?php echo htmlspecialchars($clienteRow['cliente']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-4">
					<label for="stato" class="form-label">Filtra per Stato</label>
					<select name="stato[]" class="form-select" multiple id="statoSelect">
						<option value="" data-select-all>Seleziona tutto</option>
						<option value="aperta">Aperta</option>
						<option value="Chiusa">Chiusa</option>
					</select>
				</div>
			</div>
			<button type="submit" class="btn btn-primary mt-3"><i class="fas fa-filter"></i> Applica Filtri</button>
		</form>

       

        <!-- Display error messages -->
        <?php if (isset($errorMessages)): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo $errorMessages; ?>
            </div>
        <?php endif; ?>

        <!-- Table to display commesse -->
        <div class="table-responsive">
			<table class="table table-hover table-bordered">
            	<thead>
		    <tr>
			<th onclick="sortTable(0, this)">Numero Commessa <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(1, this)">Riferimento Offerta <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(2, this)">Stato <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(3, this)">Data Apertura <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(4, this)">Cliente <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(5, this)">Descrizione Lavoro <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(6, this)">Offerta in Uscita <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(7, this)">Costo Totale Previsto <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(8, this)">Costo Fornitori Previsto <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(9, this)">Costo Personale Previsto <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th onclick="sortTable(10, this)">Data Chiusura <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
			<th class="action-column">Azione</th>
		    </tr>
		</thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['numero']); ?></td>
                        <td><?php echo htmlspecialchars($row['rifoff']); ?></td>
                        <td><?php echo htmlspecialchars($row['stato']); ?></td>
                        <td><?php echo !empty($row['dataapertura']) ? date('d-m-Y', strtotime($row['dataapertura'])) : ''; ?></td>
                        <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['deslavoro']); ?></td>
						<td>
							<?php 
							echo '€ ' . (floor($row['costooffertauscita']) == $row['costooffertauscita'] 
								? number_format($row['costooffertauscita'], 0, ',', '.') 
								: number_format($row['costooffertauscita'], 2, ',', '.')); 
							?>
						</td>
						<td>
							<?php 
							echo '€ ' . (floor($row['costo_tot_comm_prev']) == $row['costo_tot_comm_prev'] 
								? number_format($row['costo_tot_comm_prev'], 0, ',', '.') 
								: number_format($row['costo_tot_comm_prev'], 2, ',', '.')); 
							?>
						</td>
						<td>
							<?php 
							echo '€ ' . (floor($row['costo_tot_forn_prev']) == $row['costo_tot_forn_prev'] 
								? number_format($row['costo_tot_forn_prev'], 0, ',', '.') 
								: number_format($row['costo_tot_forn_prev'], 2, ',', '.')); 
							?>
						</td>
						<td>
							<?php 
							echo '€ ' . (floor($row['costo_tot_pers_prev']) == $row['costo_tot_pers_prev'] 
								? number_format($row['costo_tot_pers_prev'], 0, ',', '.') 
								: number_format($row['costo_tot_pers_prev'], 2, ',', '.')); 
							?>
						</td>
                        <td><?php echo !empty($row['datachiusura']) ? date('d-m-Y', strtotime($row['datachiusura'])) : ''; ?></td>
                        <td class="action-column">
							<div class="d-flex justify-content-end">
                            <button class="btn btn-edit btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['idcommessa']; ?>"><i class="fas fa-edit"></i></button>
                            <a href="commesse.php?delete_id=<?php echo $row['idcommessa']; ?>" class="btn btn-delete btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questa commessa?');"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $row['idcommessa']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Modifica Commessa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="commesse.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $row['idcommessa']; ?>">
                                        <div class="mb-3">
                                            <label for="numero" class="form-label">Numero Commessa</label>
                                            <input type="text" name="numero" class="form-control" id="numero" value="<?php echo htmlspecialchars($row['numero']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="rifoff" class="form-label">Riferimento Offerta</label>
                                            <input type="text" name="rifoff" class="form-control" id="rifoff" value="<?php echo htmlspecialchars($row['rifoff']); ?>">
                                        </div>
										<div class="mb-3">
											<label for="stato" class="form-label">Stato</label>
											<select name="stato" class="form-select" id="stato" required>
												<option value="">Seleziona uno stato</option> <!-- Aggiungi l'opzione di default -->
												<option value="Aperta" <?php if ($row['stato'] == 'Aperta' OR $row['stato'] == 'aperta') echo 'selected'; ?>>Aperta</option>
												<option value="Chiusa" <?php if ($row['stato'] == 'Chiusa') echo 'selected'; ?>>Chiusa</option>
												<option value="Archiviata" <?php if ($row['stato'] == 'Archiviata') echo 'selected'; ?>>Archiviata</option>
											</select>
										</div>
                                        <div class="mb-3">
                                            <label for="dataapertura" class="form-label">Data Apertura</label>
                                            <input type="date" name="dataapertura" class="form-control" id="dataapertura" value="<?php echo htmlspecialchars($row['dataapertura']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="idcliente" class="form-label">Cliente</label>
                                            <select name="idcliente" class="form-select">
											<option value="">Seleziona un cliente</option> <!-- Aggiungi l'opzione di default -->
                                                <?php 
                                                $clienteQuery = "SELECT * FROM clienti";
                                                $clienteResult = mysqli_query($mysqli, $clienteQuery);
                                                while ($clienteRow = mysqli_fetch_assoc($clienteResult)) { ?>
                                                    <option value="<?php echo $clienteRow['idcliente']; ?>" <?php if($row['idcliente'] == $clienteRow['idcliente']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($clienteRow['cliente']); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="deslavoro" class="form-label">Descrizione Lavoro</label>
                                            <textarea name="deslavoro" class="form-control" id="deslavoro"><?php echo htmlspecialchars($row['deslavoro']); ?></textarea>
                                        </div>
										<div class="mb-3">
											<label for="costooffertauscita" class="form-label">Offerta in Uscita</label>
											<input type="number" step="0.01" name="costooffertauscita" class="form-control" id="costooffertauscita" value="<?php echo htmlspecialchars($row['costooffertauscita']); ?>">
										</div>
										<div class="mb-3">
											<label for="costo_tot_comm_prev" class="form-label">Costo Totale Previsto</label>
											<input type="number" step="0.01" name="costo_tot_comm_prev" class="form-control" id="costo_tot_comm_prev" value="<?php echo htmlspecialchars($row['costo_tot_comm_prev']); ?>">
										</div>
										<div class="mb-3">
											<label for="costo_tot_forn_prev" class="form-label">Costo Fornitori Previsto</label>
											<input type="number" step="0.01" name="costo_tot_forn_prev" class="form-control" id="costo_tot_forn_prev" value="<?php echo htmlspecialchars($row['costo_tot_forn_prev']); ?>">
										</div>
										<div class="mb-3">
											<label for="costo_tot_pers_prev" class="form-label">Costo Personale Previsto</label>
											<input type="number" step="0.01" name="costo_tot_pers_prev" class="form-control" id="costo_tot_pers_prev" value="<?php echo htmlspecialchars($row['costo_tot_pers_prev']); ?>">
										</div>
                                        <div class="mb-3">
                                            <label for="datachiusura" class="form-label">Data Chiusura</label>
                                            <input type="date" name="datachiusura" class="form-control" id="datachiusura" value="<?php echo htmlspecialchars($row['datachiusura']); ?>">
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

    <!-- Modal for Adding New Commessa -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Aggiungi nuova Commessa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="commesse.php" method="POST">
                        <div class="mb-3">
                            <label for="numero" class="form-label">Numero Commessa</label>
                            <input type="text" name="numero" class="form-control" id="numero" required>
                        </div>
                        <div class="mb-3">
                            <label for="rifoff" class="form-label">Riferimento Offerta</label>
                            <input type="text" name="rifoff" class="form-control" id="rifoff">
                        </div>
						<div class="mb-3">
							<label for="stato" class="form-label">Stato</label>
							<select name="stato" class="form-select" id="stato" required>
								<option value="">Seleziona uno stato</option>
								<option value="Aperta">Aperta</option>
								<option value="Chiusa">Chiusa</option>
								<option value="Archiviata">Archiviata</option>
							</select>
						</div>
                        <div class="mb-3">
                            <label for="dataapertura" class="form-label">Data Apertura</label>
                            <input type="date" name="dataapertura" class="form-control" id="dataapertura" value="<?php echo isset($row['dataapertura']) ? htmlspecialchars($row['dataapertura']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="idcliente" class="form-label">Cliente</label>
                            <select name="idcliente" class="form-select">
							<option value="">Seleziona un cliente</option>
                                <?php 
                                $clienteQuery = "SELECT * FROM clienti";
                                $clienteResult = mysqli_query($mysqli, $clienteQuery);
                                while ($clienteRow = mysqli_fetch_assoc($clienteResult)) { ?>
                                    <option value="<?php echo $clienteRow['idcliente']; ?>">
                                        <?php echo htmlspecialchars($clienteRow['cliente']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="deslavoro" class="form-label">Descrizione Lavoro</label>
                            <textarea name="deslavoro" class="form-control" id="deslavoro"></textarea>
                        </div>
						<div class="mb-3">
							<label for="costooffertauscita" class="form-label">Offerta in Uscita</label>
							<input type="number" step="0.01" name="costooffertauscita" class="form-control" id="costooffertauscita">
						</div>
						<div class="mb-3">
							<label for="costo_tot_comm_prev" class="form-label">Costo Totale Previsto</label>
							<input type="number" step="0.01" name="costo_tot_comm_prev" class="form-control" id="costo_tot_comm_prev">
						</div>
						<div class="mb-3">
							<label for="costo_tot_forn_prev" class="form-label">Costo Fornitori Previsto</label>
							<input type="number" step="0.01" name="costo_tot_forn_prev" class="form-control" id="costo_tot_forn_prev">
						</div>
						<div class="mb-3">
							<label for="costo_tot_pers_prev" class="form-label">Costo Personale Previsto</label>
							<input type="number" step="0.01" name="costo_tot_pers_prev" class="form-control" id="costo_tot_pers_prev">
						</div>
                        <div class="mb-3">
                            <label for="data_chiusura" class="form-label">Data Chiusura</label>
                            <input type="date" name="datachiusura" class="form-control" id="datachiusura">
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Aggiungi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Modal for viewing archived commesse -->
<div class="modal fade" id="archiviateModal" tabindex="-1" aria-labelledby="archiviateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiviateModalLabel">Commesse Archiviate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                // Query per ottenere le commesse archiviate
                $archiviateQuery = "SELECT co.*, c.cliente FROM commesse co LEFT JOIN clienti c ON co.idcliente = c.idcliente WHERE co.stato = 'Archiviata'";
                $archiviateResult = mysqli_query($mysqli, $archiviateQuery);

                if (mysqli_num_rows($archiviateResult) > 0) { ?>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Numero Commessa</th>
                                <th>Cliente</th>
                                <th>Descrizione Lavoro</th>
                                <th>Data Chiusura</th>
								<th>Costo Totale Previsto</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($archiviateRow = mysqli_fetch_assoc($archiviateResult)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($archiviateRow['numero']); ?></td>
                                    <td><?php echo htmlspecialchars($archiviateRow['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($archiviateRow['deslavoro']); ?></td>
                                    <td><?php echo htmlspecialchars($archiviateRow['datachiusura']); ?></td>
									<td>
										<?php 
										echo '€ ' . (floor($archiviateRow['costo_tot_comm_prev']) == $archiviateRow['costo_tot_comm_prev'] 
													? number_format($archiviateRow['costo_tot_comm_prev'], 0, ',', '.') 
													: number_format($archiviateRow['costo_tot_comm_prev'], 2, ',', '.')); 
										?>
									</td>
                                    <td class="action-column">
										<!-- Pulsante per ripristinare lo stato della commessa a "Attiva" -->
										<a href="commesse.php?restore_id=<?php echo $archiviateRow['idcommessa']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Sei sicuro di voler ripristinare questa commessa archiviata?');">
											<i class="fas fa-undo"></i> 
										</a>
										<!-- Pulsante per eliminare la commessa -->
										<a href="commesse.php?delete_id=<?php echo $archiviateRow['idcommessa']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questa commessa archiviata?');">
											<i class="fas fa-trash-alt"></i> 
										</a>
									</td>
                                </tr>

                                <!-- Include il modale di modifica stato per la commessa archiviata -->
                                <?php include 'edit_commessa_modal.php'; ?>
                            <?php } ?>
                        </tbody>
                    </table>
				<!-- Display success messages -->
				<?php if (isset($_GET['msg']) && $_GET['msg'] == 'restore_success'): ?>
					<div class="alert alert-success text-center" role="alert">
						Commessa ripristinata con successo.
					</div>
				<?php endif; ?>

                <?php } else { ?>
                    <p class="text-center">Non ci sono commesse archiviate al momento.</p>
                <?php } ?>
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
let sortDirection = {};

function sortTable(columnIndex, thElement) {
    let table = thElement.closest('table');
    let rows = Array.from(table.querySelectorAll('tbody tr'));
    let isAscending = sortDirection[columnIndex] !== 'asc'; // Se già ascendente, sarà discendente, altrimenti ascendente

    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].innerText.trim();
        let cellB = rowB.cells[columnIndex].innerText.trim();

        // Gestione delle colonne in valuta (Offerta in Uscita, Costo Totale Previsto, ecc.)
        if (columnIndex === 6 || columnIndex >= 7 && columnIndex <= 9) {
            let numA = parseFloat(cellA.replace(/[^\d,-]+/g, '').replace(/\./g, '').replace(',', '.')); // Rimuovi simboli non numerici e sostituisci ',' con '.'
            let numB = parseFloat(cellB.replace(/[^\d,-]+/g, '').replace(/\./g, '').replace(',', '.'));
            return isAscending ? numA - numB : numB - numA;
        }
        // Gestione delle colonne di data (Data Apertura, Data Chiusura, ecc.)
        else if (columnIndex === 3 || columnIndex === 10) {
            let dateA = new Date(cellA.split('-').reverse().join('-')); // Converti "dd-mm-yyyy" in "yyyy-mm-dd"
            let dateB = new Date(cellB.split('-').reverse().join('-'));
            return isAscending ? dateA - dateB : dateB - dateA;
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
        const searchForm = document.querySelector('form[action="commesse.php"]');
        searchForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, searchForm);
        });

        // Aggiungi event listener per il form dei filtri
        const filterForm = document.getElementById('filterForm'); // Usa l'id per selezionare il form dei filtri
        filterForm.addEventListener('submit', function (event) {
            handleFormSubmit(event, filterForm);
        });

        // Funzione per gestire la selezione di "Seleziona tutto" per Numero e Cliente
        const numeroSelect = document.getElementById('numeroSelect');
        const numeroSelectAllOption = numeroSelect.querySelector('[data-select-all]');
        numeroSelect.addEventListener('change', function () {
            if (numeroSelectAllOption.selected) {
                for (let i = 0; i < numeroSelect.options.length; i++) {
                    numeroSelect.options[i].selected = true;
                }
                numeroSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
            }
        });
        const statoSelect = document.getElementById('statoSelect');
        const statoSelectAllOption = statoSelect.querySelector('[data-select-all]');
        statoSelect.addEventListener('change', function () {
            if (statoSelectAllOption.selected) {
                for (let i = 0; i < statoSelect.options.length; i++) {
                    statoSelect.options[i].selected = true;
                }
                statoSelectAllOption.selected = false; // Deseleziona "Seleziona tutto" per il prossimo click
            }
        });

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
			var filename = "commesse_filtrate_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".xlsx";
		
			// Esporta in formato Excel
			XLSX.writeFile(workbook, filename);
		}



    function exportToPDF(queryString) {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF();

        // Seleziona solo la tabella visibile (dopo i filtri o la ricerca)
        var table = document.querySelector("table");

        // Usa jsPDF AutoTable per generare la tabella nel PDF con tutte le colonne
        doc.autoTable({
            head: [
                ['Numero Commessa', 'Riferimento Offerta', 'Stato', 'Data Apertura', 'Cliente', 'Descrizione Lavoro', 'Offerta in Uscita', 'Costo Totale Previsto', 'Costo Fornitori Previsto', 'Costo Personale Previsto', 'Data Chiusura']
            ],
            body: Array.from(table.querySelectorAll('tbody tr')).map(row => {
                return [
                    row.cells[0].innerText, // Numero Commessa
                    row.cells[1].innerText, // Riferimento Offerta
                    row.cells[2].innerText, // Stato
                    row.cells[3].innerText, // Data Apertura
                    row.cells[4].innerText, // Cliente
                    row.cells[5].innerText, // Descrizione Lavoro
                    row.cells[6].innerText, // Offerta in Uscita
                    row.cells[7].innerText, // Costo Totale Previsto
                    row.cells[8].innerText, // Costo Fornitori Previsto
                    row.cells[9].innerText, // Costo Personale Previsto
                    row.cells[10].innerText // Data Chiusura
                ];
            }),
            theme: 'striped',
            headStyles: { fillColor: [22, 160, 133] },
        });

        // Aggiungi i filtri alla query string per il nome del file
        var filename = "commesse_filtrate_" + queryString.replace(/[^a-zA-Z0-9]/g, '_') + ".pdf";

        // Salva il PDF con il nome che include i filtri
        doc.save(filename);
    }

</script>
</body>
</html>
