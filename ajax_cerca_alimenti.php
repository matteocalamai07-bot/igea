<?php

$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    exit;
}

$testo = trim($_GET['q'] ?? '');

if ($testo === '') {
    exit;
}

/* QUERY RICERCA */

$stmt = $conn->prepare("
    SELECT id, nome
    FROM alimenti
    WHERE nome LIKE CONCAT('%', ?, '%')
    ORDER BY nome
    LIMIT 10
");

$stmt->bind_param("s", $testo);
$stmt->execute();
$result = $stmt->get_result();


/* OUTPUT */

if ($result->num_rows > 0) {

    echo "<ul style='list-style:none; margin:0; padding:0; border:1px solid #ccc; max-width:300px;'>";

    while ($row = $result->fetch_assoc()) {

        echo "<li style='padding:6px; border-bottom:1px solid #eee; cursor:pointer;'>";

        echo "<a href='#'
                onclick=\"confermaEliminazione('elimina_alimento.php?id=".$row['id']."'); return false;\"
                style='text-decoration:none;color:black;display:block;'>";

        echo htmlspecialchars($row['nome']);

        echo "</a>";

        echo "</li>";
    }

    echo "</ul>";

} else {

    echo "<div style='padding:6px;'>Nessun alimento trovato</div>";

}

$conn->close();

?>