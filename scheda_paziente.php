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
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </header>

        <br><br>
        <div class="top-links">
            <a href="pazienti.php" class="btn-top">Torna alla lista dei pazienti</a>
            <a href="index.php" class="btn-top">Torna alla Home</a>
        </div>

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
            <h2 style="text-align: center;">Anamnesi Paziente</h2>
            <div>
                <?php
                    $query_anamnesi = "SELECT * FROM anamnesi WHERE fk_paziente = " . $_GET['id'];
                    $result_anamnesi = $conn->query($query_anamnesi);
                    if ($result_anamnesi->num_rows > 0) {
                        while ($row_anamnesi = $result_anamnesi->fetch_assoc()) {
                            echo "<p><strong>Allergie:</strong> " . htmlspecialchars($row_anamnesi['allergie']) . "</p>";
                            echo "<p><strong>Dettagli Allergie:</strong> " . htmlspecialchars($row_anamnesi['dettagli_allergie']) . "</p>";
                            echo "<p><strong>Fumo:</strong> " . htmlspecialchars($row_anamnesi['fumo']) . "</p>";
                            echo "<p><strong>Dettagli Fumo:</strong> " . htmlspecialchars($row_anamnesi['dettagli_fumo']) . "</p>";
                            echo "<p><strong>Alcol:</strong> " . htmlspecialchars($row_anamnesi['alcol']) . "</p>";
                            echo "<p><strong>Dettagli Alcol:</strong> " . htmlspecialchars($row_anamnesi['dettagli_alcol']) . "</p>";
                            echo "<p><strong>Patologie:</strong> " . htmlspecialchars($row_anamnesi['patologie']) . "</p>";
                            echo "<p><strong>Dettagli Patologie:</strong> " . htmlspecialchars($row_anamnesi['dettagli_patologie']) . "</p>";
                            echo "<p><strong>Interventi:</strong> " . htmlspecialchars($row_anamnesi['interventi']) . "</p>";
                            echo "<p><strong>Dettagli Interventi:</strong> " . htmlspecialchars($row_anamnesi['dettagli_interventi']) . "</p>";
                            echo "<p><strong>Esami:</strong> " . htmlspecialchars($row_anamnesi['esami']) . "</p>";
                            echo "<p><strong>Dettagli Esami:</strong> " . htmlspecialchars($row_anamnesi['dettagli_esami']) . "</p>";
                            echo "<p>
                                    <button class='btn-delete-anamnesi'
                                        onclick=\"confermaEliminazione('elimina_anamnesi.php?id=".$row_anamnesi['id']."'); return false;\">
                                        Elimina Anamnesi
                                    </button>
                                </p>";
                        }
                    } else {
                        echo "<p>Nessuna anamnesi disponibile.</p>";
                        echo "<p><a href='aggiungi_anamnesi.php?id=" . $_GET['id'] . "'>Aggiungi Anamnesi</a></p>";
                    }
                ?>
        </div>

        <!-- POPUP MODALE ELIMINAZIONE -->

        <div class="modal-overlay" id="confirmModal">
            <div class="modal">
            <h3>Conferma eliminazione</h3>
            <p>Sei sicuro di voler eliminare questo paziente?</p>
                <div class="modal-buttons">
                    <button class="btn-cancel" onclick="chiudiModal()">Annulla</button>
                    <button class="btn-delete" id="confirmDelete">Elimina</button>
                </div>
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

            /* POPUP ELIMINAZIONE */

            let deleteUrl = "";

            function confermaEliminazione(url){
                deleteUrl = url;
                document.getElementById("confirmModal").style.display = "flex";
            }

            function chiudiModal(){
                document.getElementById("confirmModal").style.display = "none";
            }

            document.getElementById("confirmDelete").onclick = function(){
                window.location.href = deleteUrl;
            };
        </script>
    </body>
</html>

<?php $conn->close(); ?>