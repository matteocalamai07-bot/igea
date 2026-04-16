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
    $stmt1 = $conn->prepare("DELETE FROM integratori_prescritti WHERE fk_integratori = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // 2. Elimina l'integratore
    $stmt2 = $conn->prepare("DELETE FROM integratori WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // Conferma operazioni
    $conn->commit();

} catch (Exception $e) {

    // Se errore → annulla tutto
    $conn->rollback();
    die("Errore: " . $e->getMessage());
}

$conn->close();

// Redirect corretto
header("Location: farmaci.php");
exit;
?>