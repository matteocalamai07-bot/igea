<?php
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$query = "DELETE FROM anamnesi WHERE fk_paziente = $id";
$conn->query($query);

$conn->close();

header("Location: scheda_paziente.php?id=" . $_GET['id']);
exit;
?>