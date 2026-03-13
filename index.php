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
    </head>
    <body>
        
        <aside class="sidebar">
            <h1>Igea</h1>
            <nav>
                <a href="index.php"> Home</a>
                <a href="pazienti.php"> Pazienti</a>
                <a href="farmaci.php"> Terapie</a>
                <a href="alimenti.php"> Alimenti</a>
            </nav>
        </aside>

        <main class="main-content">

            <h1>Dashboard</h1>

            <div class="azioni-rapide">
                <div class="azioni-row">
                    
                    <div>
                        <label>Azioni rapide</label>
                        <a href="nuovo_paziente.php" class="btn-azione">+ Nuovo Paziente</a>
                    </div>

                    <div class="ricerca-box">
                        <h2>Cerca Paziente</h2>
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

            <div class="section">
                <h2>Attività Recenti</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Paziente</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            echo "<tr>"; // <-- Aggiunto per correggere l'HTML
                            echo "<td>".$row['data']."</td>";
                            echo "<td>".$row['nome']." ".$row['cognome']."</td>";
                            echo "<td>".substr($row['osservazioni'], 0, 100)."...</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>

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
