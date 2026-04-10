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
    <title>Igea - Pazienti</title>
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
            <a href="pazienti.php" class="active">Pazienti</a>
            <a href="farmaci.php">Terapie</a>
            <a href="alimenti.php">Alimenti</a>
        </nav>
    </aside>

    <main class="main-content">

        <div>
            <h1 class="page-title">Gestione Pazienti</h1>
            
            <div style="display: flex; align-items: flex-end; gap: 20px; margin-bottom: 30px;">
                
                <div>
                    <label class="section-label">Aggiungi un nuovo paziente</label>
                    <a href="nuovo_paziente.php" class="btn-azione" style="height: 40px; padding: 0 20px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; text-decoration: none;">+ Nuovo Paziente</a>
                </div>

                <div style="position: relative;">
                    <label class="section-label">Ricerca Paziente</label>
                    <input type="text" id="searchPaziente" class="search-input" placeholder="Digita nome o cognome..." autocomplete="off">
                    <div id="risultatiRicerca"></div>
                </div>

            </div>
        </div>

        <div class="card-cruscotto">
            <h2 style="margin-top:0;">Elenco Pazienti</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Data di Nascita</th>
                        <th style="text-align: center;">Scheda Paziente</th>
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
                                <a href='#' class='btn-small btn-elimina' onclick=\"confermaEliminazione('elimina_paziente.php?id=".$row['id']."'); return false;\">Elimina</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='empty-msg'>Nessun paziente registrato nel database.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    </main>

    <div class="modal-overlay" id="confirmModal">
        <div class="modal">
            <h3>Conferma eliminazione</h3>
            <p>Sei sicuro di voler eliminare questo paziente? Tutti i dati associati potrebbero andare persi.</p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="chiudiModal()">Annulla</button>
                <button class="btn-delete btn-elimina" id="confirmDelete" style="border: none;">Elimina</button>
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
