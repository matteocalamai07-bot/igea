<?php
session_start();
// Connessione database
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// SE IL FORM È STATO INVIATO 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $tipo = $_POST["tipo"]; 
    $nome = $_POST["nome"];
    $descrizione = $_POST["descrizione"];
    

    switch($tipo){
        case "farmaci":
            $sql = "INSERT INTO farmaci (nome, descrizione) VALUES ('$nome', '$descrizione')";
            break;
        case "integratori":
            $sql = "INSERT INTO integratori (nome, descrizione) VALUES ('$nome', '$descrizione')";
            break;
        case "supporti":
            $sql = "INSERT INTO supporti (nome, descrizione) VALUES ('$nome', '$descrizione')";
            break;
        case "terapie":
            $sql = "INSERT INTO terapie (nome, descrizione) VALUES ('$nome', '$descrizione')";
            break;
    }
 
    if ($conn->query($sql) === TRUE) { 
        echo "<p>Dati inseriti correttamente!</p>"; 
    } else { 
            echo "<p>Errore nell'inserimento: " . $conn->error . "</p>"; 
    } $conn->close(); 
}
?>

<!-- SE NON È STATO ANCORA INVIATO, MOSTRO IL FORM --> 
 <!DOCTYPE html>
 <html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Inserimento Terapia</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body> 
        <h1>Igea - Nuova Terapia/Farmaco</h1>
        <br>
        <div class="top-links">
            <a href="farmaci.php" class="btn-top">Torna Indietro</a>
            <a href="index.php" class="btn-top">Torna alla Home</a>
        </div>
        <form method="POST" action=""> 
            <div>
                Seleziona il tipo:
                <select name="tipo">
                    <option value="farmaci">farmaco</option>
                    <option value="integratori">integratore</option>
                    <option value="supporti">supporto</option>
                    <option value="terapie">terapia</option>
                </select>
            </div> 
            <div>
                Inserisci il nome:
                <input type="text" name="nome">
            </div>
            <div>
                Inserisci la descrizione:
                <input type="text" name="descrizione">
            </div>
            <div>
                Aggiungi:
                <input type="submit">
            </div>
        </form>
    </body>
 </html>