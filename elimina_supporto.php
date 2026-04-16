<?php
$conn = new mysqli("localhost", "root", "", "terranova");

// Controllo connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Controllo ID
if (!isset($_GET['id'])) {
    die("ID non specificato");
}

$id = intval($_GET['id']);

// Avvio transazione
$conn->begin_transaction();

try {

    // 1. Elimina riferimenti nella tabella collegata
    $stmt1 = $conn->prepare("DELETE FROM supporti_prescritti WHERE fk_supporti = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // 2. Elimina il supporto
    $stmt2 = $conn->prepare("DELETE FROM supporti WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // Conferma
    $conn->commit();

} catch (Exception $e) {

    // Rollback se errore
    $conn->rollback();
    die("Errore: " . $e->getMessage());
}

$conn->close();

// Redirect (come richiesto)
header("Location: farmaci.php");
exit;
?>