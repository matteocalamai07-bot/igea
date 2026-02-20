<?php
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$query = "DELETE FROM paziente WHERE id = $id";
$conn->query($query);

$conn->close();

header("Location: pazienti.php");
exit;
?>
