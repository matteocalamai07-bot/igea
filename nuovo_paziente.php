<?php
    session_start();

    $conn = new mysqli("localhost", "root", "", "terranova");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $errors = [];
    $success_msg = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {  
        $nome = trim($_POST["nome"]);
        $cognome = trim($_POST["cognome"]);
        $data_nascita = $_POST["data_nascita"];
        $citta = trim($_POST["citta"]);
        $indirizzo = trim($_POST["indirizzo"]);
        $civico = trim($_POST["civico"]);
        $professione = trim($_POST["professione"]);
        $email = trim($_POST["email"]);
        $telefono = trim($_POST["telefono"]);

        if (empty($nome)) { $errors[] = "Il nome è obbligatorio."; }
        if (empty($cognome)) { $errors[] = "Il cognome è obbligatorio."; }
        if (empty($data_nascita)) { $errors[] = "La data di nascita è obbligatoria."; } 
        elseif (strtotime($data_nascita) > time()) { $errors[] = "La data di nascita non può essere nel futuro."; }
        if (empty($citta)) { $errors[] = "La città è obbligatoria."; }
        if (empty($indirizzo)) { $errors[] = "L'indirizzo è obbligatorio."; }
        if (empty($civico)) { $errors[] = "Il numero civico è obbligatorio."; }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Il formato dell'email non è valido."; }
        if (!empty($telefono) && !preg_match('/^[0-9+\-\s()]+$/', $telefono)) { $errors[] = "Il formato del telefono non è valido."; }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO paziente (nome, cognome, datanascita, citta, indirizzo, civico, professione, email, telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $nome, $cognome, $data_nascita, $citta, $indirizzo, $civico, $professione, $email, $telefono);
            
            if ($stmt->execute()) { 
                $success_msg = "Dati inseriti correttamente!"; 
            } else { 
                $errors[] = "Errore nell'inserimento: " . $stmt->error; 
            }
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Nuovo Paziente</title>
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
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <h1>Nuovo Paziente</h1>

            <?php if (!empty($success_msg)): ?>
                <p class="messaggio-php"><?php echo $success_msg; ?></p>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $error) { echo "<li>$error</li>"; } ?>
                </ul>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Inserisci i Dati</h2>
                    <a href="pazienti.php" class="btn-azione" style="font-size: 0.9em; padding: 8px 12px; text-decoration: none;">← Torna alla lista dei pazienti</a>
                </div>
                
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                </div> 
                <div>
                    <label>Cognome:</label>
                    <input type="text" name="cognome" required>
                </div> 
                <div>
                    <label>Data di nascita:</label>
                    <input type="date" name="data_nascita" required max="<?php echo date('Y-m-d'); ?>">
                </div> 
                <div>
                    <label>Città:</label>
                    <input type="text" name="citta" required>
                </div> 
                <div>
                    <label>Indirizzo:</label>
                    <input type="text" name="indirizzo" required>
                </div> 
                <div>
                    <label>Numero civico:</label>
                    <input type="text" name="civico" required>
                </div> 
                <div>
                    <label>Professione:</label>
                    <input type="text" name="professione">
                </div> 
                <div>
                    <label>Email:</label>
                    <input type="email" name="email">
                </div> 
                <div>
                    <label>Telefono:</label>
                    <input type="tel" name="telefono" pattern="[0-9+\-\s()]+" title="Solo numeri, spazi, parentesi e trattini">
                </div> 
                
                <button type="submit">Salva Paziente</button>
            </form>

        </main>
    </body>
</html>
<?php $conn->close(); ?>
