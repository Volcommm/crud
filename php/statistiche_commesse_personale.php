<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit(); // Make sure to exit after redirection
}

// Including the database connection file
include_once("connection.php");

// Variabili per i filtri
$numeroCommessa = isset($_GET['numeroCommessa']) ? mysqli_real_escape_string($mysqli, $_GET['numeroCommessa']) : '';
$personale = isset($_GET['personale']) ? mysqli_real_escape_string($mysqli, $_GET['personale']) : '';
$dataCommessa = isset($_GET['dataCommessa']) ? mysqli_real_escape_string($mysqli, $_GET['dataCommessa']) : '';
$dataIntervento = isset($_GET['dataIntervento']) ? mysqli_real_escape_string($mysqli, $_GET['dataIntervento']) : '';
$oreEffettive = isset($_GET['oreEffettive']) ? mysqli_real_escape_string($mysqli, $_GET['oreEffettive']) : '';
$pagaOraria = isset($_GET['pagaOraria']) ? mysqli_real_escape_string($mysqli, $_GET['pagaOraria']) : '';
$stipendio = isset($_GET['stipendio']) ? mysqli_real_escape_string($mysqli, $_GET['stipendio']) : '';
$spesaPrevista = isset($_GET['spesaPrevista']) ? mysqli_real_escape_string($mysqli, $_GET['spesaPrevista']) : '';

// Query principale per la visualizzazione delle statistiche delle commesse
$query = "SELECT 
              commesse.numero, 
              DATE_FORMAT(commesse.dataapertura, '%d/%m/%Y') AS data_apertura, 
              DATE_FORMAT(comm_personale.datains, '%d/%m/%Y') AS data_lavoro, 
              personale.nome AS nome_personale, 
              comm_personale.ore AS ore_effettive, 
              tipologiepersonale.paga AS paga_oraria, 
              (tipologiepersonale.paga * comm_personale.ore) AS stipendio, 
              commesse.costo_tot_pers_prev AS spesa_prevista 
          FROM comm_personale 
          LEFT JOIN personale ON personale.idpersonale = comm_personale.idpersonale
          LEFT JOIN tipologiepersonale ON tipologiepersonale.idtipologia = personale.idtipologia
          INNER JOIN commesse ON commesse.idcommessa = comm_personale.idcommessa
          WHERE 1=1"; // Condizione di base

// Aggiungi i filtri alla query se presenti
if (!empty($numeroCommessa)) {
    $query .= " AND commesse.numero = '$numeroCommessa'";
}
if (!empty($personale)) {
    $query .= " AND personale.nome LIKE '%$personale%'";
}
if (!empty($dataCommessa)) {
    $query .= " AND DATE(commesse.dataapertura) = '$dataCommessa'";
}
if (!empty($dataIntervento)) {
    $query .= " AND DATE(comm_personale.datains) = '$dataIntervento'";
}
if (!empty($oreEffettive)) {
    $query .= " AND comm_personale.ore = '$oreEffettive'";
}
if (!empty($pagaOraria)) {
    $query .= " AND tipologiepersonale.paga = '$pagaOraria'";
}
if (!empty($stipendio)) {
    $query .= " AND (tipologiepersonale.paga * comm_personale.ore) = '$stipendio'";
}
if (!empty($spesaPrevista)) {
    $query .= " AND commesse.costo_tot_pers_prev = '$spesaPrevista'";
}

// Esegui la query
$result = mysqli_query($mysqli, $query);

// Controlla eventuali errori SQL
if (!$result) {
    die("Errore nella query: " . mysqli_error($mysqli));
}

// Query per ottenere i numeri delle commesse e il personale per i filtri
$numeriCommessaResult = mysqli_query($mysqli, "SELECT idcommessa, numero FROM commesse WHERE LOWER(stato) IN ('aperta', 'chiusa')  ORDER BY numero DESC");
$personaleResult = mysqli_query($mysqli, "SELECT DISTINCT nome FROM personale");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche Commesse Personale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
	<style>
    .filter-buttons {
        margin-bottom: 20px; /* Spazio tra i pulsanti e la tabella */
    }
	</style>
</head>
<body>

<div class="container">
    <h1 class="text-center display-4 mb-4">Statistiche Commesse Personale</h1>
    
    <!-- Messaggio di benvenuto e logout -->
    <div class="d-flex justify-content-between mb-3">
        <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
        <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
		
    </div>
	    <div class="mb-3 text-center">
        <a href="javascript:void(0)" class="btn btn-success" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Esporta in Excel</a>
        <a href="javascript:void(0)" class="btn btn-danger" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Esporta in PDF</a>
    </div>
    <!-- Modulo di ricerca -->
    <form id="filterForm" action="statistiche_commesse_personale.php" method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <label for="numeroCommessa" class="form-label">Numero Commessa</label>
                <select name="numeroCommessa" class="form-select">
                    <option value="">-- Qualsiasi --</option>
                    <?php while ($row = mysqli_fetch_assoc($numeriCommessaResult)) { ?>
                        <option value="<?php echo htmlspecialchars($row['numero']); ?>" <?php if($row['numero'] == $numeroCommessa) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['numero']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="personale" class="form-label">Personale</label>
                <select name="personale" class="form-select">
                    <option value="">-- Qualsiasi --</option>
                    <?php while ($row = mysqli_fetch_assoc($personaleResult)) { ?>
                        <option value="<?php echo htmlspecialchars($row['nome']); ?>" <?php if($row['nome'] == $personale) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['nome']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="dataCommessa" class="form-label">Data Commessa</label>
                <input type="date" name="dataCommessa" class="form-control" id="dataCommessa" value="<?php echo $dataCommessa; ?>">
            </div>
            <div class="col-md-3">
                <label for="dataIntervento" class="form-label">Data Intervento</label>
                <input type="date" name="dataIntervento" class="form-control" id="dataIntervento" value="<?php echo $dataIntervento; ?>">
            </div>
			<div class="col-md-3">
				<label for="oreEffettive" class="form-label">Ore Effettive</label>
				<input type="number" step="0.01" name="oreEffettive" class="form-control" id="oreEffettive" value="<?php echo isset($_GET['oreEffettive']) ? htmlspecialchars($_GET['oreEffettive']) : ''; ?>">
			</div>
			<div class="col-md-3">
				<label for="pagaOraria" class="form-label">Paga Oraria (€)</label>
				<input type="number" step="0.01" name="pagaOraria" class="form-control" id="pagaOraria" value="<?php echo isset($_GET['pagaOraria']) ? htmlspecialchars($_GET['pagaOraria']) : ''; ?>">
			</div>
			<div class="col-md-3">
				<label for="stipendio" class="form-label">Stipendio (€)</label>
				<input type="number" step="0.01" name="stipendio" class="form-control" id="stipendio" value="<?php echo isset($_GET['stipendio']) ? htmlspecialchars($_GET['stipendio']) : ''; ?>">
			</div>
			<div class="col-md-3">
				<label for="spesaPrevista" class="form-label">Spesa Prevista (€)</label>
				<input type="number" step="0.01" name="spesaPrevista" class="form-control" id="spesaPrevista" value="<?php echo isset($_GET['spesaPrevista']) ? htmlspecialchars($_GET['spesaPrevista']) : ''; ?>">
			</div>
			<div class="text-center mt-3 filter-buttons">
				<button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cerca</button>
				<button type="button" class="btn btn-secondary" id="resetFilters"><i class="fas fa-redo"></i> Resetta Filtri</button>
			</div>

    </form>

    <!-- Tabella delle statistiche -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th onclick="sortTable(0, this)">Numero Commessa <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(1, this)">Data Apertura <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(2, this)">Data Lavoro <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(3, this)">Personale <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(4, this)">Ore Effettive <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(5, this)">Paga Oraria <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(6, this)">Stipendio <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                    <th onclick="sortTable(7, this)">Spesa Prevista <span class="sort-icon"><i class="fas fa-sort"></i></span></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['numero']); ?></td>
                        <td><?php echo htmlspecialchars($row['data_apertura']); ?></td>
                        <td><?php echo htmlspecialchars($row['data_lavoro']); ?></td>
                        <td><?php echo htmlspecialchars($row['nome_personale']); ?></td>
                        <td><?php echo htmlspecialchars($row['ore_effettive']); ?></td>
						<td>€ <?php echo str_replace('.', ',', sprintf('%.2f', $row['paga_oraria'])); ?></td>
						<td>€ <?php echo str_replace('.', ',', sprintf('%.2f', $row['stipendio'])); ?></td>
						<td>€ <?php echo str_replace('.', ',', sprintf('%.2f', $row['spesa_prevista'])); ?></td>

                    </tr>
                <?php } ?>
				<tr id="totalsRow">
					<td class="text-end"><strong>Totale:</strong></td> <!-- Prima colonna con testo "Totale" -->
					<td></td> <!-- Seconda colonna vuota -->
					<td></td> <!-- Terza colonna vuota -->
					<td></td> <!-- Quarta colonna vuota -->
					<td id="totalOreEffettive"></td>
					<td id="totalPagaOraria"></td>
					<td id="totalStipendio"></td>
					<td id="totalSpesaPrevista"></td>
				</tr>
            </tbody>
        </table>
    </div>
    

</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
let sortDirection = {};

function sortTable(columnIndex, thElement) {
    let table = thElement.closest('table');
    let rows = Array.from(table.querySelectorAll('tbody tr'));

    // Escludi la riga dei totali dal sorting
    let filteredRows = rows.filter(row => !row.id.includes('totalsRow'));

    let isAscending = sortDirection[columnIndex] !== 'asc'; // Se già ascendente, sarà discendente, altrimenti ascendente

    filteredRows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].innerText.trim();
        let cellB = rowB.cells[columnIndex].innerText.trim();

        // Gestione delle colonne delle date
        if (columnIndex === 1 || columnIndex === 2) { // Supponiamo che le colonne 1 e 2 siano date
            let dateA = parseDate(cellA);
            let dateB = parseDate(cellB);
            return isAscending ? dateA - dateB : dateB - dateA;
        }

        // Gestione delle colonne in valuta (Paga Oraria, Stipendio, Spesa Prevista)
        if (columnIndex >= 5) {
            let numA = parseFloat(cellA.replace(/[^0-9,-]+/g, "").replace(',', '.')); // Rimuovi simboli non numerici e sostituisci virgola con punto
            let numB = parseFloat(cellB.replace(/[^0-9,-]+/g, "").replace(',', '.'));
            return isAscending ? numA - numB : numB - numA;
        } else {
            // Gestione per testo (Numero Commessa, Personale, ecc.)
            return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        }
    });

    // Aggiorna la tabella, inserendo prima le righe ordinate e poi la riga totale
    let tbody = table.querySelector('tbody');
    filteredRows.forEach(row => tbody.appendChild(row));

    // Mantieni la riga dei totali in fondo alla tabella
    let totalsRow = document.getElementById('totalsRow');
    if (totalsRow) {
        tbody.appendChild(totalsRow); // Rimetti la riga totale alla fine
    }

    // Resetta tutte le icone di ordinamento a uno stato neutro (icona di ordinamento generica)
    table.querySelectorAll('th .sort-icon').forEach(icon => {
        icon.innerHTML = '<i class="fas fa-sort"></i>'; // Resetta tutte le icone
    });

    // Aggiorna l'icona per la colonna ordinata
    thElement.querySelector('.sort-icon').innerHTML = isAscending ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';

    // Memorizza la direzione attuale per questa colonna
    sortDirection[columnIndex] = isAscending ? 'asc' : 'desc';
}

// Funzione per trasformare le stringhe in oggetti Date
function parseDate(dateString) {
    if (!dateString) return new Date(0); // Se la stringa è vuota o null, restituisci una data minima
    // Supponiamo che le date siano nel formato "DD/MM/YYYY"
    let parts = dateString.split('/');
    if (parts.length !== 3) return new Date(0); // Restituisci una data minima se non è nel formato giusto
    let day = parseInt(parts[0], 10);
    let month = parseInt(parts[1], 10) - 1; // Mese in JavaScript va da 0 a 11
    let year = parseInt(parts[2], 10);
    return new Date(year, month, day);
}

document.getElementById('resetFilters').addEventListener('click', function() {
    // Resetta i campi del modulo
    document.getElementById('filterForm').reset();
    
    // Invia il modulo con i valori di filtro vuoti
    window.location.href = 'statistiche_commesse_personale.php'; // Ricarica la pagina per i filtri vuoti
});
document.addEventListener('DOMContentLoaded', function () {
	calculateTotals();
    // Funzione per gestire l'invio del form via AJAX
    function handleFormSubmit(event) {
        event.preventDefault(); // Prevenire il comportamento predefinito di submit
        
        const form = event.target;
        const formData = new FormData(form); // Raccogli i dati del form

        // Crea una query string per il metodo GET
        const queryParams = new URLSearchParams();
        formData.forEach((value, key) => {
            queryParams.append(key, value);
        });

        // Invia i dati via fetch usando la query string
        fetch(`${form.action}?${queryParams.toString()}`, {
            method: 'GET'
        })
        .then(response => response.text())
        .then(data => {
            // Parsea il nuovo documento HTML
            let parser = new DOMParser();
            let doc = parser.parseFromString(data, 'text/html');

            // Sostituisci solo il contenuto del tbody
            let newTbody = doc.querySelector('tbody');
            document.querySelector('tbody').innerHTML = newTbody.innerHTML;

            // Ricalcola i totali DOPO aver inserito il nuovo tbody
            calculateTotals();
        })
        .catch(error => console.error('Errore durante l\'invio del form:', error));
    }

    // Aggiungi l'event listener per il submit del form
    const form = document.getElementById('filterForm');
    form.addEventListener('submit', handleFormSubmit);
});



function calculateTotals() {
    let totalOreEffettive = 0;
    let totalPagaOraria = 0;
    let totalStipendio = 0;
    let totalSpesaPrevista = 0;

    // Itera sulle righe del corpo della tabella, escludendo la riga dei totali
    document.querySelectorAll('tbody tr').forEach(function(row) {
        if (!row.id.includes('totalsRow')) { // Escludi la riga dei totali
            let oreEffettive = parseInt(row.cells[4].innerText.replace(/[^\d]/g, '')) || 0;
            let pagaOraria = parseFloat(row.cells[5].innerText.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
            let stipendio = parseFloat(row.cells[6].innerText.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
            let spesaPrevista = parseFloat(row.cells[7].innerText.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;

            totalOreEffettive += oreEffettive;
            totalPagaOraria += pagaOraria;
            totalStipendio += stipendio;
            totalSpesaPrevista += spesaPrevista;
        }
    });

    // Aggiorna le celle della riga dei totali
    document.getElementById('totalOreEffettive').innerText = totalOreEffettive;
    document.getElementById('totalPagaOraria').innerText = '€ ' + totalPagaOraria.toFixed(2).replace('.', ',');
    document.getElementById('totalStipendio').innerText = '€ ' + totalStipendio.toFixed(2).replace('.', ',');
    document.getElementById('totalSpesaPrevista').innerText = '€ ' + totalSpesaPrevista.toFixed(2).replace('.', ',');
}





function exportToExcel() {
    var table = document.querySelector("table");
    var rows = [];
    table.querySelectorAll("tr").forEach(function(row) {
        var rowData = [];
        row.querySelectorAll("td, th").forEach(function(cell) {
            rowData.push(cell.innerText);
        });
        rows.push(rowData);
    });

    var workbook = XLSX.utils.book_new();
    var worksheet = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet 1");
    XLSX.writeFile(workbook, "statistiche_commesse.xlsx");
}

function exportToPDF() {
    var { jsPDF } = window.jspdf;
    var doc = new jsPDF();

    // Ottieni la tabella
    var table = document.querySelector("table");

    // Configura l'intestazione e i dati del corpo della tabella
    var headers = [['Numero Commessa', 'Data Apertura', 'Data Lavoro', 'Personale', 'Ore Effettive', 'Paga Oraria', 'Stipendio', 'Spesa Prevista']];
    var data = Array.from(table.querySelectorAll('tbody tr')).map(row => {
        return Array.from(row.cells).map(cell => cell.innerText);
    });

    // Usa autotable per generare la tabella nel PDF
    doc.autoTable({
        head: headers,
        body: data,
        theme: 'striped',
        margin: { top: 20 }, // Margine superiore
    });

    // Salva il file PDF
    doc.save('statistiche_commesse.pdf');
}

</script>
</body>
</html>
