<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id = intval($_GET['id']);

// Recupera l'ID del paziente prima di eliminare l'anamnesi
$query = "SELECT fk_paziente FROM anamnesi WHERE id = $id";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$paziente_id = $row['fk_paziente'];

// Elimina l'anamnesi
$delete_query = "DELETE FROM anamnesi WHERE id = $id";
$conn->query($delete_query);

$conn->close();

header("Location: scheda_paziente.php?id=" . $paziente_id);
exit;
?>
