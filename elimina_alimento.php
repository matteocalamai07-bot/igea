<?php
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$id = intval($_GET['id']);

$query = "DELETE FROM alimenti WHERE id = $id";
$conn->query($query);

$conn->close();

header("Location: alimenti.php");
exit;
?>
