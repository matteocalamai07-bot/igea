<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID anamnesi non valido");
}

$id_anamnesi = intval($_GET['id']);

/* ANAMNESI */
$res = $conn->query("SELECT * FROM anamnesi WHERE id = $id_anamnesi");
$a = $res->fetch_assoc();

if (!$a) {
    die("Anamnesi non trovata");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Visualizza Anamnesi</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Anamnesi del <?= htmlspecialchars($a['data']) ?></h1>

<div style="margin-bottom:20px;">
    <a href="scheda_paziente.php?id=<?= $a['fk_paziente'] ?>" style="padding:8px 12px; background:#3498db; color:white; text-decoration:none; border-radius:5px;">Torna alla scheda paziente</a>
</div>

<div style="margin-bottom:20px; padding:10px; border:1px solid #ddd; border-radius:8px;">

<p><b>Allergie:</b> <?= htmlspecialchars($a['allergie']) ?></p>
<p><b>Dettagli allergie:</b> <?= htmlspecialchars($a['dettagli_allergie']) ?></p>

<p><b>Fumo:</b> <?= htmlspecialchars($a['fumo']) ?></p>
<p><b>Dettagli fumo:</b> <?= htmlspecialchars($a['dettagli_fumo']) ?></p>

<p><b>Alcol:</b> <?= htmlspecialchars($a['alcol']) ?></p>
<p><b>Dettagli alcol:</b> <?= htmlspecialchars($a['dettagli_alcol']) ?></p>

<p><b>Patologie:</b> <?= htmlspecialchars($a['patologie']) ?></p>
<p><b>Dettagli patologie:</b> <?= htmlspecialchars($a['dettagli_patologie']) ?></p>

<p><b>Interventi:</b> <?= htmlspecialchars($a['interventi']) ?></p>
<p><b>Dettagli interventi:</b> <?= htmlspecialchars($a['dettagli_interventi']) ?></p>

<p><b>Esami:</b> <?= htmlspecialchars($a['esami']) ?></p>
<p><b>Dettagli esami:</b> <?= htmlspecialchars($a['dettagli_esami']) ?></p>

<!-- BOTTONE ELIMINA -->
<button
    style="background:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer;"
    onclick="confermaEliminazione('elimina_anamnesi.php?id=<?= $a['id'] ?>')">
    Elimina anamnesi
</button>

</div>

<!-- SCRIPT CONFERMA ELIMINAZIONE -->
 <script>
function confermaEliminazione(url){
    if(confirm("Sei sicuro di voler eliminare questa anamnesi?")){
        window.location.href = url;
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>