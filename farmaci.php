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
        <style>
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
                <a href="alimenti.php">Alimenti</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <h1>Gestione Terapie e Farmaci</h1>

            <div class="azioni-rapide">
                <label>Aggiungi un nuovo elemento</label>
                <a href="nuova_terapia.php" class="btn-azione">+ Nuova Terapia/Farmaco</a>
            </div>

            <div class="section">
                <h2>Farmaci</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                            <th>Elimina</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT * FROM farmaci";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>".$row['nome']."</td>";
                                echo "<td>".$row['descrizione']."</td>";
                                echo "<td><a href='#' onclick=\"confermaEliminazione('Farmaco', '".$row['nome']."', 'elimina_farmaco.php?id=".$row['id']."'); return false;\">Elimina</a></td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>Integratori</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                            <th>Elimina</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT * FROM integratori";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>".$row['nome']."</td>";
                                echo "<td>".$row['descrizione']."</td>";
                                echo "<td><a href='#' onclick=\"confermaEliminazione('Integratore', '".$row['nome']."', 'elimina_integratore.php?id=".$row['id']."'); return false;\">Elimina</a></td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>Supporti</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                            <th>Elimina</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT * FROM supporti";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>".$row['nome']."</td>";
                                echo "<td>".$row['descrizione']."</td>";
                                echo "<td><a href='#' onclick=\"confermaEliminazione('Supporto', '".$row['nome']."', 'elimina_supporto.php?id=".$row['id']."'); return false;\">Elimina</a></td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>Terapie</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                            <th>Elimina</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $query = "SELECT * FROM terapie";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>".$row['nome']."</td>";
                                echo "<td>".$row['descrizione']."</td>";
                                echo "<td><a href='#' onclick=\"confermaEliminazione('Terapia', '".$row['nome']."', 'elimina_terapia.php?id=".$row['id']."'); return false;\">Elimina</a></td>";
                                echo "</tr>";
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
            let deleteUrl = "";

            window.confermaEliminazione = function(tipo, nome, url) {
                document.getElementById("deleteItemType").textContent = tipo;
                document.getElementById("deleteItemName").textContent = nome;
                deleteUrl = url;
                document.getElementById("deleteModal").classList.add("active");
            }

            window.chiudiModal = function() {
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