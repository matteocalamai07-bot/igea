<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$errors = [];

/* =========================
   CONTROLLO ID PAZIENTE
========================= */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("ID paziente non valido.");
}

$id_paziente = intval($_GET["id"]);

/* Verifico che il paziente esista */
$check = $conn->prepare("SELECT id FROM paziente WHERE id = ?");
$check->bind_param("i", $id_paziente);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Paziente non trovato.");
}
$check->close();

/* Cerco un'anamnesi già esistente per questo paziente */
$existing_anamnesi = null;
$anamnesi_id = null;
$stmt = $conn->prepare("SELECT * FROM anamnesi WHERE fk_paziente = ?");
$stmt->bind_param("i", $id_paziente);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $existing_anamnesi = $res->fetch_assoc();
    $anamnesi_id = $existing_anamnesi['id'];
}
$stmt->close();

/* inizializzo le variabili del form (utili anche in caso di modifica o errore di validazione) */
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
   GESTIONE FORM
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $allergie = trim($_POST["allergie"]);
    $dettagli_allergie = trim($_POST["dettagli_allergie"]);
    $fumo = trim($_POST["fumo"]);
    $dettagli_fumo = trim($_POST["dettagli_fumo"]);
    $alcol = trim($_POST["alcol"]);
    $dettagli_alcol = trim($_POST["dettagli_alcol"]);
    $patologie = trim($_POST["patologie"]);
    $dettagli_patologie = trim($_POST["dettagli_patologie"]);
    $interventi = trim($_POST["interventi"]);
    $dettagli_interventi = trim($_POST["dettagli_interventi"]);
    $esami = trim($_POST["esami"]);
    $dettagli_esami = trim($_POST["dettagli_esami"]);

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
            $stmt = $conn->prepare("UPDATE anamnesi SET allergie = ?, dettagli_allergie = ?, fumo = ?, dettagli_fumo = ?, alcol = ?, dettagli_alcol = ?, patologie = ?, dettagli_patologie = ?, interventi = ?, dettagli_interventi = ?, esami = ?, dettagli_esami = ? WHERE id = ?");
            $stmt->bind_param(
                "ssssssssssssi",
                $allergie,
                $dettagli_allergie,
                $fumo,
                $dettagli_fumo,
                $alcol,
                $dettagli_alcol,
                $patologie,
                $dettagli_patologie,
                $interventi,
                $dettagli_interventi,
                $esami,
                $dettagli_esami,
                $anamnesi_id
            );
        } else {
            $stmt = $conn->prepare("
                INSERT INTO anamnesi 
                (fk_paziente, allergie, dettagli_allergie, fumo, dettagli_fumo, alcol, dettagli_alcol, patologie, dettagli_patologie, interventi, dettagli_interventi, esami, dettagli_esami)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "issssssssssss",
                $id_paziente,
                $allergie,
                $dettagli_allergie,
                $fumo,
                $dettagli_fumo,
                $alcol,
                $dettagli_alcol,
                $patologie,
                $dettagli_patologie,
                $interventi,
                $dettagli_interventi,
                $esami,
                $dettagli_esami
            );
        }

        if ($stmt->execute()) {
            if ($editing) {
                echo "<p style='color:green;'>Anamnesi aggiornata correttamente!</p>";
            } else {
                echo "<p style='color:green;'>Anamnesi inserita correttamente!</p>";
                /* dopo l'inserimento consideriamo che ora esista un'anamnesi */
                $existing_anamnesi = true;
                $anamnesi_id = $stmt->insert_id;
            }
        } else {
            echo "<p style='color:red;'>Errore: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Igea - Inserimento Anamnesi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Igea - Anamnesi Paziente <?php echo $existing_anamnesi ? '(modifica)' : '(nuova)'; ?></h1>
<br>
<div class="top-links">
    <a href="pazienti.php" class="btn-top">Torna alla lista dei pazienti</a>
    <a href="index.php" class="btn-top">Torna alla Home</a>
</div>

<?php
if (!empty($errors)) {
    echo "<ul style='color:red;'>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}
?>

    <form method="POST" action="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>">
        <?php if ($existing_anamnesi): ?>
            <input type="hidden" name="anamnesi_id" value="<?php echo $anamnesi_id; ?>">
        <?php endif; ?>

        <div>
            Allergie:
            <select name="allergie" required>
                <option value="">-- Seleziona --</option>
                <option value="No" <?php if($allergie === 'No') echo 'selected'; ?>>No</option>
                <option value="Si" <?php if($allergie === 'Si') echo 'selected'; ?>>Sì</option>
            </select>
        </div>

        <div>
            Dettagli allergie:
            <input type="text" name="dettagli_allergie" value="<?php echo htmlspecialchars($dettagli_allergie); ?>">
        </div>

        <div>
            Fuma:
            <select name="fumo" required>
                <option value="">-- Seleziona --</option>
                <option value="No" <?php if($fumo === 'No') echo 'selected'; ?>>No</option>
                <option value="Si" <?php if($fumo === 'Si') echo 'selected'; ?>>Sì</option>
            </select>
        </div>

        <div>
            Dettagli fumo:
            <input type="text" name="dettagli_fumo" value="<?php echo htmlspecialchars($dettagli_fumo); ?>">
        </div>

        <div>
            Alcol:
            <select name="alcol" required>
                <option value="">-- Seleziona --</option>
                <option value="No" <?php if($alcol === 'No') echo 'selected'; ?>>No</option>
                <option value="Si" <?php if($alcol === 'Si') echo 'selected'; ?>>Sì</option>
            </select>
        </div>

        <div>
            Dettagli alcol:
            <input type="text" name="dettagli_alcol" value="<?php echo htmlspecialchars($dettagli_alcol); ?>">
        </div>

        <div>
            Patologie:
            <select name="patologie" required>
                <option value="">-- Seleziona --</option>
                <option value="No" <?php if($patologie === 'No') echo 'selected'; ?>>No</option>
                <option value="Si" <?php if($patologie === 'Si') echo 'selected'; ?>>Sì</option>
            </select>
        </div>

        <div>
            Dettagli patologie:
            <input type="text" name="dettagli_patologie" value="<?php echo htmlspecialchars($dettagli_patologie); ?>">
        </div>

        <div>
            Interventi:
            <select name="interventi" required>
                <option value="">-- Seleziona --</option>
                <option value="No" <?php if($interventi === 'No') echo 'selected'; ?>>No</option>
                <option value="Si" <?php if($interventi === 'Si') echo 'selected'; ?>>Sì</option>
            </select>
        </div>

        <div>
            Dettagli interventi:
            <input type="text" name="dettagli_interventi" value="<?php echo htmlspecialchars($dettagli_interventi); ?>">
        </div>

        <div>
            Esami:
            <select name="esami" required>
                <option value="">-- Seleziona --</option>
                <option value="No" <?php if($esami === 'No') echo 'selected'; ?>>No</option>
                <option value="Si" <?php if($esami === 'Si') echo 'selected'; ?>>Sì</option>
            </select>
        </div>

        <div>
            Dettagli esami:
            <input type="text" name="dettagli_esami" value="<?php echo htmlspecialchars($dettagli_esami); ?>">
        </div>

        <br>
        <button type="submit"><?php echo $existing_anamnesi ? 'Aggiorna Anamnesi' : 'Salva Anamnesi'; ?></button>

    </form>
</body>
</html>