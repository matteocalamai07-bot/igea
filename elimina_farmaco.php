<?php
$conn = new mysqli("localhost", "root", "", "terranova");

// controllo connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// controllo ID
if (!isset($_GET['id'])) {
    die("ID non specificato");
}

$id = intval($_GET['id']);

// TRANSAZIONE (molto importante)
$conn->begin_transaction();

try {

    // 1. elimina i riferimenti nella tabella collegata
    $stmt1 = $conn->prepare("DELETE FROM farmaci_prescritti WHERE fk_farmaci = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // 2. elimina il farmaco
    $stmt2 = $conn->prepare("DELETE FROM farmaci WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // conferma tutto
    $conn->commit();

} catch (Exception $e) {

    // rollback se qualcosa va male
    $conn->rollback();
    die("Errore: " . $e->getMessage());
}

$conn->close();

// redirect
header("Location: farmaci.php");
exit;
?>