<?php
include_once("connection.php");

$id = (int)$_GET['id'];

// Query per ottenere i dati di comm_personale
$query = "SELECT * FROM comm_personale WHERE idcomm_personale = $id";
$result = mysqli_query($mysqli, $query);
$data = mysqli_fetch_assoc($result);

// Genera le opzioni per le commesse e personale
$commessaOptions = '';
$commessaResult = mysqli_query($mysqli, "SELECT idcommessa, numero FROM commesse");
while ($commessaRow = mysqli_fetch_assoc($commessaResult)) {
    $selected = ($commessaRow['idcommessa'] == $data['idcommessa']) ? 'selected' : '';
    $commessaOptions .= "<option value='{$commessaRow['idcommessa']}' $selected>{$commessaRow['numero']}</option>";
}

$personaleOptions = '';
$personaleResult = mysqli_query($mysqli, "SELECT idpersonale, nome FROM personale");
while ($personaleRow = mysqli_fetch_assoc($personaleResult)) {
    $selected = ($personaleRow['idpersonale'] == $data['idpersonale']) ? 'selected' : '';
    $personaleOptions .= "<option value='{$personaleRow['idpersonale']}' $selected>{$personaleRow['nome']}</option>";
}

// Restituisci il markup del modale
echo "
<div class='modal fade' id='editModal' tabindex='-1' aria-labelledby='editModalLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title' id='editModalLabel'>Modifica Commessa-Personale</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
            </div>
            <div class='modal-body'>
                <form id='editForm' action='lavorazione_commesse_personale.php' method='POST'>
                    <input type='hidden' name='id' id='editId' value='{$data['idcomm_personale']}'>
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
                        <label for='editPersonale' class='form-label'>Personale</label>
                        <select name='idpersonale' class='form-select' id='editPersonale' required>
                            $personaleOptions
                        </select>
                    </div>
                    <div class='mb-3'>
                        <label for='editOre' class='form-label'>Ore Lavorate</label>
                        <input type='number' step='0.01' name='ore' class='form-control' id='editOre' value='{$data['ore']}' required>
                    </div>
                    <button type='submit' name='update' class='btn btn-primary'>Modifica</button>
                </form>
            </div>
        </div>
    </div>
</div>
";
?>
