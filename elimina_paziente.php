<?php
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id = intval($_GET['id']);

// Ottieni tutti gli ID delle visite del paziente
$visite_result = $conn->query("SELECT id FROM visita WHERE fk_paziente = $id");

// Elimina i dati associati a ogni visita
while ($visita = $visite_result->fetch_assoc()) {
    $visita_id = $visita['id'];
    $conn->query("DELETE FROM attivita_visita WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM farmaci_prescritti WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM integratori_prescritti WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM supporti_prescritti WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM alimenti_sospesi WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM domande WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM osservazioni_finali WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM sonno WHERE fk_visita = $visita_id");
    $conn->query("DELETE FROM `stato_psico-fisico` WHERE fk_visita = $visita_id");
}

// Elimina tutte le visite del paziente
$conn->query("DELETE FROM visita WHERE fk_paziente = $id");

// Elimina tutte le anamnesi del paziente
$conn->query("DELETE FROM anamnesi WHERE fk_paziente = $id");

// Elimina il paziente
$conn->query("DELETE FROM paziente WHERE id = $id");

$conn->close();

header("Location: pazienti.php");
exit;
?>