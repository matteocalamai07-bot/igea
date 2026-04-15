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
    $nome = trim($_POST["nome"]);
    
    if (!empty($nome)) {
        // Usa i prepared statements per sicurezza
        $stmt = $conn->prepare("INSERT INTO alimenti (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        
        if ($stmt->execute()) { 
            $success_msg = "Alimento aggiunto correttamente!"; 
        } else { 
            $error_msg = "Errore nell'inserimento: " . $stmt->error; 
        }
        $stmt->close();
    } else {
        $error_msg = "Il nome dell'alimento è obbligatorio.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Inserimento Alimento</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body> 
        <script>
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
            }
        </script>
        
        <aside class="sidebar">
            <h1>Igea</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Clienti</a>
                <a href="farmaci.php">Terapie</a>
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <h1>Nuovo Alimento</h1>

            <?php if (!empty($success_msg)): ?>
                <p class="messaggio-php"><?php echo $success_msg; ?></p>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <ul class="error-list">
                    <li><?php echo $error_msg; ?></li>
                </ul>
            <?php endif; ?>

            <form method="POST" action=""> 
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Inserisci i Dati</h2>
                    <a href="alimenti.php" class="btn-azione" style="font-size: 0.9em; padding: 8px 12px; text-decoration: none;">← Torna alla lista degli alimenti</a>
                </div>

                <div>
                    <label>Nome Alimento:</label>
                    <input type="text" name="nome" required>
                </div>
                
                <button type="submit">Aggiungi Alimento</button>
            </form> 

        </main>
    </body>
</html>
<?php $conn->close(); ?>