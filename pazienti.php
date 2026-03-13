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
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <aside class="sidebar">
        <h1>Igea</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="pazienti.php">Pazienti</a>
            <a href="farmaci.php">Terapie</a>
            <a href="alimenti.php">Alimenti</a>
        </nav>
    </aside>

    <main class="main-content">

        <h1>Gestione Pazienti</h1>

        <div class="azioni-rapide">
            <div class="azioni-row">
                <div>
                    <label>Aggiungi un nuovo paziente</label>
                    <a href="nuovo_paziente.php" class="btn-azione">+ Nuovo Paziente</a>
                </div>

                <div class="ricerca-box">
                    <h2>Ricerca Paziente</h2>
                    <input
                        type="text"
                        id="searchPaziente"
                        placeholder="Digita nome o cognome..."
                        autocomplete="off"
                    >
                    <div id="risultatiRicerca"></div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Elenco Pazienti</h2>

            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Data di Nascita</th>
                        <th>Scheda Paziente</th>
                        <th>Anamnesi</th>
                        <th>Elimina</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT * FROM paziente ORDER BY cognome";
                $result = $conn->query($query);

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['nome']}</td>";
                    echo "<td>{$row['cognome']}</td>";
                    echo "<td>{$row['datanascita']}</td>";
                    echo "<td>
                            <a href='scheda_paziente.php?id={$row['id']}' style='font-weight:600; text-decoration:none; color:#4f46e5;'>
                            Visualizza
                            </a>
                        </td>";
                    echo "<td>
                            <a href='aggiungi_anamnesi.php?id={$row['id']}' style='font-weight:600; text-decoration:none; color:#0ea5e9;'>
                            Aggiungi/Modifica
                            </a>
                        </td>";
                    echo "<td>
                            <a href='#' onclick=\"confermaEliminazione('elimina_paziente.php?id=".$row['id']."'); return false;\">
                            Elimina
                            </a>
                            </td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    </main>

    <div class="modal-overlay" id="confirmModal">
        <div class="modal">
            <h3>Conferma eliminazione</h3>
            <p>Sei sicuro di voler eliminare questo paziente?</p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="chiudiModal()">Annulla</button>
                <button class="btn-delete" id="confirmDelete">Elimina</button>
            </div>
        </div>
    </div>

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

    /* POPUP ELIMINAZIONE */
    let deleteUrl = "";

    function confermaEliminazione(url){
        deleteUrl = url;
        document.getElementById("confirmModal").style.display = "flex";
    }

    function chiudiModal(){
        document.getElementById("confirmModal").style.display = "none";
    }

    document.getElementById("confirmDelete").onclick = function(){
        window.location.href = deleteUrl;
    };
    </script>

</body>
</html>
<?php $conn->close(); ?>
