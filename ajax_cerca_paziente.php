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
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {

        $nomeCompleto = htmlspecialchars($row['nome'] . " " . $row['cognome']);

        echo "<li onclick=\"document.getElementById('searchPaziente').value='$nomeCompleto';
                           document.getElementById('risultatiRicerca').innerHTML='';\">
                <a href='scheda_paziente.php?id={$row['id']}' 
                   style='text-decoration:none; color:black;'>
                   $nomeCompleto
                </a>
              </li>";
    }
    echo "</ul>";
} else {
    echo "<div style='padding:6px;'>Nessun paziente trovato</div>";
}

$conn->close();
?>
