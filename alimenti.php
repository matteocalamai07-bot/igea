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
        <title>Igea - Alimenti</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        
        <aside class="sidebar">
            <h1>Igea</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="pazienti.php">Pazienti</a>
                <a href="farmaci.php">Terapie</a>
                <a href="alimenti.php" class="active">Alimenti</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <div>
                <h1 style="font-size: 2rem; color: #0f172a; margin-bottom: 25px; margin-top: 0;">Gestione Alimenti</h1>
                
                <div style="display: flex; align-items: flex-end; gap: 20px; margin-bottom: 30px;">
                    
                    <div>
                        <label style="display: block; font-size: 0.9rem; color: #475569; margin-bottom: 8px; font-weight: 600;">Aggiungi un nuovo alimento</label>
                        <a href="nuovo_alimento.php" class="btn-azione" style="height: 40px; padding: 0 20px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; text-decoration: none;">+ Nuovo Alimento</a>
                    </div>

                    <div style="position: relative;">
                        <label style="display: block; font-size: 0.9rem; color: #475569; margin-bottom: 8px; font-weight: 600;">Ricerca Alimenti da eliminare</label>
                        <input type="text" id="searchAlimenti" placeholder="Digita il nome dell'alimento..." autocomplete="off" style="width: 300px; height: 40px; padding: 0 15px; border: 1px solid rgba(15,23,42,0.15); border-radius: 5px; box-sizing: border-box; font-size: 0.95rem; outline: none;">
                        <div id="risultatiRicerca"></div>
                    </div>

                </div>
            </div>

            <div class="card-cruscotto">
                <h2 style="margin-top:0;">Alimenti Registrati</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Elimina</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT * FROM alimenti";
                            $result = $conn->query($query);

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>".$row['nome']."</td>";
                                echo "<td>
                                        <a href='#' onclick=\"confermaEliminazione('elimina_alimento.php?id=".$row['id']."'); return false;\">Elimina</a>
                                      </td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>

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
        document.getElementById("searchAlimenti").addEventListener("keyup", function () {
            let valore = this.value.trim();

            if (valore.length === 0) {
                document.getElementById("risultatiRicerca").innerHTML = "";
                return;
            }

            let xhr = new XMLHttpRequest();
            xhr.open("GET", "ajax_cerca_alimenti.php?q=" + encodeURIComponent(valore), true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("risultatiRicerca").innerHTML = xhr.responseText;
                }
            };

            xhr.send();
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
