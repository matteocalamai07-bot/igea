<?php
session_start();
// Connessione database
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Home</title>
    </head>
    <body>
        <header>
            <h1>Igea - Gestionale Naturopata</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
            </nav>
        </header>

        <div class="container">
            <!-- AZIONI RAPIDE -->
            <div class="azioni-rapide">
                <label>Azioni rapide:</label><br>
                <a href="nuovo_paziente.php">+ Nuovo Paziente</a>
                <a href="nuova_visita.php">+ Nuova Visita</a>
            </div>

            <!-- ATTIVITÀ RECENTI -->
            <div class="section">
                <h2>Attività Recenti</h2>

                <table>
                    <tr>
                        <th>Data</th>
                        <th>Paziente</th>
                        <th>Note</th>
                    </tr>

                    <?php
                    $query = "
                        SELECT v.data, p.nome, p.cognome, GROUP_CONCAT(o.osservazione SEPARATOR '|') AS osservazioni
                        FROM paziente p INNER JOIN visita v ON p.id = v.fk_paziente
                        INNER JOIN osservazioni_finali o ON v.id = o.fk_visita
                        GROUP BY v.id
                        ORDER BY v.data DESC
                        LIMIT 5;
                    ";

                    //prende i risultati della query
                    $result = $conn->query($query);

                    //prende una riga alla volta da result finchè non finiscono
                    while ($row = $result->fetch_assoc()) {
                        echo "<td>".$row['data']."</td>";
                        echo "<td>".$row['nome']." ".$row['cognome']."</td>";
                        echo "<td>".substr($row['osservazioni'], 0, 100)."...</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </body>
</html>

<?php $conn->close(); ?>