<?php
session_start();
ob_start(); // Start output buffering

if (!isset($_SESSION['valid'])) {
    header('Location: login.php');
    exit();
}

include_once("connection.php");

// Variabile per selezionare la commessa
$selectcommessa = isset($_POST['commessa']) ? mysqli_real_escape_string($mysqli, $_POST['commessa']) : '';

// Query per ottenere le commesse
$commessaQuery = "SELECT commesse.idcommessa, commesse.numero, clienti.cliente 
                  FROM commesse 
                  LEFT JOIN clienti ON clienti.idcliente = commesse.idcliente 
				  WHERE LOWER(commesse.stato) IN ('aperta', 'chiusa')
                  ORDER BY commesse.numero DESC";
$commessaResult = mysqli_query($mysqli, $commessaQuery);

// Variabile per il numero della commessa selezionata
$numeroCommessa = '';

// Seleziona il numero della commessa selezionata
if (!empty($selectcommessa)) {
    $numeroCommessaQuery = "SELECT numero FROM commesse WHERE idcommessa = '$selectcommessa'";
    $numeroCommessaResult = mysqli_query($mysqli, $numeroCommessaQuery);
    if ($numeroCommessaRow = mysqli_fetch_assoc($numeroCommessaResult)) {
        $numeroCommessa = $numeroCommessaRow['numero'];
    }
}

$clienteCommessa = '';
if (!empty($selectcommessa)) {
    $clienteCommessaQuery = "SELECT clienti.cliente FROM commesse 
                             LEFT JOIN clienti ON clienti.idcliente = commesse.idcliente 
                             WHERE commesse.idcommessa = '$selectcommessa'";
    $clienteCommessaResult = mysqli_query($mysqli, $clienteCommessaQuery);
    if ($clienteCommessaRow = mysqli_fetch_assoc($clienteCommessaResult)) {
        $clienteCommessa = $clienteCommessaRow['cliente'];
    }
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche Commesse Fornitori</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
	<style>
	form .form-select {
    padding: 0.25rem 0.5rem; /* Riduce l'altezza del selettore */
    text-align: center; /* Centra il testo nel selettore */
}

.form-container {
    background-color: #f1f1f1; /* Grigio chiaro per lo sfondo del container */
    padding: 20px;
    border-radius: 8px; /* Angoli arrotondati per un look moderno */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Leggera ombra per dare profondità */
}

	</style>
</head>
<body>
<body>

<div class="container">
    <h1 class="text-center display-4 mb-4">Statistiche Totali</h1>
	    <!-- Messaggio di benvenuto e logout -->
    <div class="d-flex justify-content-between mb-3">
        <a href="index.php" class="btn btn-outline-light"><i class="fas fa-home"></i> Home</a>
        <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="mb-3 text-center">
        <a href="javascript:void(0)" class="btn btn-success" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Esporta in Excel</a>
        <a href="javascript:void(0)" class="btn btn-danger" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Esporta in PDF</a>
    </div>

<div class="container text-center form-container">
    <!-- Form selezione commessa -->
    <form method="POST" class="mb-4 d-inline-block">
        <div class="mb-3">
            <select name="commessa" id="commessa" class="form-select form-select-sm w-auto text-center" style="width: 250px;" onChange="this.form.submit()">
                <option value="">-- Seleziona --</option>
                <?php while ($row = mysqli_fetch_assoc($commessaResult)) { ?>
                    <option value="<?php echo htmlspecialchars($row['idcommessa']); ?>" 
                            <?php echo $row['idcommessa'] == $selectcommessa ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['numero'] . ' - ' . $row['cliente']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <!-- Pulsante di reset lasciato con lo stile originale -->
        <div class="text-center">
            <a href="statistiche_commesse_totali.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo"></i> Resetta Filtro</a>
        </div>
    </form>
</div>






    <?php
if (!empty($selectcommessa)) {
    // Recupero dei preventivi dal database
    $spesa_prev_forn_query = "SELECT costo_tot_forn_prev FROM commesse WHERE idcommessa = '$selectcommessa'";
    $spesa_prev_forn_result = mysqli_query($mysqli, $spesa_prev_forn_query);
    $spesa_prev_forn_row = mysqli_fetch_assoc($spesa_prev_forn_result);
    $spesa_prev_forn = $spesa_prev_forn_row['costo_tot_forn_prev']; // Preventivo fornitori

    $spesa_prev_pers_query = "SELECT costo_tot_pers_prev FROM commesse WHERE idcommessa = '$selectcommessa'";
    $spesa_prev_pers_result = mysqli_query($mysqli, $spesa_prev_pers_query);
    $spesa_prev_pers_row = mysqli_fetch_assoc($spesa_prev_pers_result);
    $spesa_prev_pers = $spesa_prev_pers_row['costo_tot_pers_prev']; // Preventivo personale

    // Calcolo dei totali previsti
    $spesa_tot_prev = $spesa_prev_forn + $spesa_prev_pers;

    // Totali singoli per Fornitori
    $fornitoriQuery = "SELECT fornitori.fornitore, commesse.numero, DATE_FORMAT(dataapertura, '%d/%m/%Y') AS dataap, SUM(comm_fornitore.importo) AS spesa
                       FROM comm_fornitore
                       LEFT JOIN fornitori ON fornitori.idfornitore = comm_fornitore.idfornitore
                       INNER JOIN commesse ON commesse.idcommessa = comm_fornitore.idcommessa
                       WHERE commesse.idcommessa = '$selectcommessa'
                       GROUP BY fornitori.idfornitore";
    $fornitoriResult = mysqli_query($mysqli, $fornitoriQuery);

    echo "<h2 class='text-center my-4'>Totali singoli per Fornitori</h2>";
    echo "<table class='table table-hover'>
            <thead>
                <tr>
                    <th>Fornitore</th>
                    <th>Commessa</th>
                    <th>Data Apertura</th>
                    <th>Spesa Totale</th>
                </tr>
            </thead>
            <tbody>";
    $tot_forn_spesa = 0;
    while ($row = mysqli_fetch_assoc($fornitoriResult)) {
        $tot_forn_spesa += $row['spesa'];
        echo "<tr>
                <td>" . htmlspecialchars($row['fornitore']) . "</td>
                <td>" . htmlspecialchars($row['numero']) . "</td>
                <td>" . htmlspecialchars($row['dataap']) . "</td>
                <td>€ " . number_format($row['spesa'], 2, ',', '.') . "</td>
              </tr>";
    }
    echo "</tbody></table>";

    // Totali singoli per Personale
    $personaleQuery = "SELECT personale.nome, commesse.numero, DATE_FORMAT(dataapertura, '%d/%m/%Y') AS dataap, SUM(comm_personale.ore) AS oreeffettive, tipologiepersonale.paga AS pagaoraria, SUM(tipologiepersonale.paga * comm_personale.ore) AS stipendio
                       FROM comm_personale
                       LEFT JOIN personale ON personale.idpersonale = comm_personale.idpersonale
                       LEFT JOIN tipologiepersonale ON tipologiepersonale.idtipologia = personale.idtipologia
                       INNER JOIN commesse ON commesse.idcommessa = comm_personale.idcommessa
                       WHERE commesse.idcommessa = '$selectcommessa'
                       GROUP BY personale.idpersonale";
    $personaleResult = mysqli_query($mysqli, $personaleQuery);

    echo "<h2 class='text-center my-4'>Totali singoli per Personale</h2>";
    echo "<table class='table table-hover'>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Commessa</th>
                    <th>Data Apertura</th>
                    <th>Ore Effettuate</th>
                    <th>Paga Oraria</th>
                    <th>Paga Totale</th>
                </tr>
            </thead>
            <tbody>";
    $tot_pers_spesa = 0;
    while ($row = mysqli_fetch_assoc($personaleResult)) {
        $tot_pers_spesa += $row['stipendio'];
        echo "<tr>
                <td>" . htmlspecialchars($row['nome']) . "</td>
                <td>" . htmlspecialchars($row['numero']) . "</td>
                <td>" . htmlspecialchars($row['dataap']) . "</td>
                <td>" . htmlspecialchars($row['oreeffettive']) . "</td>
                <td>€ " . number_format($row['pagaoraria'], 2, ',', '.') . "</td>
                <td>€ " . number_format($row['stipendio'], 2, ',', '.') . "</td>
              </tr>";
    }
    echo "</tbody></table>";

    // Sezione "Totali per Fornitori"
    echo "<h2 class='text-center my-4'>Totali per Fornitori</h2>";
    echo "<table class='table table-hover'>
            <thead>
                <tr>
                    <th>Commessa</th>
                    <th>Fornitori Previsti</th>
                    <th>Fornitori Spesi</th>
                    <th>Guadagno su Commessa Fornitori</th>
                    <th>Stato Fornitori Percentuale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>" . htmlspecialchars($numeroCommessa) . "</td>
                    <td>€ " . number_format($spesa_prev_forn, 2, ',', '.') . "</td>
                    <td>€ " . number_format($tot_forn_spesa, 2, ',', '.') . "</td>
                    <td>€ " . number_format($spesa_prev_forn - $tot_forn_spesa, 2, ',', '.') . "</td>
                    <td>";
    if ($spesa_prev_forn > 0) {
        echo number_format((($tot_forn_spesa / $spesa_prev_forn) * 100), 2, ',', '.') . "%";
    } else {
        echo "0%";
    }
    echo "</td></tr></tbody></table>";

    // Sezione "Totali per Personale"
    echo "<h2 class='text-center my-4'>Totali per Personale</h2>";
    echo "<table class='table table-hover'>
            <thead>
                <tr>
                    <th>Commessa</th>
                    <th>Personale Previsti</th>
                    <th>Personale Spesi</th>
                    <th>Guadagno su Commessa Personale</th>
                    <th>Stato Personale Percentuale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>" . htmlspecialchars($numeroCommessa) . "</td>
                    <td>€ " . number_format($spesa_prev_pers, 2, ',', '.') . "</td>
                    <td>€ " . number_format($tot_pers_spesa, 2, ',', '.') . "</td>
                    <td>€ " . number_format($spesa_prev_pers - $tot_pers_spesa, 2, ',', '.') . "</td>
                    <td>";
    if ($spesa_prev_pers > 0) {
        echo number_format((($tot_pers_spesa / $spesa_prev_pers) * 100), 2, ',', '.') . "%";
    } else {
        echo "0%";
    }
    echo "</td></tr></tbody></table>";

    // Calcoli per il totale complessivo e guadagno
    $spesa_totale = $tot_forn_spesa + $tot_pers_spesa;
    $guadagno_totale = $spesa_tot_prev - $spesa_totale;

    // Verifica se il totale preventivo è maggiore di zero per evitare divisione per zero
    if ($spesa_tot_prev > 0) {
        $percentuale_guadagno = ($guadagno_totale / $spesa_tot_prev) * 100;
    } else {
        $percentuale_guadagno = 0; // Se il preventivo è zero, impostiamo la percentuale a 0
    }

    echo "<h2 class='text-center my-4'>Ricapitolo dati con utile su previsione</h2>";
    echo "<table class='table table-hover'>
            <thead>
                <tr>
                    <th>Commessa</th>
                    <th>Costo Totale Previsto</th>
                    <th>Costo Totale Speso</th>
                    <th>Guadagno su Commessa Totale</th>
                    <th>Guadagno Percentuale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>" . htmlspecialchars($numeroCommessa) . "</td>
                    <td>€ " . number_format($spesa_tot_prev, 2, ',', '.') . "</td>
                    <td>€ " . number_format($spesa_totale, 2, ',', '.') . "</td>
                    <td>€ " . number_format($guadagno_totale, 2, ',', '.') . "</td>
                    <td>" . number_format($percentuale_guadagno, 2, ',', '.') . "%</td>
                </tr>
            </tbody>
          </table>";

    // Totali per Commessa su offerta in uscita
    $offerta_uscita_query = "SELECT costooffertauscita FROM commesse WHERE idcommessa = '$selectcommessa'";
    $offerta_uscita_result = mysqli_query($mysqli, $offerta_uscita_query);
    $offerta_uscita_row = mysqli_fetch_assoc($offerta_uscita_result);
    $offerta_uscita = $offerta_uscita_row['costooffertauscita'];

    $utile_commessa = $offerta_uscita - $spesa_totale;

    // Verifica se l'offerta in uscita è maggiore di zero per evitare divisione per zero
    if ($offerta_uscita > 0) {
        $perc_utile_commessa = ($utile_commessa / $offerta_uscita) * 100;
    } else {
        $perc_utile_commessa = 0; // Se l'offerta è zero, impostiamo la percentuale a 0
    }
    echo "<h2 class='text-center my-4'>Totali per Commessa su offerta in uscita</h2>";
    echo "<table class='table table-hover'>
            <thead>
                <tr>
                    <th>Commessa</th>
                    <th>Offerta in Uscita</th>
                    <th>Spesa Totale</th>
                    <th>Utile Commessa</th>
                    <th>Utile % su Offerta</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>" . htmlspecialchars($numeroCommessa) . "</td>
                    <td>€ " . number_format($offerta_uscita, 2, ',', '.') . "</td>
                    <td>€ " . number_format($spesa_totale, 2, ',', '.') . "</td>
                    <td>€ " . number_format($utile_commessa, 2, ',', '.') . "</td>
                    <td>" . number_format($perc_utile_commessa, 2, ',', '.') . "%</td>
                </tr>
            </tbody>
          </table>";
}
?>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script>
    var numeroCommessa = "<?php echo !empty($numeroCommessa) ? htmlspecialchars($numeroCommessa) : 'commessa'; ?>";
    var clienteCommessa = "<?php echo !empty($clienteCommessa) ? htmlspecialchars($clienteCommessa) : 'cliente'; ?>";
	clienteCommessa = clienteCommessa.replace(/\s+/g, '');
</script>


<script>

function exportToExcel() {
    var workbook = XLSX.utils.book_new();  // Crea un nuovo workbook

    // Array con i nomi dei fogli di lavoro in ordine
    var sheetNames = [
        "Totali singoli per Fornitori",
        "Totali singoli per Personale",
        "Totali per Fornitori",
        "Totali per Personale",
        "Ricapitolo dati con utile su previsione",
        "Totali per Commessa su offerta in uscita"
    ];

    // Seleziona tutte le tabelle presenti nel documento
    var tables = document.querySelectorAll("table");

    // Loop su ciascuna tabella per aggiungerla come sheet nel file Excel
    tables.forEach((table, index) => {
        var rows = [];

        // Usa i nomi definiti per ciascun foglio
        var sheetName = sheetNames[index] || "Foglio " + (index + 1);  
        
        // Troncamento del nome del foglio a 31 caratteri per evitare l'errore
        if (sheetName.length > 31) {
            sheetName = sheetName.substring(0, 31);
        }

        // Ottieni i dati della tabella
        table.querySelectorAll("tr").forEach(function(row) {
            var rowData = [];
            row.querySelectorAll("td, th").forEach(function(cell) {
                rowData.push(cell.innerText);  // Raccoglie i dati della tabella
            });
            rows.push(rowData);
        });

        // Crea un nuovo foglio per ogni tabella
        var worksheet = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);  // Aggiungi il foglio al workbook con il nome specificato
    });

    // Usa il numero della commessa nel nome del file
    var fileName = "statistiche_commessa_" + numeroCommessa + "_" + clienteCommessa + ".xlsx";

    // Salva il file Excel con tutte le tabelle in vari fogli
    XLSX.writeFile(workbook, fileName);
}


function exportToPDF() {
    var { jsPDF } = window.jspdf;
    var doc = new jsPDF();

    // Array con i nomi delle sezioni, simili ai nomi dei fogli Excel
    var sectionNames = [
        "Totali singoli per Fornitori",
        "Totali singoli per Personale",
        "Totali per Fornitori",
        "Totali per Personale",
        "Ricapitolo dati con utile su previsione",
        "Totali per Commessa su offerta in uscita"
    ];

    // Seleziona tutte le tabelle presenti nel documento
    var tables = document.querySelectorAll("table");

    var currentY = 10;  // Y di partenza per ogni sezione
    var pageHeight = doc.internal.pageSize.height;  // Altezza della pagina

    // Loop su ciascuna tabella per aggiungerla al PDF
    tables.forEach((table, index) => {
        var rows = [];
        var headers = [];

        // Usa i nomi definiti per ciascuna sezione
        var sectionName = sectionNames[index] || "Sezione " + (index + 1);

        // Aggiungi il titolo per la sezione
        if (currentY + 20 > pageHeight) {
            doc.addPage();
            currentY = 10;
        }
        doc.text(sectionName, 14, currentY);
        currentY += 10;

        // Ottieni le intestazioni (th) della tabella
        table.querySelectorAll("thead tr th").forEach(function(cell) {
            headers.push(cell.innerText);
        });

        // Ottieni i dati delle righe della tabella
        table.querySelectorAll("tbody tr").forEach(function(row) {
            var rowData = [];
            row.querySelectorAll("td").forEach(function(cell) {
                rowData.push(cell.innerText);
            });
            rows.push(rowData);
        });

        // Verifica se c'è spazio sulla pagina corrente
        if (currentY + rows.length * 10 > pageHeight) {
            doc.addPage();
            currentY = 10;
        }

        // Usa autotable per generare la tabella nel PDF
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: currentY,
            theme: 'striped',
            margin: { top: 20 },
        });

        // Aggiorna la posizione corrente Y dopo la tabella
        currentY = doc.autoTable.previous.finalY + 10;
    });

    // Usa il numero della commessa nel nome del file
    var fileName = "statistiche_commessa_" + numeroCommessa + "_" + clienteCommessa + ".pdf";

    // Salva il file PDF
    doc.save(fileName);
}



</script>

</body>
</html>