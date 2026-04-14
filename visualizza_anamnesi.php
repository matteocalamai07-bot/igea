<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID anamnesi non valido");
}

$id_anamnesi = intval($_GET['id']);

/* ANAMNESI */
$res = $conn->query("SELECT * FROM anamnesi WHERE id = $id_anamnesi");
$a = $res->fetch_assoc();

if (!$a) {
    die("Anamnesi non trovata");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Visualizza Anamnesi</title>
<link rel="stylesheet" href="style.css">

<style>
    body {
        overflow: hidden;
        height: 100vh;
    }

    .main-content {
        display: flex;
        flex-direction: column;
        height: 100vh;
        padding: 15px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 15px;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .page-header h1 {
        margin: 0;
        font-size: 1.3rem;
    }

    .button-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .form-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        flex: 1;
    }

    .form-left, .form-right {
        display: flex;
        flex-direction: column;
        gap: 30px;
        justify-content: space-around;
    }

    .form-blocco {
        flex: 1;
    }

    .form-blocco h3 {
        margin: 0 0 10px 0;
        font-size: 1.05rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-color);
    }

    .info-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-main);
        min-width: 120px;
    }

    .info-value {
        color: var(--text-muted);
        flex: 1;
    }

    .button-container {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    /* MODAL ELIMINAZIONE ELEGANTE */
    .delete-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .delete-modal-overlay.active {
        display: flex;
    }

    .delete-modal-dialog {
        background: var(--bg-card);
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 420px;
        width: 90%;
        padding: 0;
        overflow: hidden;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .delete-modal-header {
        padding: 24px 24px 16px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .delete-modal-header-icon {
        font-size: 24px;
    }

    .delete-modal-header h2 {
        margin: 0;
        color: var(--text-main);
        font-size: 1.2rem;
    }

    .delete-modal-body {
        padding: 20px 24px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .delete-modal-detail {
        background: var(--bg-page);
        padding: 12px;
        border-radius: 6px;
        margin: 12px 0;
        font-size: 0.9rem;
        color: var(--text-main);
        word-break: break-word;
    }

    .delete-modal-detail-label {
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .delete-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn-modal {
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-modal-cancel {
        background: var(--border-color);
        color: var(--text-main);
    }

    .btn-modal-cancel:hover {
        background: var(--border-color);
        opacity: 0.8;
    }

    .btn-modal-delete {
        background: #ef4444 !important;
        color: white !important;
    }

    .btn-modal-delete:hover {
        background: #dc2626 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-modal-delete:active {
        transform: translateY(0);
    }
</style>

</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>

    <main class="main-content">
        <div class="page-header">
            <h1>Anamnesi del <?= htmlspecialchars($a['data']) ?></h1>
            <div class="button-group">
                <a href="scheda_paziente.php?id=<?= $a['fk_paziente'] ?>" class="btn-azione">← Torna alla scheda</a>
            </div>
        </div>

        <div class="form-container">
            <div class="form-left">
                <div class="form-blocco">
                    <h3>Allergie</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Allergie:</span>
                        <span class="info-value"><?= htmlspecialchars($a['allergie']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dettagli:</span>
                        <span class="info-value"><?= htmlspecialchars($a['dettagli_allergie']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Fumo</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Fumo:</span>
                        <span class="info-value"><?= htmlspecialchars($a['fumo']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dettagli:</span>
                        <span class="info-value"><?= htmlspecialchars($a['dettagli_fumo']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Alcol</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Alcol:</span>
                        <span class="info-value"><?= htmlspecialchars($a['alcol']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dettagli:</span>
                        <span class="info-value"><?= htmlspecialchars($a['dettagli_alcol']) ?></span>
                    </div>
                </div>
            </div>

            <div class="form-right">
                <div class="form-blocco">
                    <h3>Patologie</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Patologie:</span>
                        <span class="info-value"><?= htmlspecialchars($a['patologie']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dettagli:</span>
                        <span class="info-value"><?= htmlspecialchars($a['dettagli_patologie']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Interventi</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Interventi:</span>
                        <span class="info-value"><?= htmlspecialchars($a['interventi']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dettagli:</span>
                        <span class="info-value"><?= htmlspecialchars($a['dettagli_interventi']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Esami</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Esami:</span>
                        <span class="info-value"><?= htmlspecialchars($a['esami']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dettagli:</span>
                        <span class="info-value"><?= htmlspecialchars($a['dettagli_esami']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: center; margin-top: 15px;">
            <button class="btn-azione" style="background-color: #ef4444; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: 600;" onclick="confermaEliminazione('Anamnesi', '<?= htmlspecialchars($a['data']) ?>', 'elimina_anamnesi.php?id=<?= $a['id'] ?>')">
                Elimina anamnesi
            </button>
        </div>
    </main>

    <!-- MODAL ELIMINAZIONE ELEGANTE -->
    <div class="delete-modal-overlay" id="deleteModal">
        <div class="delete-modal-dialog">
            <div class="delete-modal-header">
                <div class="delete-modal-header-icon">⚠️</div>
                <h2>Elimina <span id="deleteItemType">Elemento</span></h2>
            </div>
            <div class="delete-modal-body">
                <p>Sei sicuro di voler eliminare questa anamnesi?</p>
                <div class="delete-modal-detail">
                    <div class="delete-modal-detail-label">Data</div>
                    <div id="deleteItemName">-</div>
                </div>
                <p style="color: #dc2626; font-size: 0.85rem; margin-top: 12px;">⚠️ Questa azione non può essere annullata.</p>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="chiudiModal()">Annulla</button>
                <button type="button" class="btn-modal btn-modal-delete" id="confirmDelete">Elimina</button>
            </div>
        </div>
    </div>

<!-- SCRIPT CONFERMA ELIMINAZIONE -->
<script>
let deleteUrl = "";

function confermaEliminazione(tipo, nome, url) {
    document.getElementById("deleteItemType").textContent = tipo;
    document.getElementById("deleteItemName").textContent = nome;
    deleteUrl = url;
    document.getElementById("deleteModal").classList.add("active");
}

function chiudiModal() {
    document.getElementById("deleteModal").classList.remove("active");
    deleteUrl = "";
}

document.getElementById("confirmDelete").addEventListener("click", function() {
    if (deleteUrl) {
        window.location.href = deleteUrl;
    }
});

// Chiudi modal cliccando fuori
document.getElementById("deleteModal").addEventListener("click", function(event) {
    if (event.target === this) {
        chiudiModal();
    }
});

// Chiudi con tasto Escape
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        chiudiModal();
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>