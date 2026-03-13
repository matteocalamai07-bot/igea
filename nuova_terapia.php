<?php
session_start();
// Connessione database
$conn = new mysqli("localhost", "root", "", "terranova");

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$success_msg = "";
$error_msg = "";

// SE IL FORM È STATO INVIATO 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $tipo = $_POST["tipo"]; 
    $nome = trim($_POST["nome"]);
    $descrizione = trim($_POST["descrizione"]);
    
    // Lista delle tabelle consentite per sicurezza
    $allowed_tables = ["farmaci", "integratori", "supporti", "terapie"];
    
    if (in_array($tipo, $allowed_tables)) {
        // Usa i prepared statements per sicurezza
        $stmt = $conn->prepare("INSERT INTO $tipo (nome, descrizione) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $descrizione);
        
        if ($stmt->execute()) { 
            $success_msg = "Dati inseriti correttamente!"; 
        } else { 
            $error_msg = "Errore nell'inserimento: " . $stmt->error; 
        }
        $stmt->close();
    } else {
        $error_msg = "Tipo di terapia non valido.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Inserimento Terapia</title>
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
            
            <h1>Nuova Terapia o Farmaco</h1>

            <?php if (!empty($success_msg)): ?>
                <p class="messaggio-php"><?php echo $success_msg; ?></p>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <ul class="error-list">
                    <li><?php echo $error_msg; ?></li>
                </ul>
            <?php endif; ?>

            <form method="POST" action=""> 
                <h2>Inserisci i Dati</h2>
                
                <div>
                    <label>Seleziona la categoria:</label>
                    <select name="tipo" required>
                        <option value="farmaci">Farmaco</option>
                        <option value="integratori">Integratore</option>
                        <option value="supporti">Supporto</option>
                        <option value="terapie">Terapia</option>
                    </select>
                </div> 
                
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                </div>
                
                <div>
                    <label>Descrizione:</label>
                    <input type="text" name="descrizione">
                </div>
                
                <button type="submit">Aggiungi</button>
            </form>

        </main>
    </body>
</html>
<?php $conn->close(); ?>
