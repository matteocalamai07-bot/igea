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
<title>Scheda Paziente</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    font-family: Arial;
    background: #f4f6f9;
    margin: 20px;
}

h1 {
    text-align: center;
}

/* LAYOUT */
.layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

/* CARD */
.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* BOTTONI */
.btn-top {
    padding: 8px 12px;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 5px;
}

/* EDIT */
.editable {
    cursor: pointer;
    border-bottom: 1px dashed #ccc;
    display: inline-block;
    min-width: 100px;
    min-height: 1em;
}

/* VISITE */
.visita-item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
}

.visita-item:hover {
    background: #ecf0f1;
}
</style>

</head>
<body>

<h1>Igea - Scheda Paziente</h1>

<div style="margin-bottom:20px;">
    <a href="pazienti.php" class="btn-top">Lista pazienti</a>
    <a href="index.php" class="btn-top">Home</a>
    <a href="aggiungi_anamnesi.php?id=<?php echo $id_paziente; ?>" class="btn-top">Nuova Anamnesi</a>
    <a href="nuova_visita.php?id=<?php echo $id_paziente; ?>" class="btn-top">Nuova Visita</a>
</div>

<div class="layout">

<!-- SINISTRA -->
<div>

<div class="card">
<h2>Anagrafica</h2>

<p><b>Nome:</b>
<span class="editable" data-field="nome" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['nome']) ?>
</span></p>

<p><b>Cognome:</b>
<span class="editable" data-field="cognome" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['cognome']) ?>
</span></p>

<p><b>Data di nascita:</b>
<span class="editable" data-field="datanascita" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['datanascita']) ?>
</span></p>

<p><b>Città:</b>
<span class="editable" data-field="citta" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['citta']) ?>
</span></p>

<p><b>Indirizzo:</b>
<span class="editable" data-field="indirizzo" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['indirizzo']) ?>
</span></p>

<p><b>Civico:</b>
<span class="editable" data-field="civico" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['civico']) ?>
</span></p>

<p><b>Professione:</b>
<span class="editable" data-field="professione" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['professione']) ?>
</span></p>

<p><b>Telefono:</b>
<span class="editable" data-field="telefono" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['telefono']) ?>
</span></p>

<p><b>Email:</b>
<span class="editable" data-field="email" data-id="<?= $paziente['id'] ?>">
<?= htmlspecialchars($paziente['email']) ?>
</span></p>

</div>

<div class="card">
<h2>Anamnesi</h2>

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
            <div class='visita-item' onclick=\"window.location='visualizza_anamnesi.php?id={$a['id']}'\">
                <b>Anamnesi N.{$numeroAnamnesi} del {$a['data']}</b>
            </div>";
            $numeroAnamnesi++;
        }
    } else {
        echo "<p>Nessuna anamnesi presente</p>";
    }
?>

</div>

</div>

<!-- DESTRA -->
<div>

<div class="card">
<h2>Storico Visite</h2>

<?php
$visite = $conn->query("
    SELECT data, id
    FROM visita
    WHERE fk_paziente = $id_paziente
    ORDER BY id ASC
");

if ($visite->num_rows > 0) {
    while($v = $visite->fetch_assoc()){
        $numVisita = 1;
        echo "
        <div class='visita-item' onclick=\"window.location='visite.php?id={$v['id']}'\">
            <b>Visita N.{$numVisita} del {$v['data']}</b>
        </div>";
        $numVisita++;
    }
} else {
    echo "<p>Nessuna visita presente</p>";
}
?>

</div>

</div>

</div>

<!-- SCRIPT EDIT INLINE -->
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