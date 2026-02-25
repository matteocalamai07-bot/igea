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

    <style>
        #risultatiRicerca {
            width: 300px;
            border: 1px solid #ccc;
            background: white;
            position: absolute;
            z-index: 1000;
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

<!--  RICERCA PAZIENTE -->
<div class="section">
    <h2>Ricerca Paziente</h2>

    <input
        type="text"
        id="searchPaziente"
        placeholder="Digita nome o cognome..."
        autocomplete="off"
    >

    <div id="risultatiRicerca"></div>
</div>

<!--  ELENCO PAZIENTI -->
<div class="section">
    <h2>Pazienti</h2>

    <table border="1">
        <tr>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Data di Nascita</th>
            <th>Scheda Paziente</th>
            <th>Elimina</th>
        </tr>

        <?php
        $query = "SELECT * FROM paziente ORDER BY cognome";
        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['nome']}</td>";
            echo "<td>{$row['cognome']}</td>";
            echo "<td>{$row['datanascita']}</td>";
            echo "<td>
                    <a href='scheda_paziente.php?id={$row['id']}'>
                    Visualizza
                    </a>
                  </td>";
            echo "<td>
                    <a href='elimina_paziente.php?id={$row['id']}'
                       onclick=\"return confirm('Sei sicuro di voler eliminare questo paziente?');\">
                       Elimina
                    </a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>
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
