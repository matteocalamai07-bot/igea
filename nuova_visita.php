<?php
session_start();

$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$errors = [];

/* =========================
   CONTROLLO ID PAZIENTE
========================= */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("ID paziente non valido.");
}

$id_paziente = intval($_GET["id"]);

/* Verifico che il paziente esista */
$check = $conn->prepare("SELECT id FROM paziente WHERE id = ?");
$check->bind_param("i", $id_paziente);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    die("Paziente non trovato.");
}
$check->close();

/* =========================
   GESTIONE FORM
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Nuova Visita Paziente</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <h1>Igea - Nuova visita paziente</h1>
        <br>
        <div class="top-links">
            <a href="scheda_paziente.php?id=<?php echo $id_paziente; ?>" class="btn-top">Torna alla scheda del paziente</a>
            <a href="index.php" class="btn-top">Torna alla Home</a>
        </div>

        <?php
        if (!empty($errors)) {
            echo "<ul style='color:red;'>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        ?>

        <form method="POST" action="nuova_visita.php?id=<?php echo $id_paziente; ?>">
            
        </form>
    </body>
</html>
