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
        <title>Igea - Scheda Paziente</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1>Igea - Scheda Paziente</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
            </nav>
        </header>
        <div class="container">
            <h2>Dettagli Paziente</h2>

            
    </body>
</html>

<?php $conn->close(); ?>