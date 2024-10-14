<!-- Edit Modal for archived commesse -->
<div class="modal fade" id="editModal<?php echo $archiviateRow['idcommessa']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $archiviateRow['idcommessa']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $archiviateRow['idcommessa']; ?>">Modifica Stato Commessa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="commesse.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $archiviateRow['idcommessa']; ?>">
                    <div class="mb-3">
                        <label for="stato" class="form-label">Stato</label>
                        <select name="stato" class="form-select" id="stato" required>
                            <option value="Aperta" <?php if ($archiviateRow['stato'] == 'Aperta') echo 'selected'; ?>>Aperta</option>
                            <option value="Chiusa" <?php if ($archiviateRow['stato'] == 'Chiusa') echo 'selected'; ?>>Chiusa</option>
                            <option value="Archiviata" <?php if ($archiviateRow['stato'] == 'Archiviata') echo 'selected'; ?>>Archiviata</option>
                        </select>
                    </div>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $archiviateRow['idcommessa']; ?>">
						<i class="fas fa-edit"></i> Modifica Stato
					</button>

                </form>
            </div>
        </div>
    </div>
</div>
