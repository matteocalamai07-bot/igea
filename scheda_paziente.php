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
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            .editable {
                cursor: pointer;
                border-bottom: 1px dashed #ccc;
            }
            .editable:hover {
                background-color: #f0f0f0;
            }
            .editing input {
                width: 100%;
                box-sizing: border-box;
            }
        </style>
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
            <h2 style="text-align: center;">Dettagli Paziente</h2>
            <div>
                <?php 
                    $query = "SELECT * FROM paziente WHERE id = " . $_GET['id'];
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo "<p><strong>Nome:</strong> <span class='editable' data-field='nome' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['nome']) . "</span></p>";
                        echo "<p><strong>Cognome:</strong> <span class='editable' data-field='cognome' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['cognome']) . "</span></p>";
                        echo "<p><strong>Data di Nascita:</strong> <span class='editable' data-field='datanascita' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['datanascita']) . "</span></p>";
                        echo "<p><strong>Città:</strong> <span class='editable' data-field='citta' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['citta']) . "</span></p>";
                        echo "<p><strong>Indirizzo:</strong> <span class='editable' data-field='indirizzo' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['indirizzo']) . "</span></p>";
                        echo "<p><strong>Numero Civico:</strong> <span class='editable' data-field='civico' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['civico']) . "</span></p>";
                        echo "<p><strong>Professione:</strong> <span class='editable' data-field='professione' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['professione']) . "</span></p>";
                        echo "<p><strong>Telefono:</strong> <span class='editable' data-field='telefono' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['telefono']) . "</span></p>";
                        echo "<p><strong>Email:</strong> <span class='editable' data-field='email' data-id='" . $row['id'] . "'>" . htmlspecialchars($row['email']) . "</span></p>";
                    } else {
                        echo "<p>Paziente non trovato.</p>";
                    }
                ?>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('.editable').click(function() {
                    var $this = $(this);
                    var field = $this.data('field');
                    var id = $this.data('id');
                    var currentValue = $this.text();

                    // Determine input type
                    var inputType = 'text';
                    if (field === 'datanascita') {
                        inputType = 'date';
                    } else if (field === 'email') {
                        inputType = 'email';
                    } else if (field === 'telefono') {
                        inputType = 'tel';
                    }

                    var input = $('<input type="' + inputType + '" value="' + currentValue + '" />');
                    $this.html(input);
                    input.focus().select();

                    input.blur(function() {
                        var newValue = $(this).val();
                        if (newValue !== currentValue) {
                            // Send AJAX request
                            $.post('update_paziente.php', {
                                id: id,
                                field: field,
                                value: newValue
                            }, function(response) {
                                var data = JSON.parse(response);
                                if (data.success) {
                                    $this.text(newValue);
                                } else {
                                    alert('Errore: ' + data.message);
                                    $this.text(currentValue);
                                }
                            }).fail(function() {
                                alert('Errore nella comunicazione con il server');
                                $this.text(currentValue);
                            });
                        } else {
                            $this.text(currentValue);
                        }
                    });

                    input.keypress(function(e) {
                        if (e.which === 13) { // Enter key
                            $(this).blur();
                        }
                    });
                });
            });
        </script>
    </body>
</html>

<?php $conn->close(); ?>