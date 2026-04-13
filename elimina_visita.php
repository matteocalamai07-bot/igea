<?php
session_start();
// Connessione al database
$conn = new mysqli('localhost', 'root', '', 'igea');

if ($conn->connect_error) {
    die('Errore di connessione: ' . $conn->connect_error);
}

// Verifica se è stato passato un ID visita
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: visite.php');
    exit();
}

$visita_id = intval($_GET['id']);

// Elimina la visita
$sql = "DELETE FROM visite WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $visita_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    $_SESSION['messaggio'] = 'Visita eliminata con successo';
    header('Location: visite.php');
} else {
    $_SESSION['errore'] = 'Errore nell\'eliminazione della visita';
    header('Location: visite.php');
}

$stmt->close();
$conn->close();
?>