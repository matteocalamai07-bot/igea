<?php
session_start();
// Connessione database
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// SE IL FORM È STATO INVIATO 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $nome = $_POST["nome"];
    

    $sql = "INSERT INTO alimenti (nome) VALUES ('$nome')";
 
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
        <title>Igea - Inserimento Alimento</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body> 
        <h1>Igea - Nuovo Alimento</h1>
        <br>
        <div class="top-links">
            <a href="alimenti.php" class="btn-top">Torna Indietro</a>
            <a href="index.php" class="btn-top">Torna alla Home</a>
        </div>
        <form method="POST" action=""> 
            <div>
                Nome Alimento:
                <input type="text" name="nome" required>
            </div>
            <br>
            <input type="submit" value="Aggiungi Alimento">
        </form> 
    </body>
</html>