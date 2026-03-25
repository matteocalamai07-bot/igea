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
   CARICAMENTO ALIMENTI
========================= */
$alimenti = [];

$result = $conn->query("SELECT id, nome FROM alimenti ORDER BY nome ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $alimenti[] = $row;
    }
}

/* =========================
   GESTIONE FORM
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Igea - Nuova Visita Paziente</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Igea - Nuova visita paziente</h1>
<br>

<div class="top-links">
    <a href="scheda_paziente.php?id=<?php echo $id_paziente; ?>" class="btn-top">Torna alla scheda del paziente</a>
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

<form method="POST" action="nuova_visita.php?id=<?php echo $id_paziente; ?>">

    <div>
        <h2>Qualità del sonno</h2>
        <label for="ore_sonno">Ore di sonno:</label>
        <input type="text" id="ore_sonno" name="ore_sonno">
        <br>

        <label for="risvegli_notturni">Risvegli notturni:</label>
        <input type="text" id="risvegli_notturni" name="risvegli_notturni">
        <br>

        <label for="difficolta_sonno">Difficoltà ad addormentarsi:</label>
        <input type="text" id="difficolta_sonno" name="difficolta_sonno">
        <br>

        <label for="qualita_sonno">Qualità percepita del sonno:</label>
        <input type="text" id="qualita_sonno" name="qualita_sonno">
    </div>

    <div>
        <h2>Livello di stress</h2>
        <label for="livello_stress">Valutazione (1-10):</label>
        <input type="number" id="livello_stress" name="livello_stress" min="1" max="10" required>
    </div>

    <div>
        <h2>Stato psico-fisico</h2>
        <label for="ansia">Ansia:</label>
        <input type="text" id="ansia" name="ansia">
        <br>

        <label for="umore">Umore:</label>
        <input type="text" id="umore" name="umore">
        <br>

        <label for="motivazione">Motivazione:</label>
        <input type="text" id="motivazione" name="motivazione">
        <br>

        <label for="concentrazione">Concentrazione:</label>
        <input type="text" id="concentrazione" name="concentrazione">
    </div>

    <div>
        <h2>Stile di vita</h2>
        <label for="attivita_fisica">Nome attività fisica:</label>
        <input type="text" id="attivita_fisica" name="attivita_fisica">
        <br>

        <label for="descrizione_attivita">Descrizione attività fisica:</label>
        <input type="text" id="descrizione_attivita" name="descrizione_attivita">
        <br>

        <label for="note_attivita">Note attività fisica (minuti):</label>
        <input type="text" id="note_attivita" name="note_attivita">
        <br>

        <label for="alimentazione">Alimentazione:</label>
        <input type="text" id="alimentazione" name="alimentazione">
    </div>

    <div>
        <h2>Aggiunta domande</h2>
        <div id="contenitore_domande"></div>
        <button type="button" onclick="aggiungiDomanda()">+ Aggiungi domanda</button>
    </div>

    <div>
        <h2>Supporti utilizzati</h2>
    </div>

    <!-- ✅ SEZIONE CORRETTA -->
    <div>
        <h2>Alimenti da evitare</h2>

        <input type="text" id="cercaAlimento" placeholder="Cerca alimento..." onkeyup="filtraAlimenti()">

        <div id="listaAlimenti" style="max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px;">
            <?php foreach ($alimenti as $alimento): ?>
                <div class="alimento-item">
                    <label>
                        <input type="checkbox" name="alimenti_evitat[]" value="<?php echo $alimento['id']; ?>">
                        <?php echo htmlspecialchars($alimento['nome']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</form>

<script>
let contatore = 0;

function aggiungiDomanda() {
    contatore++;

    let div = document.createElement("div");
    div.classList.add("blocco-domanda");

    div.innerHTML = `
        <label>Domanda:</label>
        <input type="text" name="domande[${contatore}][testo]" required>

        <label>Risposta:</label>
        <input type="text" name="domande[${contatore}][risposta]" required>

        <button type="button" onclick="this.parentElement.remove()">Rimuovi</button>
        <br><br>
    `;

    document.getElementById("contenitore_domande").appendChild(div);
}

/* ✅ FILTRO CORRETTO */
function filtraAlimenti() {
    let input = document.getElementById("cercaAlimento").value.toLowerCase();
    let items = document.getElementsByClassName("alimento-item");

    for (let i = 0; i < items.length; i++) {
        let testo = items[i].innerText.toLowerCase();

        if (testo.includes(input)) {
            items[i].style.display = "block";
        } else {
            items[i].style.display = "none";
        }
    }
}
</script>

</body>
</html>

<?php
$conn->close();
?>