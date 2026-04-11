<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID paziente non valido");
}

$id_paziente = intval($_GET['id']);

/* PAZIENTE */
$res = $conn->query("SELECT * FROM paziente WHERE id = $id_paziente");
$paziente = $res->fetch_assoc();

if (!$paziente) {
    die("Paziente non trovato");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Igea - Scheda Paziente</title>
<link rel="stylesheet" href="style.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .editable {
        cursor: pointer;
        border-bottom: 1px dashed var(--primary-color);
        display: inline-block;
        padding: 2px 5px;
    }

    .editable input {
        border: 1px solid var(--primary-color);
        padding: 4px 8px;
        border-radius: 3px;
        font-family: Arial, sans-serif;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .page-header h1 {
        margin: 0;
    }

    .button-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .form-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-left, .form-right {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 8px;
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
        min-width: 120px;
    }

    .info-value {
        color: var(--text-muted);
        flex: 1;
    }

    .visita-item {
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        cursor: pointer;
        margin-bottom: 10px;
        transition: background-color 0.2s;
        background-color: var(--bg-card);
    }

    .visita-item:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .visita-item:last-child {
        margin-bottom: 0;
    }

    .empty-message {
        color: var(--text-muted);
        font-size: 0.95rem;
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
            <h1>Scheda Paziente</h1>
            <div class="button-group">
                <a href="pazienti.php" class="btn-azione">← Lista Pazienti</a>
                <a href="index.php" class="btn-azione">Home</a>
                <a href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>" class="btn-azione">Nuova Anamnesi</a>
                <a href="nuova_visita.php?id=<?php echo $id_paziente; ?>" class="btn-azione">Nuova Visita</a>
            </div>
        </div>

        <div class="form-container">
            <div class="form-left">
                <div class="form-blocco">
                    <h3>Anagrafica</h3>
                    <div class="info-row">
                        <span class="info-label">Nome:</span>
                        <span class="info-value editable" data-field="nome" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['nome']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cognome:</span>
                        <span class="info-value editable" data-field="cognome" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['cognome']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Data di nascita:</span>
                        <span class="info-value editable" data-field="datanascita" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['datanascita']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Professione:</span>
                        <span class="info-value editable" data-field="professione" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['professione']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Contatti</h3>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value editable" data-field="email" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Telefono:</span>
                        <span class="info-value editable" data-field="telefono" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['telefono']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Anamnesi</h3>
                    <?php
                        $anamnesi = $conn->query("
                            SELECT data, id
                            FROM anamnesi
                            WHERE fk_paziente = $id_paziente
                            ORDER BY id ASC
                        ");
                        
                        if ($anamnesi->num_rows > 0) {
                            $numeroAnamnesi = 1;
                            while($a = $anamnesi->fetch_assoc()){
                                echo "
                                <div class='visita-item' onclick=\"window.location='visualizza_anamnesi.php?id={$a['id']}'\" style='margin-bottom: 10px;'>
                                    <b>Anamnesi N.{$numeroAnamnesi} del {$a['data']}</b>
                                </div>";
                                $numeroAnamnesi++;
                            }
                        } else {
                            echo "<p class='empty-message'>Nessuna anamnesi presente</p>";
                        }
                    ?>
                </div>
            </div>

            <div class="form-right">
                <div class="form-blocco">
                    <h3>Indirizzo</h3>
                    <div class="info-row">
                        <span class="info-label">Città:</span>
                        <span class="info-value editable" data-field="citta" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['citta']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Indirizzo:</span>
                        <span class="info-value editable" data-field="indirizzo" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['indirizzo']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Civico:</span>
                        <span class="info-value editable" data-field="civico" data-id="<?= $paziente['id'] ?>"><?= htmlspecialchars($paziente['civico']) ?></span>
                    </div>
                </div>

                <div class="form-blocco">
                    <h3>Storico Visite</h3>
                    <?php
                    $visite = $conn->query("
                        SELECT data, id
                        FROM visita
                        WHERE fk_paziente = $id_paziente
                        ORDER BY id ASC
                    ");

                    if ($visite->num_rows > 0) {
                        $numVisita = 1;
                        while($v = $visite->fetch_assoc()){
                            echo "
                            <div class='visita-item' onclick=\"window.location='visite.php?id={$v['id']}'\" style='margin-bottom: 10px;'>
                                <b>Visita N.{$numVisita} del {$v['data']}</b>
                            </div>";
                            $numVisita++;
                        }
                    } else {
                        echo "<p class='empty-message'>Nessuna visita presente</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    $('.editable').click(function(){
        let span = $(this);
        let val = span.text();
        let field = span.data('field');
        let id = span.data('id');

        let input = $('<input type="text">').val(val);
        span.html(input);
        input.focus();

        input.blur(function(){
            let newVal = $(this).val();

            $.post('update_paziente.php',{
                id:id,
                field:field,
                value:newVal
            },function(){
                span.text(newVal);
            });
        });
    });
    </script>

</body>
</html>

<?php $conn->close(); ?>