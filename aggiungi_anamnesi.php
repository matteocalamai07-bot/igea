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
        /* Stili extra per il form per non toccare il file CSS principale */
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
        }
        .form-input {
            width: 100%;
            height: 40px;
            padding: 0 15px;
            border: 1px solid rgba(15,23,42,0.15);
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 0.95rem;
            outline: none;
            background: #f8fafc;
        }
        .form-input:focus {
            border-color: #3b82f6;
            background: #ffffff;
        }

        /* Lista anamnesi */
        .anamnesi-list { margin-bottom: 20px; }
        .anamnesi-item { padding: 10px; border-bottom: 1px solid #e6e6e6; display:flex; justify-content:space-between; align-items:center; }
        .anamnesi-meta { color: #64748b; font-size:0.9rem; }
        .btn-small { font-size:0.85rem; padding:6px 10px; margin-left:8px; text-decoration:none; border-radius:4px; background:#f1f5f9; color:#0f172a; }
    </style>
</head>
<body>

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

        <div>
            <div style="margin-bottom: 20px;">
                <a href="pazienti.php" style="color: #475569; text-decoration: none; font-size: 0.9rem;">&larr; Torna alla lista pazienti</a>
            </div>
            <h1 style="font-size: 2rem; color: #0f172a; margin-top: 0; margin-bottom: 5px;">
                Anamnesi di <?php echo htmlspecialchars($nome_paziente); ?>
            </h1>
            <p style="color: #64748b; margin-top: 0; margin-bottom: 30px;">
                <?php echo $existing_anamnesi ? 'Modifica le informazioni cliniche del paziente.' : 'Inserisci le informazioni cliniche del paziente.'; ?>
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error) { echo "<li>".htmlspecialchars($error)."</li>"; } ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div style="background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <div class="card-cruscotto">

            <!-- Lista anamnesi esistenti -->
            <?php if (!empty($anamnesi_list)): ?>
                <div class="anamnesi-list">
                    <h3 style="margin-top:0;">Anamnesi precedenti</h3>
                    <?php foreach ($anamnesi_list as $a): ?>
                        <div class="anamnesi-item">
                            <div>
                                <div class="anamnesi-meta">ID <?php echo htmlspecialchars($a['id']); ?></div>
                                <div style="color:#0f172a;">
                                    Allergie: <?php echo htmlspecialchars($a['allergie']); ?>;
                                    Patologie: <?php echo htmlspecialchars($a['patologie']); ?>;
                                    Esami: <?php echo htmlspecialchars($a['esami']); ?>
                                </div>
                            </div>
                            <div style="white-space:nowrap;">
                                <a class="btn-small" href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&anamnesi_id=<?php echo $a['id']; ?>">Modifica</a>
                                <a class="btn-small" href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&delete_anamnesi=<?php echo $a['id']; ?>" onclick="return confirm('Eliminare questa anamnesi?')">Elimina</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom:16px;">
                <a href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>&new=1" class="btn-small">+ Nuova anamnesi</a>
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

</body>
</html>