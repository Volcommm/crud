<!-- Modal for confirming restore or delete -->
<div class="modal fade" id="editModal<?php echo $archiviateRow['idcommessa']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $archiviateRow['idcommessa']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $archiviateRow['idcommessa']; ?>">Azioni per Commessa Archiviata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler ripristinare o eliminare questa commessa archiviata?</p>
                <p><strong>Numero Commessa:</strong> <?php echo htmlspecialchars($archiviateRow['numero']); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($archiviateRow['cliente']); ?></p>
                <p><strong>Descrizione Lavoro:</strong> <?php echo htmlspecialchars($archiviateRow['deslavoro']); ?></p>
                <p><strong>Data Chiusura:</strong> <?php echo htmlspecialchars($archiviateRow['datachiusura']); ?></p>
            </div>
            <div class="modal-footer">
                <!-- Pulsante per ripristinare la commessa -->
                <a href="commesse.php?restore_id=<?php echo $archiviateRow['idcommessa']; ?>" class="btn btn-success" onclick="return confirm('Sei sicuro di voler ripristinare questa commessa archiviata?');">
                    <i class="fas fa-undo"></i> Ripristina
                </a>
                <!-- Pulsante per eliminare la commessa -->
                <a href="commesse.php?delete_id=<?php echo $archiviateRow['idcommessa']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare definitivamente questa commessa archiviata?');">
                    <i class="fas fa-trash-alt"></i> Elimina
                </a>
            </div>
        </div>
    </div>
</div>
