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
        <title>Igea - Alimenti</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1>Igea - Alimenti</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </header>

        <div class="azioni-rapide">
            <label>Aggiungi un nuovo alimento:</label>
            <a href="nuovo_alimento.php">+ Nuovo Alimento</a>
        </div>

        <div class="section">
            <h2>Alimenti:</h2>

            <table border="1">
                <tr>
                    <th>Nome</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM alimenti";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>
                                <a href='elimina_alimento.php?id=".$row['id']."' 
                                onclick=\"return confirm('Sei sicuro di voler eliminare questo elemento?');\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>
    </body>
</html>

<?php $conn->close(); ?>