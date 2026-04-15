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
    <title>Igea - Clienti</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Stili generali e adattamento al tema */
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
            top: 100%; 
            left: 0;
            width: 100%; 
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
            display: block;
        }
        #risultatiRicerca div, 
        #risultatiRicerca p, 
        #risultatiRicerca a {
            display: block;
            padding: 10px 15px;
            margin: 0;
            color: #0f172a;
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

        /* --- STILE DEI PULSANTI IN TABELLA --- */
        .btn-small {
            display: inline-block;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
            transition: all 0.2s;
            text-align: center;
        }

        /* Pulsante Visualizza (Blu) */
        .btn-visualizza {
            background-color: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
        }
        .btn-visualizza:hover { background-color: #3b82f6; color: #ffffff; }

        /* Pulsante Anamnesi MANCANTE (Rosso) */
        .btn-anamnesi-mancante {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }
        .btn-anamnesi-mancante:hover { background-color: #ef4444; color: #ffffff; }

        /* Pulsante Anamnesi PRESENTE (Verde) */
        .btn-anamnesi-presente {
            background-color: #f0fdf4;
            color: #22c55e;
            border: 1px solid #bbf7d0;
        }
        .btn-anamnesi-presente:hover { background-color: #22c55e; color: #ffffff; }

        /* Pulsante Elimina (Rosso Pieno) */
        .btn-elimina {
            background-color: #ef4444;
            color: #ffffff;
            border: 1px solid #dc2626;
        }
        .btn-elimina:hover { background-color: #dc2626; color: #ffffff; }
        .btn-elimina:active { transform: scale(0.95); }

        .empty-msg {
            text-align: center; 
            padding: 20px; 
            color: #475569;
        }

        /* --- OVERRIDE TEMA SCURO --- */
        body.dark-mode .page-title,
        body.dark-mode .card-cruscotto h2 { color: #f8fafc; }
        body.dark-mode .section-label,
        body.dark-mode .empty-msg { color: #cbd5e1; }
        
        body.dark-mode .search-input { background-color: #1e293b; border-color: #334155; color: #f8fafc; }
        body.dark-mode .search-input:focus { border-color: #3b82f6; background-color: #0f172a; }

        body.dark-mode #risultatiRicerca { background-color: #0f172a; border-color: #334155; box-shadow: 0 4px 6px rgba(0,0,0,0.4); }
        body.dark-mode #risultatiRicerca div, 
        body.dark-mode #risultatiRicerca p, 
        body.dark-mode #risultatiRicerca a { color: #f8fafc; border-bottom-color: #1e293b; }
        body.dark-mode #risultatiRicerca div:hover, 
        body.dark-mode #risultatiRicerca p:hover, 
        body.dark-mode #risultatiRicerca a:hover { background-color: #1e293b; }

        /* Colori bottoni tabella in Dark Mode */
        body.dark-mode .btn-visualizza { background-color: rgba(59, 130, 246, 0.15); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3); }
        body.dark-mode .btn-visualizza:hover { background-color: #3b82f6; color: #ffffff; }

        body.dark-mode .btn-anamnesi-mancante { background-color: rgba(239, 68, 68, 0.15); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
        body.dark-mode .btn-anamnesi-mancante:hover { background-color: #ef4444; color: #ffffff; }

        body.dark-mode .btn-anamnesi-presente { background-color: rgba(34, 197, 94, 0.15); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
        body.dark-mode .btn-anamnesi-presente:hover { background-color: #22c55e; color: #ffffff; }

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
            <a href="pazienti.php" class="active">Clienti</a>
            <a href="farmaci.php">Terapie</a>
            <a href="alimenti.php">Alimenti</a>
        </nav>
    </aside>

    <main class="main-content">

        <div>
            <h1 class="page-title">Gestione Clienti</h1>
            
            <div style="display: flex; align-items: flex-end; gap: 20px; margin-bottom: 30px;">
                
                <div>
                    <label class="section-label">Aggiungi un nuovo cliente</label>
                    <a href="nuovo_paziente.php" class="btn-azione" style="height: 40px; padding: 0 20px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; text-decoration: none;">+ Nuovo Cliente</a>
                </div>

                <div style="position: relative;">
                    <label class="section-label">Ricerca Cliente</label>
                    <input type="text" id="searchPaziente" class="search-input" placeholder="Digita nome o cognome..." autocomplete="off">
                    <div id="risultatiRicerca"></div>
                </div>

            </div>
        </div>

        <div class="card-cruscotto">
            <h2 style="margin-top:0;">Elenco Clienti</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Data di Nascita</th>
                        <th style="text-align: center;">Scheda Cliente</th>
                        <th style="text-align: center;">Anamnesi</th>
                        <th style="text-align: center;">Azione</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Query aggiornata con il campo fk_paziente corrispondente al tuo database
                $query = "SELECT p.*, (SELECT COUNT(*) FROM anamnesi a WHERE a.fk_paziente = p.id) AS has_anamnesi FROM paziente p ORDER BY p.cognome";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        
                        // Logica per determinare colore e testo del pulsante Anamnesi
                        if ($row['has_anamnesi'] > 0) {
                            $classe_anamnesi = "btn-anamnesi-presente";
                            $testo_anamnesi = "+ Aggiungi";
                        } else {
                            $classe_anamnesi = "btn-anamnesi-mancante";
                            $testo_anamnesi = "+ Aggiungi";
                        }

                        echo "<tr>";
                        echo "<td>{$row['nome']}</td>";
                        echo "<td>{$row['cognome']}</td>";
                        echo "<td>{$row['datanascita']}</td>";
                        
                        echo "<td style='text-align: center;'>
                                <a href='scheda_paziente.php?id={$row['id']}' class='btn-small btn-visualizza'>Visualizza</a>
                              </td>";
                              
                        echo "<td style='text-align: center;'>
                                <a href='aggiungi_anamnesi.php?id={$row['id']}' class='btn-small {$classe_anamnesi}'>{$testo_anamnesi}</a>
                              </td>";
                              
                        echo "<td style='text-align: center;'>
                                <a href='#' class='btn-small btn-elimina' onclick=\"confermaEliminazione('Cliente', '{$row['nome']} {$row['cognome']}', 'elimina_paziente.php?id={$row['id']}'); return false;\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='empty-msg'>Nessun cliente registrato nel database.</td></tr>";
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
                <p>Sei sicuro di voler eliminare questo elemento? Tutti i dati associati potrebbero andare persi.</p>
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
    document.getElementById("searchPaziente").addEventListener("keyup", function () {
        let valore = this.value.trim();

        if (valore.length === 0) {
            document.getElementById("risultatiRicerca").innerHTML = "";
            return;
        }

        let xhr = new XMLHttpRequest();
        xhr.open("GET", "ajax_cerca_paziente.php?q=" + encodeURIComponent(valore), true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("risultatiRicerca").innerHTML = xhr.responseText;
            }
        };

        xhr.send();
    });

    // Chiude i risultati di ricerca se l'utente clicca fuori dalla casella
    document.addEventListener('click', function(event) {
        let searchBox = document.getElementById('searchPaziente');
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