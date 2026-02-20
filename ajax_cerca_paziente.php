<?php
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    exit;
}

$testo = $_GET['q'] ?? '';

if (strlen($testo) < 1) {
    exit;
}

$stmt = $conn->prepare("
    SELECT id, nome, cognome
    FROM paziente
    WHERE nome LIKE CONCAT('%', ?, '%')
       OR cognome LIKE CONCAT('%', ?, '%')
    ORDER BY cognome
    LIMIT 10
");

$stmt->bind_param("ss", $testo, $testo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul style='list-style:none; margin:0; padding:0;'>";
    while ($row = $result->fetch_assoc()) {
        echo "<li style='padding:6px; border-bottom:1px solid #eee; cursor:pointer'>";
        echo htmlspecialchars($row['nome'] . " " . $row['cognome']);
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<div style='padding:6px;'>Nessun paziente trovato</div>";
}

$conn->close();