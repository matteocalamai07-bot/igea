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
            <button class="btn-azione" style="background-color: #e74c3c; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: 600;" onclick="confermaEliminazione('elimina_anamnesi.php?id=<?= $a['id'] ?>')">
                Elimina anamnesi
            </button>
        </div>
    </main>

<!-- SCRIPT CONFERMA ELIMINAZIONE -->
<script>
function confermaEliminazione(url){
    if(confirm("Sei sicuro di voler eliminare questa anamnesi?")){
        window.location.href = url;
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>