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
   CARICAMENTO INTEGRATORI
========================= */

$integratori = [];

$result = $conn->query("SELECT id, nome, descrizione FROM integratori ORDER BY nome ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $integratori[] = $row;
    }
}

/* =========================
   CARICAMENTO FARMACI
========================= */
$farmaci = [];

$result = $conn->query("SELECT id, nome, descrizione FROM farmaci ORDER BY nome ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $farmaci[] = $row;
    }
}

/* =========================
   CARICAMENTO SUPPORTI 
========================= */

$supporti = [];

$result = $conn->query("SELECT id, nome, descrizione FROM supporti ORDER BY nome ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $supporti[] = $row;
    }
}

/* =========================
   CARICAMENTO TERAPIE
========================= */

$terapie = [];

$result = $conn->query("SELECT id, nome, descrizione FROM terapie ORDER BY nome ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $terapie[] = $row;
    }
}

/* =========================
   GESTIONE FORM
========================= */

$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ore_sonno = trim($_POST["ore_sonno"] ?? "");
    $risvegli_notturni = trim($_POST["risvegli_notturni"] ?? "");
    $difficolta_sonno = trim($_POST["difficolta_sonno"] ?? "");
    $qualita_sonno = trim($_POST["qualita_sonno"] ?? "");

    $livello_stress = trim($_POST["livello_stress"] ?? "");

    $ansia = trim($_POST["ansia"] ?? "");
    $umore = trim($_POST["umore"] ?? "");
    $motivazione = trim($_POST["motivazione"] ?? "");
    $concentrazione = trim($_POST["concentrazione"] ?? "");

    $attivita_fisica_nome = $_POST["attivita_fisica"] ?? [];
    $attivita_fisica_descrizione = $_POST["descrizione_attivita"] ?? [];
    $attivita_fisica_note = $_POST["note_attivita"] ?? [];

    if (!is_array($attivita_fisica_nome)) {
        $attivita_fisica_nome = $attivita_fisica_nome !== '' ? [$attivita_fisica_nome] : [];
    }
    if (!is_array($attivita_fisica_descrizione)) {
        $attivita_fisica_descrizione = $attivita_fisica_descrizione !== '' ? [$attivita_fisica_descrizione] : [];
    }
    if (!is_array($attivita_fisica_note)) {
        $attivita_fisica_note = $attivita_fisica_note !== '' ? [$attivita_fisica_note] : [];
    }

    $alimentazione = trim($_POST["alimentazione"] ?? "");

    $domande = $_POST["domande"] ?? [];
    $osservazioni = $_POST["osservazioni"] ?? [];

    $farmaci_selezionati = $_POST["farmaci"] ?? [];
    $integratori_selezionati = $_POST["integratori"] ?? [];
    $supporti_selezionati = $_POST["supporti"] ?? [];
    $terapie_selezionate = $_POST["terapie"] ?? [];
    $alimenti_evitat = $_POST["alimenti_evitat"] ?? [];

    if ($livello_stress !== "") {
        if (!is_numeric($livello_stress) || intval($livello_stress) < 1 || intval($livello_stress) > 10) {
            $errors[] = "Livello stress deve essere un numero tra 1 e 10.";
        }
        $livello_stress_val = intval($livello_stress);
    } else {
        $livello_stress_val = 0;
    }

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO visita (livello_stress, alimentazione, fk_paziente) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $livello_stress_val, $alimentazione, $id_paziente);
            if (!$stmt->execute()) {
                throw new Exception("Errore inserimento visita: " . $stmt->error);
            }
            $id_visita = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO sonno (ore, risvegli, difficolta, qualita, fk_visita) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $ore_sonno, $risvegli_notturni, $difficolta_sonno, $qualita_sonno, $id_visita);
            if (!$stmt->execute()) {
                throw new Exception("Errore inserimento sonno: " . $stmt->error);
            }
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO `stato_psico-fisico` (ansia, umore, motivazione, concentrazione, fk_visita) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $ansia, $umore, $motivazione, $concentrazione, $id_visita);
            if (!$stmt->execute()) {
                throw new Exception("Errore inserimento stato psico-fisico: " . $stmt->error);
            }
            $stmt->close();

            $attivita_count = max(count($attivita_fisica_nome), count($attivita_fisica_descrizione), count($attivita_fisica_note));
            for ($i = 0; $i < $attivita_count; $i++) {
                $nome = trim($attivita_fisica_nome[$i] ?? "");
                $descrizione = trim($attivita_fisica_descrizione[$i] ?? "");
                $note = trim($attivita_fisica_note[$i] ?? "");

                if ($nome === '' && $descrizione === '' && $note === '') {
                    continue;
                }

                $stmt = $conn->prepare("INSERT INTO attivita_fisica (nome, descrizione, note) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nome, $descrizione, $note);
                if (!$stmt->execute()) {
                    throw new Exception("Errore inserimento attività fisica: " . $stmt->error);
                }
                $id_attivita = $conn->insert_id;
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO attivita_visita (fk_visita, fk_attivita) VALUES (?, ?)");
                $stmt->bind_param("ii", $id_visita, $id_attivita);
                if (!$stmt->execute()) {
                    throw new Exception("Errore inserimento attivita_visita: " . $stmt->error);
                }
                $stmt->close();
            }

            foreach ($domande as $domanda) {
                $testo = trim($domanda["testo"] ?? "");
                $risposta = trim($domanda["risposta"] ?? "");
                if ($testo !== '' || $risposta !== '') {
                    $stmt = $conn->prepare("INSERT INTO domande (domanda, risposta, nota, fk_visita) VALUES (?, ?, '', ?)");
                    $stmt->bind_param("ssi", $testo, $risposta, $id_visita);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento domande: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($osservazioni as $osservazione) {
                $testo_osservazione = trim($osservazione);
                if ($testo_osservazione !== '') {
                    $stmt = $conn->prepare("INSERT INTO osservazioni_finali (osservazione, fk_visita) VALUES (?, ?)");
                    $stmt->bind_param("si", $testo_osservazione, $id_visita);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento osservazioni_finali: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($farmaci_selezionati as $id_farmaco) {
                $id_farmaco = intval($id_farmaco);
                if ($id_farmaco > 0) {
                    $stmt = $conn->prepare("INSERT INTO farmaci_prescritti (fk_visita, fk_farmaci) VALUES (?, ?)");
                    $stmt->bind_param("ii", $id_visita, $id_farmaco);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento farmaci_prescritti: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($integratori_selezionati as $id_integratore) {
                $id_integratore = intval($id_integratore);
                if ($id_integratore > 0) {
                    $stmt = $conn->prepare("INSERT INTO integratori_prescritti (fk_visita, fk_integratori) VALUES (?, ?)");
                    $stmt->bind_param("ii", $id_visita, $id_integratore);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento integratori_prescritti: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($supporti_selezionati as $id_supporto) {
                $id_supporto = intval($id_supporto);
                if ($id_supporto > 0) {
                    $stmt = $conn->prepare("INSERT INTO supporti_prescritti (fk_visita, fk_supporti) VALUES (?, ?)");
                    $stmt->bind_param("ii", $id_visita, $id_supporto);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento supporti_prescritti: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($terapie_selezionate as $id_terapia) {
                $id_terapia = intval($id_terapia);
                if ($id_terapia > 0) {
                    $stmt = $conn->prepare("INSERT INTO terapie_prescritte (fk_visita, fk_terapie, note) VALUES (?, ?, '')");
                    $stmt->bind_param("ii", $id_visita, $id_terapia);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento terapie_prescritte: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            foreach ($alimenti_evitat as $id_alimento) {
                $id_alimento = intval($id_alimento);
                if ($id_alimento > 0) {
                    $stmt = $conn->prepare("INSERT INTO alimenti_sospesi (fk_visita, fk_alimenti, note) VALUES (?, ?, '')");
                    $stmt->bind_param("ii", $id_visita, $id_alimento);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore inserimento alimenti_sospesi: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            $conn->commit();
            $success_msg = "Visita salvata correttamente.";

            header("Location: scheda_paziente.php?id=$id_paziente");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
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
if (!empty($success_msg)) {
    echo "<p style='color:green;'>" . htmlspecialchars($success_msg) . "</p>";
}
if (!empty($errors)) {
    echo "<ul style='color:red;'>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
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
        <input type="number" id="livello_stress" name="livello_stress" min="1" max="10">
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
        <label for="attivita_fisica">Attività fisica:</label>
        <div id="contenitore_attivita_fisica"></div>
        <button type="button" onclick="aggiungiAttivitaFisica()">+ Aggiungi attività fisica</button>
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
        <label for="farmaci">Farmaci:</label>
        <select name="farmaci[]" id="farmaci" multiple>
            <?php foreach ($farmaci as $farmaco): ?>
                <option value="<?php echo $farmaco['id']; ?>">
                    <?php echo htmlspecialchars($farmaco['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="integratori">Integratori:</label>
        <select name="integratori[]" id="integratori" multiple>
            <?php foreach ($integratori as $integratore): ?>
                <option value="<?php echo $integratore['id']; ?>">
                    <?php echo htmlspecialchars($integratore['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="supporti">Supporti:</label>
        <select name="supporti[]" id="supporti" multiple>
            <?php foreach ($supporti as $supporto): ?>
                <option value="<?php echo $supporto['id']; ?>">
                    <?php echo htmlspecialchars($supporto['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="terapie">Terapie:</label>
        <select name="terapie[]" id="terapie" multiple>
            <?php foreach ($terapie as $terapia): ?>
                <option value="<?php echo $terapia['id']; ?>">
                    <?php echo htmlspecialchars($terapia['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

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

    <div>
        <h2>Osservazioni finali:</h2>
        <div id="contenitore_osservazioni"></div>
        <button type="button" onclick="aggiungiOsservazione()">+ Aggiungi osservazione</button>
    </div>
    <br>
    <button type="submit">Salva visita</button>
</form>

<script>
let contatore = 0;

function aggiungiDomanda() {
    contatore++;

    let div = document.createElement("div");
    div.classList.add("blocco-domanda");

    div.innerHTML = `
        <label>Domanda:</label>
        <input type="text" name="domande[${contatore}][testo]">

        <label>Risposta:</label>
        <input type="text" name="domande[${contatore}][risposta]">

        <button type="button" onclick="this.parentElement.remove()">Rimuovi</button>
        <br><br>
    `;

    document.getElementById("contenitore_domande").appendChild(div);
}

let counterOsservazioni = 0;

function aggiungiOsservazione() {
    counterOsservazioni++;

    let div = document.createElement("div");
    div.classList.add("blocco-osservazione");

    div.innerHTML = `
        <label>Osservazione:</label>
        <input type="text" name="osservazioni[${counterOsservazioni}]">

        <button type="button" onclick="this.parentElement.remove()">Rimuovi</button>
        <br><br>
    `;

    document.getElementById("contenitore_osservazioni").appendChild(div);
}

let counterAttivita = 0;

function aggiungiAttivitaFisica() {
    counterAttivita++;

    let div = document.createElement("div");
    div.classList.add("blocco-attivita");

    div.innerHTML = `
        <label>Nome attività:</label>
        <input type="text" name="attivita_fisica[]">

        <label>Descrizione:</label>
        <input type="text" name="descrizione_attivita[]">

        <label>Note:</label>
        <input type="text" name="note_attivita[]">

        <button type="button" onclick="this.parentElement.remove()">Rimuovi</button>
        <br><br>
    `;

    document.getElementById("contenitore_attivita_fisica").appendChild(div);
}

/* FILTRO */
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
