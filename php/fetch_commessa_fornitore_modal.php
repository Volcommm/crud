<?php
include_once("connection.php");

$id = (int) $_GET['id'];

// Query per ottenere i dati di comm_fornitore
$query = "SELECT * FROM comm_fornitore WHERE idcomm_fornitore = $id";
$result = mysqli_query($mysqli, $query);
$data = mysqli_fetch_assoc($result);

// Genera le opzioni per le commesse e fornitori
$commessaOptions = '';
$commessaResult = mysqli_query($mysqli, "SELECT idcommessa, numero FROM commesse");
while ($commessaRow = mysqli_fetch_assoc($commessaResult)) {
    $selected = ($commessaRow['idcommessa'] == $data['idcommessa']) ? 'selected' : '';
    $commessaOptions .= "<option value='{$commessaRow['idcommessa']}' $selected>{$commessaRow['numero']}</option>";
}

$fornitoreOptions = '';
$fornitoreResult = mysqli_query($mysqli, "SELECT idfornitore, fornitore FROM fornitori");
while ($fornitoreRow = mysqli_fetch_assoc($fornitoreResult)) {
    $selected = ($fornitoreRow['idfornitore'] == $data['idfornitore']) ? 'selected' : '';
    $fornitoreOptions .= "<option value='{$fornitoreRow['idfornitore']}' $selected>{$fornitoreRow['fornitore']}</option>";
}

$idtipologia_rifOptions = "<option value=''>Seleziona un riferimento</option>"; // Aggiungi un'opzione di default
$idtipologia_rifResult = mysqli_query($mysqli, "SELECT idtipologia_rif, descr_rif FROM tipologieriferimenti");
while ($idtipologia_rifRow = mysqli_fetch_assoc($idtipologia_rifResult)) {
    // Se l'id della tipologia riferimento coincide con il valore esistente nei dati ($data['idtipologia_rif'])
    $selected = ($idtipologia_rifRow['idtipologia_rif'] == $data['idtipologia_rif']) ? 'selected' : '';
    $idtipologia_rifOptions .= "<option value='{$idtipologia_rifRow['idtipologia_rif']}' $selected>{$idtipologia_rifRow['descr_rif']}</option>";
}




// Restituisci il markup del modale
echo "
<div class='modal fade' id='editModal' tabindex='-1' aria-labelledby='editModalLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title' id='editModalLabel'>Modifica Commessa-Fornitore</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <form id='editForm' action='lavorazione_commesse_fornitori.php' method='POST' enctype='multipart/form-data'>
                    <input type='hidden' name='id' id='editId' value='{$data['idcomm_fornitore']}'>
                    <div class='mb-3'>
                        <label for='editDatains' class='form-label'>Data Inserimento</label>
                        <input type='date' name='datains' class='form-control' id='editDatains' value='{$data['datains']}' required>
                    </div>
                    <div class='mb-3'>
                        <label for='editCommessa' class='form-label'>Commessa</label>
                        <select name='idcommessa' class='form-select' id='editCommessa' required>
                            $commessaOptions
                        </select>
                    </div>
                    <div class='mb-3'>
                        <label for='editFornitore' class='form-label'>Fornitore</label>
                        <select name='idfornitore' class='form-select' id='editFornitore' required>
                            $fornitoreOptions
                        </select>
                    </div>
                    <div class='mb-3'>
                        <label for='editImporto' class='form-label'>Importo</label>
                        <input type='number' step='0.01' name='importo' class='form-control' id='editImporto' value='{$data['importo']}' required>
                    </div>
		    <div class='mb-3'>
                        <label for='editTipoRif' class='form-label'>TipologiaRiferimento</label>
                        <select name='idtipologia_rif' class='form-select' id='editTipoRif' required>
                            $idtipologia_rifOptions
                        </select>
                    </div>
                    <div class='mb-3'>
                        <label for='editRif' class='form-label'>Riferimento</label>
                        <input type='text' name='rif' class='form-control' id='editRif' value='{$data['rif']}' required>
                    </div>
		    <div class='mb-3'>
			<label for='editFileAllegato' class='form-label'>File Allegato Riferimento</label>
			<input type='file' name='FileAllegatoRiferimento' class='form-control' id='editFileAllegato'>
		    </div>
                    <button type='submit' name='update' class='btn btn-primary'>Modifica</button>
                </form>
            </div>
        </div>
    </div>
</div>
";
