<?php
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$query = "DELETE FROM supporti WHERE id = $id";
$conn->query($query);

$conn->close();

header("Location: farmaci.php");
exit;
?>
