<?php
    session_start();

    $conn = new mysqli("localhost", "root", "", "terranova");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    // ==========================================
    // LOGICA DI PAGINAZIONE
    // ==========================================
    $elementi_per_pagina = 5;
    
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
            /* Stili per adattare gli elementi al tema chiaro/scuro */
            .page-title {
                font-size: 2rem; 
                color: #0f172a; 
                margin-bottom: 25px; 
                margin-top: 0;
                transition: color 0.3s;
            }
            .section-label {
                display: block; 
                font-size: 0.9rem; 
                color: #475569; 
                margin-bottom: 8px; 
                font-weight: 600;
                transition: color 0.3s;
            }
            .search-input {
                width: 300px; 
                height: 40px; 
                padding: 0 15px; 
                border: 1px solid #cbd5e1; 
                border-radius: 5px; 
                box-sizing: border-box; 
                font-size: 0.95rem; 
                outline: none;
                background-color: #f8fafc;
                color: #0f172a;
                transition: all 0.3s;
            }
            .search-input:focus {
                border-color: #3b82f6; 
                background-color: #ffffff;
            }

            /* --- STILE PER IL BOX DEI SUGGERIMENTI AJAX --- */
            #risultatiRicerca {
                position: absolute;
                top: 100%; /* Si posiziona esattamente sotto il contenitore padre */
                left: 0;
                width: 100%; /* Stessa larghezza dell'input */
                background-color: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 5px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 1000;
                max-height: 250px;
                overflow-y: auto;
                margin-top: 5px;
                display: none;
            }
            #risultatiRicerca:not(:empty) {
                display: block; /* Mostra il contenitore solo se ci sono risultati */
            }
            /* Stile per i singoli elementi della ricerca (assumendo che ajax restituisca div, p o a) */
            #risultatiRicerca div, 
            #risultatiRicerca p, 
            #risultatiRicerca a {
                display: block;
                padding: 10px 15px;
                margin: 0;
                /*color: #0f172a;*/
                text-decoration: none;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.95rem;
                cursor: pointer;
                transition: background-color 0.2s;
            }
            #risultatiRicerca div:last-child, 
            #risultatiRicerca p:last-child, 
            #risultatiRicerca a:last-child {
                border-bottom: none;
            }
            #risultatiRicerca div:hover, 
            #risultatiRicerca p:hover, 
            #risultatiRicerca a:hover {
                background-color: #f8fafc;
            }

            /* --- STILE PER I PULSANTI ELIMINA IN ROSSO --- */
            .btn-elimina {
                display: inline-block;
                padding: 6px 12px;
                background-color: #ef4444; /* Rosso vivo */
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                font-size: 0.85rem;
                font-weight: bold;
                border: 1px solid #dc2626; /* Rosso scuro per il bordo */
                transition: background-color 0.2s, transform 0.1s;
            }
            .btn-elimina:hover {
                background-color: #dc2626;
                color: #ffffff;
            }
            .btn-elimina:active {
                transform: scale(0.95);
            }

            /* Stile per i bottoni della paginazione */
            .btn-page {
                padding: 6px 12px;
                border: 1px solid #cbd5e1;
                background-color: #ffffff;
                color: #0f172a;
                text-decoration: none;
                border-radius: 5px;
                font-size: 0.9rem;
                font-weight: bold;
                transition: all 0.2s;
            }
            .btn-page:hover {
                background-color: #f1f5f9;
                border-color: #6366f1; 
                color: #6366f1;
            }
            .pagination-text {
                font-size: 0.9rem; 
                color: #475569;
                transition: color 0.3s;
            }
            .empty-msg {
                text-align: center; 
                padding: 20px; 
                color: #475569;
            }

            /* --- OVERRIDE TEMA SCURO --- */
            body.dark-mode .page-title,
            body.dark-mode .card-cruscotto h2 { color: #f8fafc; }
            body.dark-mode .section-label,
            body.dark-mode .pagination-text,
            body.dark-mode .empty-msg { color: #cbd5e1; }
            
            body.dark-mode .search-input { background-color: #1e293b; border-color: #334155; color: #f8fafc; }
            body.dark-mode .search-input:focus { border-color: #3b82f6; background-color: #0f172a; }

            body.dark-mode .btn-page { background-color: #334155; color: #f8fafc; border-color: #475569; }
            body.dark-mode .btn-page:hover { background-color: #475569; border-color: #818cf8; color: #818cf8; }

            /* Risultati ricerca in tema scuro */
            body.dark-mode #risultatiRicerca { background-color: #0f172a; border-color: #334155; box-shadow: 0 4px 6px rgba(0,0,0,0.4); }
            body.dark-mode #risultatiRicerca div, 
            body.dark-mode #risultatiRicerca p, 
            body.dark-mode #risultatiRicerca a { color: #f8fafc; border-bottom-color: #1e293b; }
            body.dark-mode #risultatiRicerca div:hover, 
            body.dark-mode #risultatiRicerca p:hover, 
            body.dark-mode #risultatiRicerca a:hover { background-color: #1e293b; }

            /* Finestra Modale in tema scuro */
            body.dark-mode .modal { background-color: #1e293b; color: #f8fafc; border: 1px solid #334155; }
            body.dark-mode .modal h3 { color: #f8fafc; }
            body.dark-mode .modal p { color: #cbd5e1; }
            body.dark-mode .btn-cancel { background-color: #334155; color: #f8fafc; border-color: #475569; }
            body.dark-mode .btn-cancel:hover { background-color: #475569; }

            /* MODAL ELIMINAZIONE ELEGANTE */
            .delete-modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
                z-index: 10000;
                align-items: center;
                justify-content: center;
            }

            .delete-modal-overlay.active {
                display: flex;
            }

            .delete-modal-dialog {
                background: var(--bg-card);
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 420px;
                width: 90%;
                padding: 0;
                overflow: hidden;
                animation: modalSlideIn 0.3s ease-out;
            }

            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: scale(0.95) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .delete-modal-header {
                padding: 24px 24px 16px;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .delete-modal-header-icon {
                font-size: 24px;
            }

            .delete-modal-header h2 {
                margin: 0;
                color: var(--text-main);
                font-size: 1.2rem;
            }

            .delete-modal-body {
                padding: 20px 24px;
                color: var(--text-muted);
                line-height: 1.6;
            }

            .delete-modal-detail {
                background: var(--bg-page);
                padding: 12px;
                border-radius: 6px;
                margin: 12px 0;
                font-size: 0.9rem;
                color: var(--text-main);
                word-break: break-word;
            }

            .delete-modal-detail-label {
                font-weight: 600;
                color: var(--text-main);
                margin-bottom: 4px;
            }

            .delete-modal-footer {
                padding: 16px 24px;
                border-top: 1px solid var(--border-color);
                display: flex;
                gap: 12px;
                justify-content: flex-end;
            }

            .btn-modal {
                padding: 10px 18px;
                border: none;
                border-radius: 6px;
                font-size: 0.95rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .btn-modal-cancel {
                background: var(--border-color);
                color: var(--text-main);
            }

            .btn-modal-cancel:hover {
                background: var(--border-color);
                opacity: 0.8;
            }

            .btn-modal-delete {
                background: #ef4444 !important;
                color: white !important;
            }

            .btn-modal-delete:hover {
                background: #dc2626 !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            }

            .btn-modal-delete:active {
                transform: translateY(0);
            }
        </style>
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
                <a href="alimenti.php" class="active">Alimenti</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <div>
                <h1 class="page-title">Gestione Alimenti</h1>
                
                <div style="display: flex; align-items: flex-end; gap: 20px; margin-bottom: 30px;">
                    
                    <div>
                        <label class="section-label">Aggiungi un nuovo alimento</label>
                        <a href="nuovo_alimento.php" class="btn-azione" style="height: 40px; padding: 0 20px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; text-decoration: none;">+ Nuovo Alimento</a>
                    </div>

                    <div style="position: relative;">
                        <label class="section-label">Ricerca Alimenti da eliminare</label>
                        <input type="text" id="searchAlimenti" class="search-input" placeholder="Digita il nome dell'alimento..." autocomplete="off">
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
                        
                        <span class="pagination-text">
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
                            <th>Azione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Aggiunti LIMIT e OFFSET per mostrare solo elementi paginati
                            $query = "SELECT * FROM alimenti ORDER BY id DESC LIMIT $elementi_per_pagina OFFSET $offset";
                            $result = $conn->query($query);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>".$row['nome']."</td>";
                                    echo "<td>";
                                    echo "<a href='#' class='btn-elimina' onclick=\"confermaEliminazione('Alimento', '".$row['nome']."', 'elimina_alimento.php?id=".$row['id']."'); return false;\">Elimina</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' class='empty-msg'>Nessun alimento registrato.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>

        <!-- MODAL ELIMINAZIONE ELEGANTE -->
        <div class="delete-modal-overlay" id="deleteModal">
            <div class="delete-modal-dialog">
                <div class="delete-modal-header">
                    <div class="delete-modal-header-icon">⚠️</div>
                    <h2>Elimina <span id="deleteItemType">Elemento</span></h2>
                </div>
                <div class="delete-modal-body">
                    <p>Sei sicuro di voler eliminare questo elemento?</p>
                    <div class="delete-modal-detail">
                        <div class="delete-modal-detail-label">Nome</div>
                        <div id="deleteItemName">-</div>
                    </div>
                    <p style="color: #dc2626; font-size: 0.85rem; margin-top: 12px;">⚠️ Questa azione non può essere annullata.</p>
                </div>
                <div class="delete-modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="chiudiModal()">Annulla</button>
                    <button type="button" class="btn-modal btn-modal-delete" id="confirmDelete">Elimina</button>
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

        // Chiude i risultati di ricerca se si clicca fuori
        document.addEventListener('click', function(event) {
            let searchBox = document.getElementById('searchAlimenti');
            let resultsBox = document.getElementById('risultatiRicerca');
            if (!searchBox.contains(event.target) && !resultsBox.contains(event.target)) {
                resultsBox.innerHTML = "";
            }
        });

        /* POPUP ELIMINAZIONE */
        let deleteUrl = "";

        function confermaEliminazione(tipo, nome, url) {
            document.getElementById("deleteItemType").textContent = tipo;
            document.getElementById("deleteItemName").textContent = nome;
            deleteUrl = url;
            document.getElementById("deleteModal").classList.add("active");
        }

        function chiudiModal() {
            document.getElementById("deleteModal").classList.remove("active");
            deleteUrl = "";
        }

        document.getElementById("confirmDelete").addEventListener("click", function() {
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        });

        // Chiudi modal cliccando fuori
        document.getElementById("deleteModal").addEventListener("click", function(event) {
            if (event.target === this) {
                chiudiModal();
            }
        });

        // Chiudi con tasto Escape
        document.addEventListener("keydown", function(event) {
            if (event.key === "Escape") {
                chiudiModal();
            }
        });
        </script>
    </body>
</html>
<?php $conn->close(); ?>