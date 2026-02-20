<?php
    session_start();

    $conn = new mysqli("localhost", "root", "", "terranova");

    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Pazienti</title>
    </head>
    <body>
        <header>
            <h1>Igea - Gestione Pazienti</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
            </nav>
        </header>

        <div class="azioni-rapide">
            <label>Aggiungi un nuovo paziente:</label>
            <a href="nuovo_paziente.php">+ Nuovo Paziente</a>
        </div>

        <div class="ricerca-paziente">
            <label>Ricerca Paziente:</label>
            <!-- Implementazione futura: campo di ricerca per nome, cognome -->
        </div>

        <div class="section">
            <h2>Pazienti:</h2>

            <table>
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Data di Nascita</th>
                    <th>Elimina</th>
                </tr>

                <?php
                    $query = "SELECT * FROM paziente";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['cognome']."</td>";
                        echo "<td>".$row['datanascita']."</td>";
                        echo "<td><a href='elimina_paziente.php?id=".$row['id']."' onclick=\"return confirm('Sei sicuro di voler eliminare questo paziente?');\">Elimina</a></td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

    </body>
</html>

<?php $conn->close(); ?>