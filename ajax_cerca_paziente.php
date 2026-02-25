<?php
$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    exit;
}

$testo = trim($_GET['q'] ?? '');
if ($testo === '') {
    exit;
}

/* 🔹 SPLIT PER SPAZI */
$parole = preg_split('/\s+/', $testo);

/* 🔹 UNA SOLA PAROLA */
if (count($parole) === 1) {

    $p = $parole[0];

    $stmt = $conn->prepare("
        SELECT id, nome, cognome
        FROM paziente
        WHERE nome LIKE CONCAT('%', ?, '%')
           OR cognome LIKE CONCAT('%', ?, '%')
        ORDER BY cognome
        LIMIT 10
    ");
    $stmt->bind_param("ss", $p, $p);

} else {

    /* 🔹 DUE PAROLE: nome+cognome OR cognome+nome */
    $p1 = $parole[0];
    $p2 = $parole[1];

    $stmt = $conn->prepare("
        SELECT id, nome, cognome
        FROM paziente
        WHERE (nome LIKE CONCAT('%', ?, '%') AND cognome LIKE CONCAT('%', ?, '%'))
           OR (nome LIKE CONCAT('%', ?, '%') AND cognome LIKE CONCAT('%', ?, '%'))
        ORDER BY cognome
        LIMIT 10
    ");
    $stmt->bind_param("ssss", $p1, $p2, $p2, $p1);
}

$stmt->execute();
$result = $stmt->get_result();

/* 🔹 OUTPUT */
if ($result->num_rows > 0) {
    echo "<ul style='list-style:none; margin:0; padding:0;'>";
    while ($row = $result->fetch_assoc()) {
        echo "<li style='padding:6px; border-bottom:1px solid #eee; cursor:pointer'>
            <a href='scheda_paziente.php?id={$row['id']}' 
            style='text-decoration:none; color:black; display:block;'>
                " . htmlspecialchars($row['nome'] . " " . $row['cognome']) . "
            </a>
        </li>";
    }
    echo "</ul>";
} else {
    echo "<div style='padding:6px;'>Nessun paziente trovato</div>";
}

$conn->close();