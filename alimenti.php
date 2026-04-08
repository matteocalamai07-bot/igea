<?php
    session_start();

    $conn = new mysqli("localhost", "root", "", "terranova");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // ==========================================
    // LOGICA DI PAGINAZIONE
    // ==========================================
    $elementi_per_pagina = 7;
    
    // Recupera il numero di pagina corrente (di default 1)
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    
    // Calcola l'offset per la query
    $offset = ($page - 1) * $elementi_per_pagina;
    
    // Conta il totale degli alimenti per capire quante pagine ci sono
    $count_query = "SELECT COUNT(*) as totale FROM alimenti";
    $count_result = $conn->query($count_query);
    $totale_elementi = $count_result->fetch_assoc()['totale'];
    $totale_pagine = ceil($totale_elementi / $elementi_per_pagina);
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Igea - Alimenti</title>
        <link rel="stylesheet" href="style.css">
        <style>
            /* Stile per i bottoni della paginazione */
            .btn-page {
                padding: 6px 12px;
                border: 1px solid rgba(15,23,42,0.2);
                background-color: #ffffff;
                color: #0f172a;
                text-decoration: none;
                border-radius: 5px;
                font-size: 0.9rem;
                font-weight: bold;
                transition: background-color 0.2s, border-color 0.2s;
            }
            .btn-page:hover {
                background-color: #f1f5f9;
                border-color: #6366f1; /* Colore primario hover */
                color: #6366f1;
            }
        </style>
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0;">Alimenti Registrati</h2>
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php if($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="btn-page">&laquo; Prec</a>
                        <?php endif; ?>
                        
                        <span style="font-size: 0.9rem; color: #475569;">
                            Pagina <strong><?= $page ?></strong> di <strong><?= max(1, $totale_pagine) ?></strong>
                        </span>
                        
                        <?php if($page < $totale_pagine): ?>
                            <a href="?page=<?= $page + 1 ?>" class="btn-page">Succ &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Elimina</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Aggiunti LIMIT e OFFSET per mostrare solo 10 alimenti alla volta
                            $query = "SELECT * FROM alimenti ORDER BY id DESC LIMIT $elementi_per_pagina OFFSET $offset";
                            $result = $conn->query($query);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>".$row['nome']."</td>";
                                    echo "<td>
                                            <a href='#' onclick=\"confermaEliminazione('elimina_alimento.php?id=".$row['id']."'); return false;\">Elimina</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' style='text-align:center; padding: 20px; color: #475569;'>Nessun alimento registrato.</td></tr>";
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
