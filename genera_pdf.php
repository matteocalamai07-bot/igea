<?php
$conn = new mysqli("localhost","root","","terranova");
if ($conn->connect_error) die("Errore connessione");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID non valido");
}

$id = intval($_GET['id']);

/* =========================
   QUERY DATI
========================= */
$visita = $conn->query("
SELECT v.*, p.nome, p.cognome
FROM visita v
JOIN paziente p ON v.fk_paziente=p.id
WHERE v.id=$id
")->fetch_assoc();

$sonno = $conn->query("SELECT * FROM sonno WHERE fk_visita=$id")->fetch_assoc();
$stato = $conn->query("SELECT * FROM `stato_psico-fisico` WHERE fk_visita=$id")->fetch_assoc();

$attivita = $conn->query("
    SELECT a.*
    FROM attivita_fisica a
    JOIN attivita_visita av ON a.id = av.fk_attivita
    WHERE av.fk_visita = $id
");

$domande = $conn->query("SELECT * FROM domande WHERE fk_visita=$id");
$osservazioni = $conn->query("SELECT * FROM osservazioni_finali WHERE fk_visita=$id");

$farmaci = $conn->query("
    SELECT f.nome 
    FROM farmaci f
    JOIN farmaci_prescritti fp ON f.id = fp.fk_farmaci
    WHERE fp.fk_visita = $id
");

$integratori = $conn->query("
    SELECT i.nome 
    FROM integratori i
    JOIN integratori_prescritti ip ON i.id = ip.fk_integratori
    WHERE ip.fk_visita = $id
");

$supporti = $conn->query("
    SELECT s.nome 
    FROM supporti s
    JOIN supporti_prescritti sp ON s.id = sp.fk_supporti
    WHERE sp.fk_visita = $id
");

$terapie = $conn->query("
    SELECT t.nome 
    FROM terapie t
    JOIN terapie_prescritte tp ON t.id = tp.fk_terapie
    WHERE tp.fk_visita = $id
");

$alimenti = $conn->query("
    SELECT a.nome
    FROM alimenti a
    JOIN alimenti_sospesi aps ON a.id = aps.fk_alimenti
    WHERE aps.fk_visita = $id
");

/* =========================
   GENERAZIONE HTML STAMPABILE
========================= */
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Referto Visita - <?php echo $visita['nome'] . " " . $visita['cognome']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #2c3e50;
            line-height: 1.6;
            background: white;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
        }

        .header {
            border-bottom: 3px solid #3498db;
            margin-bottom: 25px;
            padding-bottom: 15px;
            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }

        .title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .meta {
            color: #666;
            font-size: 12px;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section h2 {
            background: linear-gradient(135deg, #3498db, #0ea5e9);
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            margin-bottom: 12px;
            border-radius: 5px;
        }

        .box {
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 4px;
            background: #f9fafb;
        }

        .row {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .row:last-child {
            margin-bottom: 0;
        }

        .label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 120px;
        }

        .value {
            flex: 1;
            color: #555;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul li {
            padding: 10px;
            margin-bottom: 8px;
            background: white;
            border-left: 3px solid #3498db;
            border-radius: 3px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        @media print {
            body {
                padding: 0;
            }
            .container {
                max-width: 100%;
                margin: 0;
            }
            .section {
                page-break-inside: avoid;
            }
            .no-print {
                display: none;
            }
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .no-print button {
            background: linear-gradient(135deg, #3498db, #0ea5e9);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .no-print button:hover {
            transform: translateY(-2px);
        }

        .empty {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print">
        <button onclick="window.print()">🖨️ Stampa / Salva come PDF</button>
        <button onclick="window.history.back()" style="margin-left: 10px;">← Indietro</button>
    </div>

    <div class="header">
        <div class="logo">IGEA</div>
        <div class="title">Referto Visita Paziente</div>
        <div class="meta">Data: <?php echo date("d/m/Y H:i"); ?></div>
    </div>

    <div class="section">
        <h2>Dati Paziente</h2>
        <div class="box">
            <div class="row">
                <span class="label">Nome:</span>
                <span class="value"><?php echo htmlspecialchars($visita['nome'] . " " . $visita['cognome']); ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Valutazione Generale</h2>
        <div class="box">
            <div class="row">
                <span class="label">Livello Stress:</span>
                <span class="value"><?php echo htmlspecialchars($visita['livello_stress']); ?>/10</span>
            </div>
            <div class="row">
                <span class="label">Alimentazione:</span>
                <span class="value"><?php echo htmlspecialchars($visita['alimentazione']); ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Qualità del Sonno</h2>
        <div class="box">
            <div class="row">
                <span class="label">Ore di sonno:</span>
                <span class="value"><?php echo htmlspecialchars($sonno['ore'] ?? '-'); ?></span>
            </div>
            <div class="row">
                <span class="label">Risvegli notturni:</span>
                <span class="value"><?php echo htmlspecialchars($sonno['risvegli'] ?? '-'); ?></span>
            </div>
            <div class="row">
                <span class="label">Difficoltà ad addormentarsi:</span>
                <span class="value"><?php echo htmlspecialchars($sonno['difficolta'] ?? '-'); ?></span>
            </div>
            <div class="row">
                <span class="label">Qualità percepita:</span>
                <span class="value"><?php echo htmlspecialchars($sonno['qualita'] ?? '-'); ?></span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Stato Psico-Fisico</h2>
        <div class="box">
            <div class="row">
                <span class="label">Ansia:</span>
                <span class="value"><?php echo htmlspecialchars($stato['ansia'] ?? '-'); ?></span>
            </div>
            <div class="row">
                <span class="label">Umore:</span>
                <span class="value"><?php echo htmlspecialchars($stato['umore'] ?? '-'); ?></span>
            </div>
            <div class="row">
                <span class="label">Motivazione:</span>
                <span class="value"><?php echo htmlspecialchars($stato['motivazione'] ?? '-'); ?></span>
            </div>
            <div class="row">
                <span class="label">Concentrazione:</span>
                <span class="value"><?php echo htmlspecialchars($stato['concentrazione'] ?? '-'); ?></span>
            </div>
        </div>
    </div>

    <?php if ($domande->num_rows > 0): ?>
    <div class="section">
        <h2>Domande e Risposte</h2>
        <ul>
            <?php while($d = $domande->fetch_assoc()): ?>
                <li>
                    <strong><?php echo htmlspecialchars($d['domanda']); ?></strong><br>
                    <span style="color: #666;">Risposta: <?php echo htmlspecialchars($d['risposta']); ?></span>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($osservazioni->num_rows > 0): ?>
    <div class="section">
        <h2>Osservazioni Finali</h2>
        <ul>
            <?php while($o = $osservazioni->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($o['osservazione']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($attivita->num_rows > 0): ?>
    <div class="section">
        <h2>Attività Fisica</h2>
        <ul>
            <?php while($att = $attivita->fetch_assoc()): ?>
                <li>
                    <strong><?php echo htmlspecialchars($att['nome']); ?></strong><br>
                    <span style="color: #666;">
                        <?php if (!empty($att['descrizione'])): ?>
                            <?php echo htmlspecialchars($att['descrizione']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($att['note'])): ?>
                            Note: <?php echo htmlspecialchars($att['note']); ?>
                        <?php endif; ?>
                    </span>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($farmaci->num_rows > 0): ?>
    <div class="section">
        <h2>Farmaci Consigliati</h2>
        <ul>
            <?php while($f = $farmaci->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($f['nome']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($integratori->num_rows > 0): ?>
    <div class="section">
        <h2>Integratori Consigliati</h2>
        <ul>
            <?php while($int = $integratori->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($int['nome']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($supporti->num_rows > 0): ?>
    <div class="section">
        <h2>Supporti Consigliati</h2>
        <ul>
            <?php while($sup = $supporti->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($sup['nome']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($terapie->num_rows > 0): ?>
    <div class="section">
        <h2>Terapie Prescritte</h2>
        <ul>
            <?php while($ter = $terapie->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($ter['nome']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($alimenti->num_rows > 0): ?>
    <div class="section">
        <h2>Alimenti da Evitare</h2>
        <ul>
            <?php while($ali = $alimenti->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($ali['nome']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="footer">
        Referto generato automaticamente dal sistema IGEA - <?php echo date("d/m/Y H:i"); ?>
    </div>
</div>

</body>
</html>
