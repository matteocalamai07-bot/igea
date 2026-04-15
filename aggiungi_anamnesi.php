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
    die("ID cliente non valido.");
}

$id_paziente = intval($_GET["id"]);

/* Verifico che il paziente esista */
$check = $conn->prepare("SELECT id, nome, cognome FROM paziente WHERE id = ?");
$check->bind_param("i", $id_paziente);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Cliente non trovato.");
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
        body.dark-mode .card-cruscotto { background-color: var(--bg-card); }
        
        .card-cruscotto {
            display: flex;
            flex-direction: column;
            padding: 15px 20px;
            padding-bottom: 0;
            overflow-y: auto;
            max-height: calc(100vh - 60px);
        }
        
        form {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        main.main-content {
            padding-bottom: 0 !important;
            overflow: hidden !important;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 12px;
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
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 5px;
            font-weight: 600;
            transition: color 0.3s;
        }
        .form-input {
            width: 100%;
            height: 36px;
            padding: 0 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 0.9rem;
            outline: none;
            background: var(--bg-card);
            color: var(--text-main);
            transition: all 0.3s;
        }
        .form-input:focus {
            border-color: var(--primary-color);
            background: var(--bg-card);
        }
        
        /* Assicura che le opzioni si vedano bene nel menù a tendina */
        .form-input option {
            background-color: var(--bg-card);
            color: var(--text-main);
        }

        /* Lista anamnesi */
        .anamnesi-list { margin-bottom: 30px; }
        .anamnesi-item { padding: 15px; border-bottom: 1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; }
        .anamnesi-meta { color: var(--text-muted); font-size:0.9rem; margin-bottom: 5px; font-weight: bold;}
        .anamnesi-text { color: var(--text-main); }
        .btn-small { font-size:0.9rem; padding:10px 20px; margin-left:8px; text-decoration:none; border-radius:4px; background:var(--text-main); color:white; border: none; transition: all 0.2s; cursor: pointer; }
        .btn-small:hover { opacity: 0.8; transform: translateY(-2px); }
        .btn-small.btn-elimina {
            background-color: #ef4444 !important; color: #ffffff !important; border: none !important;
        }
        .btn-small.btn-elimina:hover { background-color: #dc2626 !important; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); }

        /* Stili Pop-up Modale */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.55);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal {
            background: var(--bg-card); padding: 25px 50px; border-radius: 8px;
            width: 90%; max-width: 450px; text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--border-color);
        }
        .modal h3 { margin-top: 0; color: var(--text-main); }
        .modal p { color: var(--text-muted); margin-bottom: 25px; line-height: 1.5; }
        .modal-buttons { display: flex; justify-content: center; gap: 15px; }
        .btn-cancel {
            background: var(--bg-page); color: var(--text-main); border: 1px solid var(--border-color); font-weight: bold;
            padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: 0.2s;
        }
        .btn-cancel:hover { background: var(--border-color); }

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

        /* MODAL VECCHIO (deprecated) */
            background-color: #ef4444; color: #ffffff; border: 1px solid #dc2626; font-weight: bold;
            padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: 0.2s; text-decoration: none;
        }
        .btn-elimina:hover { background-color: #dc2626; color: #ffffff; }

        /* Messaggi */
        .msg-error { background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .msg-success { background: #dcfce7; border-left: 4px solid #22c55e; color: #166534; padding: 15px; margin-bottom: 20px; border-radius: 4px; }

        /* --- OVERRIDE TEMA SCURO --- */
        body.dark-mode .form-label { color: var(--text-main); }
        body.dark-mode .form-input { background-color: var(--bg-page); border-color: var(--border-color); color: var(--text-main); }
        body.dark-mode .form-input:focus { border-color: var(--primary-color); background-color: var(--bg-page); }
        body.dark-mode .form-input option { background-color: var(--bg-card); color: var(--text-main); }
        
        body.dark-mode .anamnesi-item { border-bottom-color: var(--border-color); }
        body.dark-mode .anamnesi-text { color: var(--text-main); }
        body.dark-mode .anamnesi-meta { color: var(--text-muted); }
        body.dark-mode .btn-small { background-color: var(--bg-page); color: var(--text-main); border-color: var(--border-color); }
        body.dark-mode .btn-small:hover { background-color: var(--border-color); }

        body.dark-mode .msg-error { background: #7f1d1d; border-color: #f87171; color: #fecaca; }
        body.dark-mode .msg-success { background: #166534; border-color: #86efac; color: #dcfce7; }
        
        body.dark-mode .modal { background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color); }
        body.dark-mode .modal h3 { color: var(--text-main); }
        body.dark-mode .modal p { color: var(--text-muted); }
        body.dark-mode .btn-cancel { background-color: var(--bg-page); color: var(--text-main); border-color: var(--border-color); }
        body.dark-mode .btn-cancel:hover { background-color: var(--border-color); }
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
            <a href="pazienti.php" class="active">Clienti</a>
            <a href="farmaci.php">Terapie</a>
            <a href="alimenti.php">Alimenti</a>
        </nav>
    </aside>

    <main class="main-content">

        <div class="card-cruscotto">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                <div>
                    <h2 style="margin: 0; font-size: 1.8rem; color: var(--text-main);">Anamnesi di <?php echo htmlspecialchars($nome_paziente); ?></h2>
                    <p style="color: var(--text-muted); margin-top: 5px; margin-bottom: 0;">
                        <?php echo $existing_anamnesi ? 'Modifica le informazioni cliniche del paziente.' : 'Inserisci le informazioni cliniche del paziente.'; ?>
                    </p>
                </div>
                <a href="pazienti.php" class="btn-azione" style="font-size: 0.9em; padding: 10px 16px; text-decoration: none; white-space: nowrap;">← Torna alla lista clienti</a>
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
                                <a class="btn-small btn-elimina" href="#" onclick="confermaEliminazioneAnamnesi('aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&delete_anamnesi=<?php echo $a['id']; ?>'); return false;">Elimina</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 15px;">
                <button type="button" class="btn-azione" style="font-size: 0.95em; padding: 12px 24px; border: none; cursor: pointer; background: linear-gradient(135deg, #6366f1, #0ea5e9) !important; color: white;" onclick="document.querySelector('form').submit();">
                    <?php echo $existing_anamnesi ? 'Aggiorna Anamnesi' : 'Salva Anamnesi'; ?>
                </button>
                <a href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&new=1" class="btn-azione" style="font-size: 0.95em; padding: 12px 24px; text-decoration: none; background: linear-gradient(135deg, #6366f1, #0ea5e9) !important; color: white;">+ Aggiungi una Nuova Anamnesi</a>
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

            </form>
        </div>

    </main>

    <!-- MODAL ELIMINAZIONE ELEGANTE -->
    <div class="delete-modal-overlay" id="deleteModalAnamnesi">
        <div class="delete-modal-dialog">
            <div class="delete-modal-header">
                <div class="delete-modal-header-icon">⚠️</div>
                <h2>Elimina Anamnesi</h2>
            </div>
            <div class="delete-modal-body">
                <p>Sei sicuro di voler eliminare questa anamnesi?</p>
                <p style="color: #dc2626; font-size: 0.85rem; margin-top: 12px;">⚠️ Questa azione non può essere annullata.</p>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="chiudiModalAnamnesi()">Annulla</button>
                <button type="button" class="btn-modal btn-modal-delete" id="confirmDeleteAnamnesi">Elimina</button>
            </div>
        </div>
    </div>

    <script>
        let deleteAnamnesiUrl = "";

        function confermaEliminazioneAnamnesi(url) {
            deleteAnamnesiUrl = url;
            document.getElementById("deleteModalAnamnesi").classList.add("active");
        }

        function chiudiModalAnamnesi() {
            document.getElementById("deleteModalAnamnesi").classList.remove("active");
            deleteAnamnesiUrl = "";
        }

        document.getElementById("confirmDeleteAnamnesi").addEventListener("click", function() {
            if (deleteAnamnesiUrl) {
                window.location.href = deleteAnamnesiUrl;
            }
        });

        // Chiudi modal cliccando fuori
        document.getElementById("deleteModalAnamnesi").addEventListener("click", function(event) {
            if (event.target === this) {
                chiudiModalAnamnesi();
            }
        });

        // Chiudi con tasto Escape
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                chiudiModalAnamnesi();
            }
        });
    </script>

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
