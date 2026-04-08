<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("ID visita non valido.");
}

$id_visita = intval($_GET["id"]);

/* =========================
   VISITA BASE
========================= */
$stmt = $conn->prepare("
    SELECT v.*, p.nome, p.cognome
    FROM visita v
    JOIN paziente p ON v.fk_paziente = p.id
    WHERE v.id = ?
");
$stmt->bind_param("i", $id_visita);
$stmt->execute();
$visita = $stmt->get_result()->fetch_assoc();

if (!$visita) {
    die("Visita non trovata.");
}

/* =========================
   SONNO
========================= */
$sonno = $conn->query("SELECT * FROM sonno WHERE fk_visita = $id_visita")->fetch_assoc();

/* =========================
   STATO PSICO-FISICO
========================= */
$stato = $conn->query("SELECT * FROM `stato_psico-fisico` WHERE fk_visita = $id_visita")->fetch_assoc();

/* =========================
   ATTIVITA FISICA
========================= */
$attivita = $conn->query("
    SELECT a.*
    FROM attivita_fisica a
    JOIN attivita_visita av ON a.id = av.fk_attivita
    WHERE av.fk_visita = $id_visita
");

/* =========================
   DOMANDE
========================= */
$domande = $conn->query("SELECT * FROM domande WHERE fk_visita = $id_visita");

/* =========================
   OSSERVAZIONI
========================= */
$osservazioni = $conn->query("SELECT * FROM osservazioni_finali WHERE fk_visita = $id_visita");

/* =========================
   FARMACI / INTEGRATORI / SUPPORTI / TERAPIE
========================= */
$farmaci = $conn->query("
    SELECT f.nome 
    FROM farmaci f
    JOIN farmaci_prescritti fp ON f.id = fp.fk_farmaci
    WHERE fp.fk_visita = $id_visita
");

$integratori = $conn->query("
    SELECT i.nome 
    FROM integratori i
    JOIN integratori_prescritti ip ON i.id = ip.fk_integratori
    WHERE ip.fk_visita = $id_visita
");

$supporti = $conn->query("
    SELECT s.nome 
    FROM supporti s
    JOIN supporti_prescritti sp ON s.id = sp.fk_supporti
    WHERE sp.fk_visita = $id_visita
");

$terapie = $conn->query("
    SELECT t.nome 
    FROM terapie t
    JOIN terapie_prescritte tp ON t.id = tp.fk_terapie
    WHERE tp.fk_visita = $id_visita
");

/* =========================
   ALIMENTI EVITATI
========================= */
$alimenti = $conn->query("
    SELECT a.nome
    FROM alimenti a
    JOIN alimenti_sospesi aps ON a.id = aps.fk_alimenti
    WHERE aps.fk_visita = $id_visita
");

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettaglio Visita</title>
    <link rel="stylesheet" href="style.css">

    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        margin: 10px;
        color: #333;
    }

    h1 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    h2 {
        background-color: #3498db;
        color: white;
        padding: 6px 10px;
        border-radius: 5px;
        margin-top: 15px;
        font-size: 1em;
    }

    .top-links {
        text-align: center;
        margin-bottom: 15px;
    }

    .top-links a {
        background-color: #2ecc71;
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        margin: 3px;
        border-radius: 4px;
        font-size: 0.9em;
    }

    .top-links a:hover {
        background-color: #27ae60;
    }

    /* LAYOUT DUE COLONNE */
    .container {
        display: flex;
        gap: 15px;
    }

    .column {
        flex: 1;
        background-color: white;
        padding: 10px 15px;
        border-radius: 6px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.1);
        max-height: 85vh;
        overflow-y: auto;
    }

    /* CARD COMPATTE PER CAMPI MULTI-LINEA */
    .card p, .card li {
        margin: 4px 0;
        font-size: 0.9em;
        line-height: 1.3em;
    }

    ul {
        list-style-type: disc;
        padding-left: 20px;
        margin: 0;
    }

    ul li {
        background-color: #ecf0f1;
        border-radius: 4px;
        padding: 4px 8px;
        margin-bottom: 3px;
    }

    /* SCROLL PER LISTE LUNGHE */
    .scrollable {
        max-height: 200px;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* TITOLO SEZIONE PIU' COMPATTO */
    h2 {
        font-size: 0.95em;
        padding: 5px 8px;
    }

    /* LABEL IN LINEA */
    .card p strong {
        width: 120px;
        display: inline-block;
    }

</style>
</head>
<body>

<h1>Dettaglio Visita</h1>

<div class="top-links">
    <a href="scheda_paziente.php?id=<?php echo $visita['fk_paziente']; ?>">Torna al paziente</a>
</div>

<h2>Paziente</h2>
<p><?php echo htmlspecialchars($visita['nome'] . " " . $visita['cognome']); ?></p>

<h2>Informazioni generali</h2>
<p><strong>Livello stress:</strong> <?php echo $visita['livello_stress']; ?></p>
<p><strong>Alimentazione:</strong> <?php echo htmlspecialchars($visita['alimentazione']); ?></p>

<h2>Sonno</h2>
<p>Ore: <?php echo $sonno['ore'] ?? ''; ?></p>
<p>Risvegli: <?php echo $sonno['risvegli'] ?? ''; ?></p>
<p>Difficoltà: <?php echo $sonno['difficolta'] ?? ''; ?></p>
<p>Qualità: <?php echo $sonno['qualita'] ?? ''; ?></p>

<h2>Stato psico-fisico</h2>
<p>Ansia: <?php echo $stato['ansia'] ?? ''; ?></p>
<p>Umore: <?php echo $stato['umore'] ?? ''; ?></p>
<p>Motivazione: <?php echo $stato['motivazione'] ?? ''; ?></p>
<p>Concentrazione: <?php echo $stato['concentrazione'] ?? ''; ?></p>

<h2>Attività fisica</h2>
<ul>
<?php while($row = $attivita->fetch_assoc()): ?>
    <li>
        <strong><?php echo htmlspecialchars($row['nome']); ?></strong><br>
        <?php echo htmlspecialchars($row['descrizione']); ?><br>
        <em><?php echo htmlspecialchars($row['note']); ?></em>
    </li>
<?php endwhile; ?>
</ul>

<h2>Domande</h2>
<ul>
<?php while($row = $domande->fetch_assoc()): ?>
    <li>
        <strong><?php echo htmlspecialchars($row['domanda']); ?></strong><br>
        Risposta: <?php echo htmlspecialchars($row['risposta']); ?>
    </li>
<?php endwhile; ?>
</ul>

<h2>Osservazioni</h2>
<ul>
<?php while($row = $osservazioni->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['osservazione']); ?></li>
<?php endwhile; ?>
</ul>

<h2>Farmaci</h2>
<ul>
<?php while($row = $farmaci->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['nome']); ?></li>
<?php endwhile; ?>
</ul>

<h2>Integratori</h2>
<ul>
<?php while($row = $integratori->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['nome']); ?></li>
<?php endwhile; ?>
</ul>

<h2>Supporti</h2>
<ul>
<?php while($row = $supporti->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['nome']); ?></li>
<?php endwhile; ?>
</ul>

<h2>Terapie</h2>
<ul>
<?php while($row = $terapie->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['nome']); ?></li>
<?php endwhile; ?>
</ul>

<h2>Alimenti evitati</h2>
<ul>
<?php while($row = $alimenti->fetch_assoc()): ?>
    <li><?php echo htmlspecialchars($row['nome']); ?></li>
<?php endwhile; ?>
</ul>

</body>
</html>

<?php $conn->close(); ?>