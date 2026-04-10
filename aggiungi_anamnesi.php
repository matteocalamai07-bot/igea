<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$errors = [];
$success_msg = "";

/* =========================
   CONTROLLO ID PAZIENTE
========================= */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("ID paziente non valido.");
}

$id_paziente = intval($_GET["id"]);

/* Verifico che il paziente esista */
$check = $conn->prepare("SELECT id, nome, cognome FROM paziente WHERE id = ?");
$check->bind_param("i", $id_paziente);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Paziente non trovato.");
}
$paziente_info = $result->fetch_assoc();
$nome_paziente = $paziente_info['nome'] . " " . $paziente_info['cognome'];
$check->close();

/* =========================
   GESTIONE ELIMINAZIONE
========================= */
if (isset($_GET['delete_anamnesi']) && is_numeric($_GET['delete_anamnesi'])) {
    $del = intval($_GET['delete_anamnesi']);
    $stmt = $conn->prepare("DELETE FROM anamnesi WHERE id = ? AND fk_paziente = ?");
    $stmt->bind_param("ii", $del, $id_paziente);
    if ($stmt->execute()) {
        header("Location: aggiungi_anamnesi.php?id=$id_paziente");
        exit;
    } else {
        $errors[] = "Errore durante l'eliminazione: " . $stmt->error;
    }
    $stmt->close();
}

/* =========================
   RECUPERA TUTTE LE ANAMNESI
   (ordina per id DESC per mostrare le più recenti)
========================= */
$anamnesi_list = [];
$stmt = $conn->prepare("SELECT * FROM anamnesi WHERE fk_paziente = ? ORDER BY id DESC");
$stmt->bind_param("i", $id_paziente);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $anamnesi_list[] = $row;
}
$stmt->close();

/* =========================
   SELEZIONA UNA SPECIFICA ANAMNESI (per modifica)
   Se ?anamnesi_id=... -> carica quella anamnesi
   Se ?new=1 -> form vuoto per nuova anamnesi
========================= */
$existing_anamnesi = null;
$anamnesi_id = null;
if (isset($_GET['anamnesi_id']) && is_numeric($_GET['anamnesi_id'])) {
    $aid = intval($_GET['anamnesi_id']);
    $stmt = $conn->prepare("SELECT * FROM anamnesi WHERE id = ? AND fk_paziente = ?");
    $stmt->bind_param("ii", $aid, $id_paziente);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $existing_anamnesi = $res->fetch_assoc();
        $anamnesi_id = $existing_anamnesi['id'];
    }
    $stmt->close();
}

/* inizializzo le variabili del form */
$allergie = $dettagli_allergie = $fumo = $dettagli_fumo = $alcol = $dettagli_alcol = $patologie = $dettagli_patologie = $interventi = $dettagli_interventi = $esami = $dettagli_esami = "";
if ($existing_anamnesi) {
    $allergie = $existing_anamnesi['allergie'];
    $dettagli_allergie = $existing_anamnesi['dettagli_allergie'];
    $fumo = $existing_anamnesi['fumo'];
    $dettagli_fumo = $existing_anamnesi['dettagli_fumo'];
    $alcol = $existing_anamnesi['alcol'];
    $dettagli_alcol = $existing_anamnesi['dettagli_alcol'];
    $patologie = $existing_anamnesi['patologie'];
    $dettagli_patologie = $existing_anamnesi['dettagli_patologie'];
    $interventi = $existing_anamnesi['interventi'];
    $dettagli_interventi = $existing_anamnesi['dettagli_interventi'];
    $esami = $existing_anamnesi['esami'];
    $dettagli_esami = $existing_anamnesi['dettagli_esami'];
}

/* =========================
   GESTIONE FORM (POST)
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $allergie = trim($_POST["allergie"] ?? "");
    $dettagli_allergie = trim($_POST["dettagli_allergie"] ?? "");
    $fumo = trim($_POST["fumo"] ?? "");
    $dettagli_fumo = trim($_POST["dettagli_fumo"] ?? "");
    $alcol = trim($_POST["alcol"] ?? "");
    $dettagli_alcol = trim($_POST["dettagli_alcol"] ?? "");
    $patologie = trim($_POST["patologie"] ?? "");
    $dettagli_patologie = trim($_POST["dettagli_patologie"] ?? "");
    $interventi = trim($_POST["interventi"] ?? "");
    $dettagli_interventi = trim($_POST["dettagli_interventi"] ?? "");
    $esami = trim($_POST["esami"] ?? "");
    $dettagli_esami = trim($_POST["dettagli_esami"] ?? "");

    $editing = false;
    if (isset($_POST['anamnesi_id']) && is_numeric($_POST['anamnesi_id'])) {
        $editing = true;
        $anamnesi_id = intval($_POST['anamnesi_id']);
    }

    /* VALIDAZIONI */
    if (empty($allergie)) $errors[] = "Inserisci le allergie.";
    if (empty($fumo)) $errors[] = "Inserisci se il/la paziente fuma.";
    if (empty($alcol)) $errors[] = "Inserisci se il/la paziente fa uso di alcol.";
    if (empty($patologie)) $errors[] = "Inserisci se il/la paziente ha patologie.";
    if (empty($interventi)) $errors[] = "Inserisci se il/la paziente ha subito interventi.";
    if (empty($esami)) $errors[] = "Inserisci se il/la paziente ha fatto esami.";

    /* SE NON CI SONO ERRORI */
    if (empty($errors)) {
        if ($editing) {
            $stmt = $conn->prepare("UPDATE anamnesi SET allergie = ?, dettagli_allergie = ?, fumo = ?, dettagli_fumo = ?, alcol = ?, dettagli_alcol = ?, patologie = ?, dettagli_patologie = ?, interventi = ?, dettagli_interventi = ?, esami = ?, dettagli_esami = ? WHERE id = ? AND fk_paziente = ?");
            $stmt->bind_param(
                "ssssssssssssii",
                $allergie, $dettagli_allergie, $fumo, $dettagli_fumo, $alcol, $dettagli_alcol,
                $patologie, $dettagli_patologie, $interventi, $dettagli_interventi, $esami, $dettagli_esami,
                $anamnesi_id, $id_paziente
            );
        } else {
            $stmt = $conn->prepare("
                INSERT INTO anamnesi 
                (fk_paziente, allergie, dettagli_allergie, fumo, dettagli_fumo, alcol, dettagli_alcol, patologie, dettagli_patologie, interventi, dettagli_interventi, esami, dettagli_esami)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "issssssssssss",
                $id_paziente, $allergie, $dettagli_allergie, $fumo, $dettagli_fumo, $alcol, $dettagli_alcol,
                $patologie, $dettagli_patologie, $interventi, $dettagli_interventi, $esami, $dettagli_esami
            );
        }

        if ($stmt->execute()) {
            if ($editing) {
                $success_msg = "Anamnesi aggiornata correttamente!";
            } else {
                $success_msg = "Anamnesi inserita correttamente!";
                $anamnesi_id = $stmt->insert_id;
            }
            // ricarica in modifica sulla nuova/aggiornata anamnesi
            header("Location: aggiungi_anamnesi.php?id=$id_paziente&anamnesi_id=$anamnesi_id");
            exit;
        } else {
            $errors[] = "Errore durante il salvataggio: " . $stmt->error;
        }
        $stmt->close();
    }
}

/* Aggiorna lista anamnesi (dopo eventuali modifiche) */
$anamnesi_list = [];
$stmt = $conn->prepare("SELECT * FROM anamnesi WHERE fk_paziente = ? ORDER BY id DESC");
$stmt->bind_param("i", $id_paziente);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $anamnesi_list[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Igea - Anamnesi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Stili extra per il form adattati al tema chiaro e scuro */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-col-small {
            flex: 1;
            min-width: 120px;
        }
        .form-col-large {
            flex: 3;
            min-width: 250px;
        }
        .form-label {
            display: block;
            font-size: 0.9rem;
            color: #475569;
            margin-bottom: 8px;
            font-weight: 600;
            transition: color 0.3s;
        }
        .form-input {
            width: 100%;
            height: 40px;
            padding: 0 15px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 0.95rem;
            outline: none;
            background: #f8fafc;
            color: #0f172a;
            transition: all 0.3s;
        }
        .form-input:focus {
            border-color: #3b82f6;
            background: #ffffff;
        }
        
        /* Assicura che le opzioni si vedano bene nel menù a tendina */
        .form-input option {
            background-color: #ffffff;
            color: #0f172a;
        }

        /* Lista anamnesi */
        .anamnesi-list { margin-bottom: 30px; }
        .anamnesi-item { padding: 15px; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
        .anamnesi-meta { color: #64748b; font-size:0.9rem; margin-bottom: 5px; font-weight: bold;}
        .anamnesi-text { color: #0f172a; }
        .btn-small { font-size:0.85rem; padding:6px 10px; margin-left:8px; text-decoration:none; border-radius:4px; background:#f1f5f9; color:#0f172a; border: 1px solid #cbd5e1; transition: all 0.2s; }
        .btn-small:hover { background: #e2e8f0; }

        /* Stili Pop-up Modale */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal {
            background: #ffffff; padding: 25px; border-radius: 8px;
            width: 90%; max-width: 450px; text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .modal h3 { margin-top: 0; color: #0f172a; }
        .modal p { color: #475569; margin-bottom: 25px; line-height: 1.5; }
        .modal-buttons { display: flex; justify-content: center; gap: 15px; }
        .btn-cancel {
            background: #e2e8f0; color: #475569; border: none; font-weight: bold;
            padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: 0.2s;
        }
        .btn-cancel:hover { background: #cbd5e1; }
        .btn-elimina {
            background-color: #ef4444; color: #ffffff; border: 1px solid #dc2626; font-weight: bold;
            padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: 0.2s; text-decoration: none;
        }
        .btn-elimina:hover { background-color: #dc2626; color: #ffffff; }

        /* Messaggi */
        .msg-error { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .msg-success { background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; padding: 15px; margin-bottom: 20px; border-radius: 4px; }

        /* --- OVERRIDE TEMA SCURO --- */
        body.dark-mode .form-label { color: #cbd5e1; }
        body.dark-mode .form-input { background-color: #1e293b; border-color: #334155; color: #f8fafc; }
        body.dark-mode .form-input:focus { border-color: #3b82f6; background-color: #0f172a; }
        body.dark-mode .form-input option { background-color: #1e293b; color: #f8fafc; }
        
        body.dark-mode .anamnesi-item { border-bottom-color: #334155; }
        body.dark-mode .anamnesi-text { color: #f8fafc; }
        body.dark-mode .anamnesi-meta { color: #94a3b8; }
        body.dark-mode .btn-small { background-color: #334155; color: #f8fafc; border-color: #475569; }
        body.dark-mode .btn-small:hover { background-color: #475569; }

        body.dark-mode .msg-error { background: #450a0a; border-color: #f87171; color: #fca5a5; }
        body.dark-mode .msg-success { background: #052e16; border-color: #4ade80; color: #86efac; }
        
        body.dark-mode .modal { background-color: #1e293b; color: #f8fafc; border: 1px solid #334155; }
        body.dark-mode .modal h3 { color: #f8fafc; }
        body.dark-mode .modal p { color: #cbd5e1; }
        body.dark-mode .btn-cancel { background-color: #334155; color: #f8fafc; }
        body.dark-mode .btn-cancel:hover { background-color: #475569; }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>

    <aside class="sidebar">
        <h1>Igea</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="pazienti.php" class="active">Pazienti</a>
            <a href="farmaci.php">Terapie</a>
            <a href="alimenti.php">Alimenti</a>
        </nav>
    </aside>

    <main class="main-content">

        <div class="card-cruscotto">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                <div>
                    <h2 style="margin: 0; font-size: 1.8rem;">Anamnesi di <?php echo htmlspecialchars($nome_paziente); ?></h2>
                    <p style="color: #64748b; margin-top: 5px; margin-bottom: 0;">
                        <?php echo $existing_anamnesi ? 'Modifica le informazioni cliniche del paziente.' : 'Inserisci le informazioni cliniche del paziente.'; ?>
                    </p>
                </div>
                <a href="pazienti.php" class="btn-azione" style="font-size: 0.9em; padding: 10px 16px; text-decoration: none; white-space: nowrap;">← Torna alla lista pazienti</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="msg-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error) { echo "<li>".htmlspecialchars($error)."</li>"; } ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_msg)): ?>
                <div class="msg-success">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($anamnesi_list)): ?>
                <div class="anamnesi-list">
                    <h3 style="margin-top:0;">Anamnesi precedenti</h3>
                    <?php foreach ($anamnesi_list as $a): ?>
                        <div class="anamnesi-item">
                            <div>
                                <div class="anamnesi-meta">Scheda ID: <?php echo htmlspecialchars($a['id']); ?> Data: <?php echo htmlspecialchars($a['data']); ?></div>
                                <div class="anamnesi-text">
                                    <strong>Allergie:</strong> <?php echo htmlspecialchars($a['allergie']); ?>; 
                                    <strong>Fumo:</strong> <?php echo htmlspecialchars($a['fumo']); ?>; 
                                    <strong>Alcol:</strong> <?php echo htmlspecialchars($a['alcol']); ?>;
                                    <strong>Patologie:</strong> <?php echo htmlspecialchars($a['patologie']); ?>; 
                                    <strong>Interventi:</strong> <?php echo htmlspecialchars($a['interventi']); ?>;
                                    <strong>Esami:</strong> <?php echo htmlspecialchars($a['esami']); ?>
                                </div>
                            </div>
                            <div style="white-space:nowrap;">
                                <a class="btn-small" href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&anamnesi_id=<?php echo $a['id']; ?>">Modifica</a>
                                <a class="btn-small btn-elimina" style="color: white;" href="#" onclick="confermaEliminazioneAnamnesi('aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&delete_anamnesi=<?php echo $a['id']; ?>'); return false;">Elimina</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 25px;">
                <a href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&new=1" class="btn-azione" style="font-size: 0.85rem; padding: 8px 12px; text-decoration: none;">+ Aggiungi una Nuova Anamnesi</a>
            </div>

            <form method="POST" action="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>">
                <?php if ($existing_anamnesi): ?>
                    <input type="hidden" name="anamnesi_id" value="<?php echo $anamnesi_id; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-col-small">
                        <label class="form-label">Allergie</label>
                        <select name="allergie" class="form-input" required>
                            <option value="">-- Seleziona --</option>
                            <option value="No" <?php if($allergie === 'No') echo 'selected'; ?>>No</option>
                            <option value="Si" <?php if($allergie === 'Si') echo 'selected'; ?>>Sì</option>
                        </select>
                    </div>
                    <div class="form-col-large">
                        <label class="form-label">Dettagli allergie</label>
                        <input type="text" name="dettagli_allergie" class="form-input" placeholder="Specifica se presenti..." value="<?php echo htmlspecialchars($dettagli_allergie); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-small">
                        <label class="form-label">Fuma</label>
                        <select name="fumo" class="form-input" required>
                            <option value="">-- Seleziona --</option>
                            <option value="No" <?php if($fumo === 'No') echo 'selected'; ?>>No</option>
                            <option value="Si" <?php if($fumo === 'Si') echo 'selected'; ?>>Sì</option>
                        </select>
                    </div>
                    <div class="form-col-large">
                        <label class="form-label">Dettagli fumo</label>
                        <input type="text" name="dettagli_fumo" class="form-input" placeholder="Quantità/Frequenza..." value="<?php echo htmlspecialchars($dettagli_fumo); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-small">
                        <label class="form-label">Alcol</label>
                        <select name="alcol" class="form-input" required>
                            <option value="">-- Seleziona --</option>
                            <option value="No" <?php if($alcol === 'No') echo 'selected'; ?>>No</option>
                            <option value="Si" <?php if($alcol === 'Si') echo 'selected'; ?>>Sì</option>
                        </select>
                    </div>
                    <div class="form-col-large">
                        <label class="form-label">Dettagli alcol</label>
                        <input type="text" name="dettagli_alcol" class="form-input" placeholder="Frequenza..." value="<?php echo htmlspecialchars($dettagli_alcol); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-small">
                        <label class="form-label">Patologie</label>
                        <select name="patologie" class="form-input" required>
                            <option value="">-- Seleziona --</option>
                            <option value="No" <?php if($patologie === 'No') echo 'selected'; ?>>No</option>
                            <option value="Si" <?php if($patologie === 'Si') echo 'selected'; ?>>Sì</option>
                        </select>
                    </div>
                    <div class="form-col-large">
                        <label class="form-label">Dettagli patologie</label>
                        <input type="text" name="dettagli_patologie" class="form-input" placeholder="Specifica patologie..." value="<?php echo htmlspecialchars($dettagli_patologie); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-small">
                        <label class="form-label">Interventi</label>
                        <select name="interventi" class="form-input" required>
                            <option value="">-- Seleziona --</option>
                            <option value="No" <?php if($interventi === 'No') echo 'selected'; ?>>No</option>
                            <option value="Si" <?php if($interventi === 'Si') echo 'selected'; ?>>Sì</option>
                        </select>
                    </div>
                    <div class="form-col-large">
                        <label class="form-label">Dettagli interventi</label>
                        <input type="text" name="dettagli_interventi" class="form-input" placeholder="Tipo di interventi e anno..." value="<?php echo htmlspecialchars($dettagli_interventi); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-small">
                        <label class="form-label">Esami recenti</label>
                        <select name="esami" class="form-input" required>
                            <option value="">-- Seleziona --</option>
                            <option value="No" <?php if($esami === 'No') echo 'selected'; ?>>No</option>
                            <option value="Si" <?php if($esami === 'Si') echo 'selected'; ?>>Sì</option>
                        </select>
                    </div>
                    <div class="form-col-large">
                        <label class="form-label">Dettagli esami</label>
                        <input type="text" name="dettagli_esami" class="form-input" placeholder="Quali esami e quando..." value="<?php echo htmlspecialchars($dettagli_esami); ?>">
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn-azione" style="height: 40px; padding: 0 30px; border: none; font-size: 1rem; cursor: pointer;">
                        <?php echo $existing_anamnesi ? 'Aggiorna Anamnesi' : 'Salva Anamnesi'; ?>
                    </button>
                </div>

            </form>
        </div>

    </main>

    <div class="modal-overlay" id="confirmModalAnamnesi">
        <div class="modal">
            <h3>Conferma eliminazione</h3>
            <p>Sei sicuro di voler eliminare questa anamnesi? I dati andranno persi per sempre.</p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="chiudiModalAnamnesi()">Annulla</button>
                <button class="btn-elimina" id="confirmDeleteAnamnesi" style="border: none;">Elimina</button>
            </div>
        </div>
    </div>

    <script>
        let deleteAnamnesiUrl = "";

        function confermaEliminazioneAnamnesi(url) {
            deleteAnamnesiUrl = url;
            document.getElementById("confirmModalAnamnesi").style.display = "flex";
        }

        function chiudiModalAnamnesi() {
            document.getElementById("confirmModalAnamnesi").style.display = "none";
        }

        document.getElementById("confirmDeleteAnamnesi").onclick = function() {
            window.location.href = deleteAnamnesiUrl;
        };
    </script>

</body>
</html>