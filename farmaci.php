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
        <title>Igea - Terapie</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1>Igea - Terapie/Farmaci</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </header>

        <div class="azioni-rapide">
            <label>Aggiungi un nuovo elemento:</label>
            <a href="nuova_terapia.php">+ Nuova Terapia/Farmaco</a>
        </div>

        <!-- FARMACI -->
        <div class="section">
            <h2>Farmaci:</h2>

            <table border="1">
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM farmaci";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='#' onclick=\"confermaEliminazione('elimina_farmaco.php?id=".$row['id']."'); return false;\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <!-- INTEGRATORI -->
        <div class="section">
            <h2>Integratori:</h2>

            <table border="1">
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM integratori";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='#' onclick=\"confermaEliminazione('elimina_integratore.php?id=".$row['id']."'); return false;\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <!-- SUPPORTI -->
        <div class="section">
            <h2>Supporti:</h2>

            <table border="1">
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM supporti";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='#' onclick=\"confermaEliminazione('elimina_supporto.php?id=".$row['id']."'); return false;\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <!-- TERAPIE -->
        <div class="section">
            <h2>Terapie:</h2>

            <table border="1">
                <tr>
                    <th>Nome</th>
                    <th>Descrizione</th>
                    <th>Elimina</th>
                </tr>
                <?php
                    $query = "SELECT * FROM terapie";
                    $result = $conn->query($query);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        echo "<td>".$row['descrizione']."</td>";
                        echo "<td>
                                <a href='#' onclick=\"confermaEliminazione('elimina_terapia.php?id=".$row['id']."'); return false;\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <!-- POPUP MODALE -->
        <div class="modal-overlay" id="confirmModal">
            <div class="modal">
                <h3>Conferma eliminazione</h3>
                <p>Sei sicuro di voler eliminare questo elemento?</p>
                <div class="modal-buttons">
                    <button class="btn-cancel" onclick="chiudiModal()">Annulla</button>
                    <button class="btn-delete" id="confirmDelete">Elimina</button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {

            let deleteUrl = "";

            window.confermaEliminazione = function(url){
                deleteUrl = url;
                document.getElementById("confirmModal").style.display = "flex";
            }

            window.chiudiModal = function(){
                document.getElementById("confirmModal").style.display = "none";
            }

            document.getElementById("confirmDelete").addEventListener("click", function(){
                window.location.href = deleteUrl;
            });

        });
        </script>

    </body>
</html>

<?php $conn->close(); ?>
