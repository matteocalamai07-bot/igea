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
</head>
<body>

<main class="main-content">
    <div class="dashboard-header">
        <div>
            <h1>Dettaglio Visita</h1>
            <p style="color: var(--text-muted); margin-top: 5px;">
                Paziente: <?php echo htmlspecialchars($visita['nome'] . " " . $visita['cognome']); ?>
            </p>
        </div>
        <div class="header-actions">
            <a href="scheda_paziente.php?id=<?php echo $visita['fk_paziente']; ?>" class="btn-azione">Torna al paziente</a>
            <a href="genera_pdf.php?id=<?php echo $id_visita; ?>" class="btn-azione">Scarica PDF</a>
            <a href="index.php" class="btn-azione">Home</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="card-cruscotto">
            <h2>Informazioni generali</h2>
            <div class="detail-row"><span class="detail-label">Livello stress:</span><?php echo $visita['livello_stress']; ?></div>
            <div class="detail-row"><span class="detail-label">Alimentazione:</span><?php echo htmlspecialchars($visita['alimentazione']); ?></div>
        </section>

        <section class="card-cruscotto">
            <h2>Sonno</h2>
            <div class="detail-row"><span class="detail-label">Ore:</span><?php echo $sonno['ore'] ?? ''; ?></div>
            <div class="detail-row"><span class="detail-label">Risvegli:</span><?php echo $sonno['risvegli'] ?? ''; ?></div>
            <div class="detail-row"><span class="detail-label">Difficoltà:</span><?php echo $sonno['difficolta'] ?? ''; ?></div>
            <div class="detail-row"><span class="detail-label">Qualità:</span><?php echo $sonno['qualita'] ?? ''; ?></div>
        </section>
    </div>

    <div class="dashboard-grid">
        <section class="card-cruscotto">
            <h2>Stato psico-fisico</h2>
            <div class="detail-row"><span class="detail-label">Ansia:</span><?php echo $stato['ansia'] ?? ''; ?></div>
            <div class="detail-row"><span class="detail-label">Umore:</span><?php echo $stato['umore'] ?? ''; ?></div>
            <div class="detail-row"><span class="detail-label">Motivazione:</span><?php echo $stato['motivazione'] ?? ''; ?></div>
            <div class="detail-row"><span class="detail-label">Concentrazione:</span><?php echo $stato['concentrazione'] ?? ''; ?></div>
        </section>

        <section class="card-cruscotto">
            <h2>Attività fisica</h2>
            <?php if ($attivita->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $attivita->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($row['nome']); ?></strong>
                            <?php echo htmlspecialchars($row['descrizione']); ?><br>
                            <span class="text-muted"><?php echo htmlspecialchars($row['note']); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessuna attività registrata.</p>
            <?php endif; ?>
        </section>
    </div>

    <div class="section-group">
        <section class="card-cruscotto">
            <h2>Domande</h2>
            <?php if ($domande->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $domande->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($row['domanda']); ?></strong>
                            Risposta: <?php echo htmlspecialchars($row['risposta']); ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessuna domanda registrata.</p>
            <?php endif; ?>
        </section>

        <section class="card-cruscotto">
            <h2>Osservazioni</h2>
            <?php if ($osservazioni->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $osservazioni->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['osservazione']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessuna osservazione registrata.</p>
            <?php endif; ?>
        </section>
    </div>

    <div class="dashboard-grid">
        <section class="card-cruscotto">
            <h2>Farmaci</h2>
            <?php if ($farmaci->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $farmaci->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['nome']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessun farmaco registrato.</p>
            <?php endif; ?>
        </section>
        <section class="card-cruscotto">
            <h2>Integratori</h2>
            <?php if ($integratori->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $integratori->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['nome']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessun integratore registrato.</p>
            <?php endif; ?>
        </section>
    </div>

    <div class="dashboard-grid">
        <section class="card-cruscotto">
            <h2>Supporti</h2>
            <?php if ($supporti->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $supporti->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['nome']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessun supporto registrato.</p>
            <?php endif; ?>
        </section>
        <section class="card-cruscotto">
            <h2>Terapie</h2>
            <?php if ($terapie->num_rows > 0): ?>
                <ul class="detail-list">
                    <?php while($row = $terapie->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['nome']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Nessuna terapia registrata.</p>
            <?php endif; ?>
        </section>
    </div>

    <section class="card-cruscotto">
        <h2>Alimenti evitati</h2>
        <?php if ($alimenti->num_rows > 0): ?>
            <ul class="detail-list">
                <?php while($row = $alimenti->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($row['nome']); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Nessun alimento evitato registrato.</p>
        <?php endif; ?>
    </section>
</main>

</body>
</html>

<?php $conn->close(); ?>