<?php
session_start();

// Verifica se è stato passato un ID visita
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['errore'] = 'ID visita non valido';
    header('Location: visite.php');
    exit();
}

// Connessione al database
$conn = new mysqli('localhost', 'root', '', 'terranova');

if ($conn->connect_error) {
    $_SESSION['errore'] = 'Errore di connessione al database';
    $redirectId = isset($_GET['paziente_id']) && is_numeric($_GET['paziente_id']) ? intval($_GET['paziente_id']) : '';
    header('Location: scheda_paziente.php?id=' . $redirectId);
    exit();
}

$visita_id = intval($_GET['id']);
$paziente_id = isset($_GET['paziente_id']) && is_numeric($_GET['paziente_id']) ? intval($_GET['paziente_id']) : null;

try {
    // Ottieni il paziente associato alla visita
    $sql_check = "SELECT fk_paziente FROM visita WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    
    if (!$stmt_check) {
        throw new Exception('Errore nella query di verifica');
    }
    
    $stmt_check->bind_param('i', $visita_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Visita non trovata');
    }

    $row = $result->fetch_assoc();
    $fk_paziente = intval($row['fk_paziente']);
    if ($paziente_id !== null && $paziente_id !== $fk_paziente) {
        throw new Exception('Visita non appartiene a questo paziente');
    }
    $paziente_id = $fk_paziente;
    
    $stmt_check->close();
    
    // Disabilita i vincoli di chiave esterna
    $conn->query('SET FOREIGN_KEY_CHECKS = 0');
    
    // Lista di tutte le tabelle da cui eliminare i dati
    $tabelle = [
        'domande',
        'attivita_visita',
        'alimenti_sospesi',
        'osservazioni_finali',
        'sonno',
        '`stato_psico-fisico`',
        'farmaci_prescritti',
        'integratori_prescritti',
        'supporti_prescritti',
        'terapie_prescritte'
    ];
    
    // Elimina i dati da tutte le tabelle dipendenti
    foreach ($tabelle as $tabella) {
        $sql = "DELETE FROM $tabella WHERE fk_visita = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Errore nella query di eliminazione su ' . $tabella . ': ' . $conn->error);
        }
        $stmt->bind_param('i', $visita_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Elimina la visita
    $sql_delete = "DELETE FROM visita WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    
    if (!$stmt_delete) {
        throw new Exception('Errore nella query di eliminazione visita');
    }
    
    $stmt_delete->bind_param('i', $visita_id);
    
    if (!$stmt_delete->execute()) {
        throw new Exception('Errore nell\'eliminazione della visita: ' . $stmt_delete->error);
    }
    
    $stmt_delete->close();
    
    // Riabilita i vincoli di chiave esterna
    $conn->query('SET FOREIGN_KEY_CHECKS = 1');
    
    $_SESSION['messaggio'] = 'Visita e tutti i dati associati eliminati con successo';
    
} catch (Exception $e) {
    // Riabilita i vincoli in caso di errore
    $conn->query('SET FOREIGN_KEY_CHECKS = 1');
    $_SESSION['errore'] = $e->getMessage();
}

$conn->close();
header('Location: scheda_paziente.php?id=' . $paziente_id);
exit();
?>    
