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

    /* VALIDAZIONI */

    if (empty($allergie)) $errors[] = "Inserisci le allergie.";
    if (empty($fumo)) $errors[] = "Inserisci se il/la paziente fuma.";
    if (empty($alcol)) $errors[] = "Inserisci se il/la paziente fa uso di alcol.";
    if (empty($patologie)) $errors[] = "Inserisci se il/la paziente ha patologie.";
    if (empty($interventi)) $errors[] = "Inserisci se il/la paziente ha subito interventi.";
    if (empty($esami)) $errors[] = "Inserisci se il/la paziente ha fatto esami.";

    /* SE NON CI SONO ERRORI */
    if (empty($errors)) {

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

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Anamnesi inserita correttamente!</p>";
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
</head>
<body>

<h1>Igea - Anamnesi Paziente</h1>

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

    <div>
        Allergie:
        <select name="allergie" required>
            <option value="">-- Seleziona --</option>
            <option value="No">No</option>
            <option value="Si">Sì</option>
        </select>
    </div>

    <div>
        Dettagli allergie:
        <input type="text" name="dettagli_allergie">
    </div>

    <div>
        Fuma:
        <select name="fumo" required>
            <option value="">-- Seleziona --</option>
            <option value="No">No</option>
            <option value="Si">Sì</option>
        </select>
    </div>

    <div>
        Dettagli fumo:
        <input type="text" name="dettagli_fumo">
    </div>

    <div>
        Alcol:
        <select name="alcol" required>
            <option value="">-- Seleziona --</option>
            <option value="No">No</option>
            <option value="Si">Sì</option>
        </select>
    </div>

    <div>
        Dettagli alcol:
        <input type="text" name="dettagli_alcol">
    </div>

    <div>
        Patologie:
        <select name="patologie" required>
            <option value="">-- Seleziona --</option>
            <option value="No">No</option>
            <option value="Si">Sì</option>
        </select>
    </div>

    <div>
        Dettagli patologie:
        <input type="text" name="dettagli_patologie">
    </div>

    <div>
        Interventi:
        <select name="interventi" required>
            <option value="">-- Seleziona --</option>
            <option value="No">No</option>
            <option value="Si">Sì</option>
        </select>
    </div>

    <div>
        Dettagli interventi:
        <input type="text" name="dettagli_interventi">
    </div>

    <div>
        Esami:
        <select name="esami" required>
            <option value="">-- Seleziona --</option>
            <option value="No">No</option>
            <option value="Si">Sì</option>
        </select>
    </div>

    <div>
        Dettagli esami:
        <input type="text" name="dettagli_esami">
    </div>

    <br>
    <button type="submit">Salva Anamnesi</button>

</form>

<br>
<a href="pazienti.php">Torna alla lista pazienti</a>

</body>
</html>
