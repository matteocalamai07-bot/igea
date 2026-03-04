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
        <link rel="stylesheet" href="style.css">

        <style>
            #risultatiRicerca {
                width: 300px;
                border: 1px solid #ccc;
                background: white;
                max-height: 200px;
                overflow-y: auto;
            }

            #risultatiRicerca ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            #risultatiRicerca li {
                padding: 6px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
            }

            #risultatiRicerca li:hover {
                background: #f0f0f0;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Igea - Home</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </header>

        <div class="container">
            <!-- AZIONI RAPIDE -->
            <div class="azioni-rapide">
                <label>Azioni rapide:</label>

                <div class="azioni-row">
                    <a href="nuovo_paziente.php">+ Nuovo Paziente</a>

                    <div class="ricerca-box">
                        <input
                            type="text"
                            id="searchPaziente"
                            placeholder="Cerca paziente per nome o cognome..."
                            autocomplete="off"
                        >
                        <div id="risultatiRicerca"></div>
                    </div>
                </div>
            </div>

            <!-- ATTIVITÀ RECENTI -->
            <div class="section">
                <h2>Attività Recenti</h2>

                <table border="1">
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

        <!--  JAVASCRIPT AJAX -->
        <script>
        document.getElementById("searchPaziente").addEventListener("keyup", function () {

            let valore = this.value.trim();

            if (valore.length === 0) {
                document.getElementById("risultatiRicerca").innerHTML = "";
                return;
            }

            let xhr = new XMLHttpRequest();
            xhr.open("GET", "ajax_cerca_paziente.php?q=" + encodeURIComponent(valore), true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("risultatiRicerca").innerHTML = xhr.responseText;
                }
            };

            xhr.send();
        });
        </script>
    </body>
</html>

<?php $conn->close(); ?>