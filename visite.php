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

        .page-header p {
            margin: 4px 0 0 0;
            font-size: 0.9rem;
        }

        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-wrapper {
            flex: 1;
            overflow-y: auto;
        }

        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-left, .form-right {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-blocco {
            padding: 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-page);
            border-radius: 8px;
        }

        .form-blocco h3 {
            margin: 0 0 10px 0;
            font-size: 1rem;
            color: var(--text-main);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 6px;
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
            font-size: 0.9rem;
        }

        .info-value {
            color: var(--text-muted);
            text-align: right;
            flex: 1;
        }

        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .item-list li {
            margin-bottom: 8px;
            padding: 8px;
            background: var(--bg-card);
            border-radius: 4px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .item-list li:last-child {
            margin-bottom: 0;
        }

        .item-list strong {
            color: var(--text-main);
        }

        .empty-message {
            color: var(--text-muted);
            font-style: italic;
            font-size: 0.9rem;
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
            <div>
                <h1>Dettaglio Visita</h1>
                <p>Paziente: <?php echo htmlspecialchars($visita['nome'] . " " . $visita['cognome']); ?></p>
            </div>
            <div class="button-group">
                <a href="pazienti.php" class="btn-azione">← Pazienti</a>
                <a href="scheda_paziente.php?id=<?php echo $visita['fk_paziente']; ?>" class="btn-azione">← Scheda Paziente</a>
                <a href="genera_pdf.php?id=<?php echo $id_visita; ?>" class="btn-azione">📄 PDF</a>
                <a href="elimina_visita.php?id=<?php echo $id_visita; ?>" class="btn-azione btn-elimina" onclick="return confirm('Sei sicuro di voler eliminare questa visita?');">Elimina</a>
            </div>
        </div>

        <div class="form-wrapper">
            <div class="form-container">
                <div class="form-left">
                    <!-- INFORMAZIONI GENERALI -->
                    <div class="form-blocco">
                        <h3>Informazioni Generali</h3>
                        <div class="info-row">
                            <span class="info-label">Livello stress:</span>
                            <span class="info-value"><?php echo $visita['livello_stress']; ?>/10</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Alimentazione:</span>
                            <span class="info-value"><?php echo htmlspecialchars($visita['alimentazione']); ?></span>
                        </div>
                    </div>

                    <!-- SONNO -->
                    <div class="form-blocco">
                        <h3>Qualità del Sonno</h3>
                        <div class="info-row">
                            <span class="info-label">Ore:</span>
                            <span class="info-value"><?php echo $sonno['ore'] ?? '-'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Risvegli:</span>
                            <span class="info-value"><?php echo $sonno['risvegli'] ?? '-'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Difficoltà:</span>
                            <span class="info-value"><?php echo $sonno['difficolta'] ?? '-'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Qualità:</span>
                            <span class="info-value"><?php echo $sonno['qualita'] ?? '-'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="form-right">
                    <!-- STATO PSICO-FISICO -->
                    <div class="form-blocco">
                        <h3>Stato Psico-Fisico</h3>
                        <div class="info-row">
                            <span class="info-label">Ansia:</span>
                            <span class="info-value"><?php echo $stato['ansia'] ?? '-'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Umore:</span>
                            <span class="info-value"><?php echo $stato['umore'] ?? '-'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Motivazione:</span>
                            <span class="info-value"><?php echo $stato['motivazione'] ?? '-'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Concentrazione:</span>
                            <span class="info-value"><?php echo $stato['concentrazione'] ?? '-'; ?></span>
                        </div>
                    </div>

                    <!-- ATTIVITÀ FISICA -->
                    <div class="form-blocco">
                        <h3>Attività Fisica</h3>
                        <?php if ($attivita->num_rows > 0): ?>
                            <ul class="item-list">
                                <?php while($row = $attivita->fetch_assoc()): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($row['nome']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['descrizione']); ?></small>
                                        <?php if ($row['note']): ?><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($row['note']); ?></small><?php endif; ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-message">Nessuna attività registrata.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- DOMANDE E RISPOSTE -->
            <div class="form-container">
                <div class="form-blocco">
                    <h3>Domande e Risposte</h3>
                    <?php if ($domande->num_rows > 0): ?>
                        <ul class="item-list">
                            <?php while($row = $domande->fetch_assoc()): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($row['domanda']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($row['risposta']); ?></small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Nessuna domanda registrata.</p>
                    <?php endif; ?>
                </div>

                <div class="form-blocco">
                    <h3>Osservazioni Finali</h3>
                    <?php if ($osservazioni->num_rows > 0): ?>
                        <ul class="item-list">
                            <?php while($row = $osservazioni->fetch_assoc()): ?>
                                <li><?php echo htmlspecialchars($row['osservazione']); ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Nessuna osservazione registrata.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PRESCRIZIONI -->
            <div class="form-container">
                <div class="form-blocco">
                    <h3>Farmaci</h3>
                    <?php if ($farmaci->num_rows > 0): ?>
                        <ul class="item-list">
                            <?php while($row = $farmaci->fetch_assoc()): ?>
                                <li><?php echo htmlspecialchars($row['nome']); ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Nessun farmaco registrato.</p>
                    <?php endif; ?>
                </div>

                <div class="form-blocco">
                    <h3>Integratori</h3>
                    <?php if ($integratori->num_rows > 0): ?>
                        <ul class="item-list">
                            <?php while($row = $integratori->fetch_assoc()): ?>
                                <li><?php echo htmlspecialchars($row['nome']); ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Nessun integratore registrato.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-container">
                <div class="form-blocco">
                    <h3>Supporti</h3>
                    <?php if ($supporti->num_rows > 0): ?>
                        <ul class="item-list">
                            <?php while($row = $supporti->fetch_assoc()): ?>
                                <li><?php echo htmlspecialchars($row['nome']); ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Nessun supporto registrato.</p>
                    <?php endif; ?>
                </div>

                <div class="form-blocco">
                    <h3>Terapie</h3>
                    <?php if ($terapie->num_rows > 0): ?>
                        <ul class="item-list">
                            <?php while($row = $terapie->fetch_assoc()): ?>
                                <li><?php echo htmlspecialchars($row['nome']); ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty-message">Nessuna terapia registrata.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ALIMENTI EVITATI -->
            <div class="form-blocco">
                <h3>Alimenti da Evitare</h3>
                <?php if ($alimenti->num_rows > 0): ?>
                    <ul class="item-list">
                        <?php while($row = $alimenti->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($row['nome']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="empty-message">Nessun alimento evitato registrato.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>

<?php $conn->close(); ?>