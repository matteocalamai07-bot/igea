<?php
    session_start();

    $conn = new mysqli("localhost", "root", "", "terranova");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $errors = [];

    // SE IL FORM È STATO INVIATO 
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

        // Validazioni
        if (empty($nome)) {
            $errors[] = "Il nome è obbligatorio.";
        }
        if (empty($cognome)) {
            $errors[] = "Il cognome è obbligatorio.";
        }
        if (empty($data_nascita)) {
            $errors[] = "La data di nascita è obbligatoria.";
        } elseif (strtotime($data_nascita) > time()) {
            $errors[] = "La data di nascita non può essere nel futuro.";
        }
        if (empty($citta)) {
            $errors[] = "La città è obbligatoria.";
        }
        if (empty($indirizzo)) {
            $errors[] = "L'indirizzo è obbligatorio.";
        }
        if (empty($civico)) {
            $errors[] = "Il numero civico è obbligatorio.";
        }
        if (empty($email)) {
            $errors[] = "L'email è obbligatoria.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Il formato dell'email non è valido.";
        }
        if (empty($telefono)) {
            $errors[] = "Il telefono è obbligatorio.";
        } elseif (!preg_match('/^[0-9+\-\s()]+$/', $telefono)) {
            $errors[] = "Il formato del telefono non è valido. Usa solo numeri, spazi, parentesi e trattini.";
        }

        // Se non ci sono errori, inserisci nel database
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO paziente (nome, cognome, datanascita, citta, indirizzo, civico, professione, email, telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $nome, $cognome, $data_nascita, $citta, $indirizzo, $civico, $professione, $email, $telefono);
            
            if ($stmt->execute()) { 
                echo "<p>Dati inseriti correttamente!</p>"; 
            } else { 
                echo "<p>Errore nell'inserimento: " . $stmt->error . "</p>"; 
            }
            $stmt->close();
        } else {
            // Mostra errori
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        $conn->close(); 
    }
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Inserimento Paziente</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Igea - Nuovo Paziente</h1>
        <form method="POST" action=""> 
            <div>
                Inserisci il nome:
                <input type="text" name="nome" required>
            </div> 
            <div>
                Inserisci il cognome:
                <input type="text" name="cognome" required>
            </div> 
            <div>
                Inserisci la data di nascita:
                <input type="date" name="data_nascita" required max="<?php echo date('Y-m-d'); ?>">
            </div> 
            <div>
                Inserisci la città:
                <input type="text" name="citta" required>
            </div> 
            <div>
                Inserisci l'indirizzo:
                <input type="text" name="indirizzo" required>
            </div> 
            <div>
                Inserisci il numero civico:
                <input type="text" name="civico" required>
            </div> 
            <div>
                Inserisci la professione:
                <input type="text" name="professione">
            </div> 
            <div>
                Inserisci l'email:
                <input type="email" name="email" required>
            </div> 
            <div>
                Inserisci il telefono:
                <input type="tel" name="telefono" pattern="[0-9+\-\s()]+" title="Solo numeri, spazi, parentesi e trattini" required>
            </div> 
            
            <button type="submit">Salva Paziente</button>
        </form>
        <a href="pazienti.php">Torna alla lista dei pazienti</a>
        <br>
        <a href="index.php">Torna alla Home</a>
    </body>
</html>