<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $field = $_POST['field'];
    $value = trim($_POST['value']);

    // Allowed fields
    $allowed_fields = ['nome', 'cognome', 'datanascita', 'citta', 'indirizzo', 'civico', 'professione', 'email', 'telefono'];

    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['success' => false, 'message' => 'Campo non valido']);
        exit;
    }

    // Basic validation
    if (empty($value) && in_array($field, ['nome', 'cognome', 'datanascita', 'citta', 'indirizzo', 'civico', 'email', 'telefono'])) {
        echo json_encode(['success' => false, 'message' => 'Il campo non può essere vuoto']);
        exit;
    }

    if ($field == 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email non valida']);
        exit;
    }

    if ($field == 'telefono' && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Telefono non valido']);
        exit;
    }

    if ($field == 'datanascita' && strtotime($value) > time()) {
        echo json_encode(['success' => false, 'message' => 'Data di nascita non valida']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE paziente SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aggiornato con successo']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore nell\'aggiornamento']);
    }

    $stmt->close();
}

$conn->close();
?>