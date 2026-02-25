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
        <title>Igea - Terapie</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1>Igea - Terapie/Farmaci</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
            </nav>
        </header>

        <div class="azioni-rapide">
            <label>Aggiungi un nuovo elemento:</label>
            <a href="nuova_terapia.php">+ Nuova Terapia/Farmaco</a>
        </div>

        <div class="section">
            <h2>Farmaci:</h2>

            <table>
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM farmaci";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='elimina_farmaco.php?id=".$row['id']."' 
                                onclick=\"return confirm('Sei sicuro di voler eliminare questo elemento?');\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>Integratori:</h2>

            <table>
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM integratori";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='elimina_integratore.php?id=".$row['id']."' 
                                onclick=\"return confirm('Sei sicuro di voler eliminare questo elemento?');\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>Supporti:</h2>

            <table>
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM supporti";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='elimina_supporto.php?id=".$row['id']."' 
                                onclick=\"return confirm('Sei sicuro di voler eliminare questo elemento?');\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>Terapie:</h2>

            <table>
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM terapie";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='elimina_terapia.php?id=".$row['id']."' 
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