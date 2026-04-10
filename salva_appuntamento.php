<?php
$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$form_action = $_POST['form_action'] ?? 'save';
$appointment_id = intval($_POST['appointment_id'] ?? 0);
$data_appuntamento = $_POST['data_appuntamento'] ?? '';
$titolo = trim($_POST['titolo'] ?? '');
$ora_inizio = $_POST['ora_inizio'] ?? '';
$ora_fine = $_POST['ora_fine'] ?? '';

$errors = [];

if ($form_action === 'delete') {
    if ($appointment_id <= 0) {
        $errors[] = 'Appuntamento non valido.';
    }
} else {
    if (!$data_appuntamento || !DateTime::createFromFormat('Y-m-d', $data_appuntamento)) {
        $errors[] = 'Data appuntamento non valida.';
    }

    if (!$titolo) {
        $errors[] = 'Titolo obbligatorio.';
    }

    if (!$ora_inizio || !DateTime::createFromFormat('H:i', $ora_inizio)) {
        $errors[] = 'Orario di inizio non valido.';
    }

    if (!$ora_fine || !DateTime::createFromFormat('H:i', $ora_fine)) {
        $errors[] = 'Orario di fine non valido.';
    }

    if (empty($errors)) {
        if (strtotime($ora_fine) <= strtotime($ora_inizio)) {
            $errors[] = 'L\'ora di fine deve essere successiva all\'ora di inizio.';
        }
    }
}

if (!empty($errors)) {
    echo '<h1>Errore</h1>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '<p><a href="index.php">Torna alla dashboard</a></p>';
    exit;
}

if ($form_action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM appuntamenti WHERE id = ?");
    $stmt->bind_param('i', $appointment_id);
    if (!$stmt->execute()) {
        die('Errore eliminazione appuntamento: ' . $stmt->error);
    }
    $stmt->close();
    $conn->close();
    header('Location: index.php');
    exit;
}

if ($form_action === 'edit' && $appointment_id > 0) {
    $stmt = $conn->prepare("UPDATE appuntamenti SET data_appuntamento = ?, titolo = ?, ora_inizio = ?, ora_fine = ? WHERE id = ?");
    $stmt->bind_param('ssssi', $data_appuntamento, $titolo, $ora_inizio, $ora_fine, $appointment_id);
} else {
    $stmt = $conn->prepare("INSERT INTO appuntamenti (data_appuntamento, titolo, ora_inizio, ora_fine) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $data_appuntamento, $titolo, $ora_inizio, $ora_fine);
}

if (!$stmt->execute()) {
    die('Errore salvataggio appuntamento: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: index.php');
exit;
