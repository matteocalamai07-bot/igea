<?php
session_start();

// Connessione database
$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// =========================================================================
// ENDPOINT AJAX PER IL GRAFICO
// =========================================================================
if (isset($_GET['ajax_chart'])) {
    $anno = isset($_GET['anno']) ? intval($_GET['anno']) : date('Y');
    
    $chart_data = array_fill(0, 12, 0);
    
    $query_chart = "
        SELECT MONTH(data) as mese_num, COUNT(*) as conteggio 
        FROM visita 
        WHERE YEAR(data) = $anno 
        GROUP BY MONTH(data)
    ";
    
    $result_chart = $conn->query($query_chart);
    
    if ($result_chart && $result_chart->num_rows > 0) {
        while($row = $result_chart->fetch_assoc()) {
            $mese_index = (int)$row['mese_num'] - 1;
            $chart_data[$mese_index] = (int)$row['conteggio'];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['data' => $chart_data]);
    exit; 
}

// =========================================================================
// CARICAMENTO NORMALE DELLA PAGINA
// =========================================================================

// 1. QUERY PER IL CALENDARIO
$query_visite = "
    SELECT v.data, p.nome, p.cognome 
    FROM visita v 
    JOIN paziente p ON v.fk_paziente = p.id
";
$result_visite = $conn->query($query_visite);

$query_appuntamenti = "
    SELECT id, data_appuntamento, titolo, ora_inizio, ora_fine
    FROM appuntamenti
";
$result_appuntamenti = $conn->query($query_appuntamenti);

$eventi_calendario = [];
if ($result_visite && $result_visite->num_rows > 0) {
    while($row = $result_visite->fetch_assoc()) {
        $eventi_calendario[] = [
            'id' => 'vis_' . $row['data'] . '_' . md5($row['nome'] . $row['cognome']),
            'title' => 'Visita: ' . $row['nome'] . ' ' . $row['cognome'],
            'start' => $row['data'],
            'allDay' => true,
            'color' => 'var(--primary-color)',
            'extendedProps' => [
                'tipo' => 'visita'
            ]
        ];
    }
}

if ($result_appuntamenti && $result_appuntamenti->num_rows > 0) {
    while($row = $result_appuntamenti->fetch_assoc()) {
        $eventi_calendario[] = [
            'id' => 'app_' . $row['id'],
            'title' => $row['titolo'],
            'start' => $row['data_appuntamento'] . 'T' . substr($row['ora_inizio'], 0, 5),
            'end' => $row['data_appuntamento'] . 'T' . substr($row['ora_fine'], 0, 5),
            'allDay' => false,
            'color' => '#10b981',
            'extendedProps' => [
                'tipo' => 'appuntamento',
                'data_appuntamento' => $row['data_appuntamento'],
                'ora_inizio' => substr($row['ora_inizio'], 0, 5),
                'ora_fine' => substr($row['ora_fine'], 0, 5)
            ]
        ];
    }
}
$eventi_json = json_encode($eventi_calendario);

// 2. QUERY PER LE ATTIVITÀ RECENTI
$query_attivita = "
    SELECT v.data, p.nome, p.cognome, o.osservazione as note
    FROM visita v
    JOIN paziente p ON v.fk_paziente = p.id
    LEFT JOIN osservazioni_finali o ON v.id = o.fk_visita
    ORDER BY v.data DESC LIMIT 6
";
$result_attivita = $conn->query($query_attivita);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Igea - Dashboard</title>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* VARIABILI CSS PER DARK / LIGHT MODE */
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: #e2e8f0;
            --primary-color: #6366f1;
            /* Azzurro scuro/blu per la modalità chiara */
            --sidebar-bg: #1d4ed8; 
            --sidebar-text: #ffffff;
        }

        body.dark-mode {
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            /* Grigio scurissimo/blu notte per la modalità scura */
            --sidebar-bg: #0b1120;
        }

        /* LAYOUT PRINCIPALE */
        html, body {
            height: 100%;
            margin: 0;
            overflow: auto;
            font-family: Arial, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
        }

        body { display: flex; }

        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 20px;
            box-sizing: border-box;
            transition: background-color 0.3s;
        }
        .sidebar h1 { margin-top: 0; color: #fff; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 10px 0; }
        .sidebar a:hover, .sidebar a.active { color: #fff; font-weight: bold; }

        .main-content {
            flex: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            padding: 10px 20px 20px 20px; 
            overflow: hidden;
        }

        .top-section { flex-shrink: 0; }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .header-top h1 { margin: 0; font-size: 2rem; }

        .action-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .btn-azione {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 0 15px;
            border-radius: 5px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        /* STILE DELLA RICERCA E DEL DROPDOWN AJAX */
        .search-container {
            position: relative;
            width: 290px;
            height: 35px;
        }

        .search-box-btn-style {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-card);
            color: var(--text-main);
            font-size: 0.9rem;
            width: 100%;
            outline: none;
            height: 35px;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        
        .search-box-btn-style:focus { border-color: var(--primary-color); }

        #risultatiRicerca {
            position: absolute;
            top: calc(100% + 4px); /* Si posiziona appena sotto la barra */
            left: 0;
            width: 100%;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            max-height: 250px;
            overflow-y: auto;
            display: none; /* Nascosto di default */
        }

        #risultatiRicerca:not(:empty) {
            display: block; /* Mostra se ci sono contenuti */
        }

        /* Formattazione base dei risultati restituiti da ajax_cerca_paziente.php */
        #risultatiRicerca div, #risultatiRicerca a {
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            text-decoration: none;
            display: block;
            cursor: pointer;
        }
        #risultatiRicerca div:last-child, #risultatiRicerca a:last-child {
            border-bottom: none;
        }
        #risultatiRicerca div:hover, #risultatiRicerca a:hover {
            background-color: var(--bg-page);
        }

        /* GRIGLIA DASHBOARD */
        .dashboard-grid {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(320px, 380px) 1fr;
            gap: 20px;
            min-height: 0;
            height: 100%;
        }

        .left-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: 100%;
            min-height: 0;
        }

        .card-cruscotto {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 0;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .left-column .card-cruscotto { 
            flex: 1; overflow: hidden; 
        }

        .card-cruscotto h2 { 
            margin-top: 0; 
            font-size: 1.2rem; 
            margin-bottom: 15px; 
        }

        .card-cruscotto.calendar-card {
            min-height: 560px;
            align-self: start;
            grid-column: 2;
            margin-top: -10px;
        }

        #calendar {
            flex: 1;
            min-height: 520px;
            width: 100%;
        }

        .lista-attivita {
            flex: 1;
            overflow-y: auto;
            margin: 0;
            padding: 0;
            list-style-type: none;
        }
        .lista-attivita li {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        .lista-attivita li:last-child { border-bottom: none; }

        .chart-container { flex: 1; position: relative; min-height: 0; width: 100%; }
        /* OVERRIDE FULLCALENDAR */
        .fc { 
            --fc-today-bg-color: rgba(99, 102, 241, 0.15);
            --fc-border-color: var(--border-color);
            --fc-page-bg-color: var(--bg-card);
            --fc-neutral-text-color: var(--text-main);
        }
        
        .fc .fc-button-primary {
            background: linear-gradient(135deg, #6366f1, #0ea5e9) !important;
            border: none !important;
            text-transform: capitalize;
            font-weight: 600 !important;
        }

        .fc-toolbar-chunk { display: flex; gap: 15px; align-items: center; }
        .fc .fc-button-group { display: flex; gap: 8px; }
        .fc .fc-button-group > .fc-button { border-radius: 5px !important; margin: 0 !important; }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            position: relative;
            width: 100%;
            max-width: 480px;
            background: var(--bg-card);
            border-radius: 14px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            padding: 25px 60px;
        }

        .modal h2 {
            font-size: 1.35rem;
            margin-bottom: 15px;
            color: var(--text-main);
        }

        .modal label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            color: var(--text-main);
        }

        .modal input[type="text"],
        .modal input[type="date"],
        .modal input[type="time"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-page);
            color: var(--text-main);
            margin-bottom: 15px;
        }

        .modal textarea {
            width: 100%;
            min-height: 90px;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            resize: vertical;
            background: var(--bg-page);
            color: var(--text-main);
            margin-bottom: 15px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal-actions button {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
        }

        .modal-actions .btn-cancel {
            background: #ff4444;
            color: white;
        }

        .modal-actions .btn-save {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .modal-close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: var(--bg-card);
            color: var(--text-main);
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        /* HEADER GRAFICO */
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .chart-btn {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 5px;
            padding: 5px 12px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .chart-btn:hover { background-color: var(--border-color); }
        .chart-title { margin: 0; font-size: 0.95rem; font-weight: bold; }
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
            <a href="index.php" class="active">Home</a>
            <a href="pazienti.php">Pazienti</a>
            <a href="farmaci.php">Terapie</a>
            <a href="alimenti.php">Alimenti</a>
        </nav>
    </aside>

    <main class="main-content">
        
        <div class="top-section">
            <div class="header-top">
                <h1>Dashboard</h1>
                <button id="themeToggle" class="search-box-btn-style" style="width: auto; cursor: pointer; padding: 8px 20px;">
                    🌓 Cambia Tema
                </button>
            </div>
            
            <div class="action-bar">
                <a href="nuovo_paziente.php" class="btn-azione">+ Nuovo Paziente</a>
                <a href="#" class="btn-azione" id="openAppointmentModalBtn">+ Appuntamento</a>
            </div>

            <div class="search-bar">
                <div class="search-container">
                    <input type="text" id="cercaPaziente" class="search-box-btn-style" placeholder="Cerca paziente..." onkeyup="cerca()">
                    <div id="risultatiRicerca"></div>
                </div>
            </div>

            <div id="appointmentModal" class="modal-overlay">
                <div class="modal">
                    <button type="button" class="modal-close" id="closeAppointmentModal">×</button>
                    <h2>Nuovo appuntamento</h2>
                    <form id="appointmentForm" method="POST" action="salva_appuntamento.php">
                        <input type="hidden" id="appointment_id" name="appointment_id" value="">
                        <input type="hidden" id="form_action" name="form_action" value="save">

                        <label for="data_appuntamento">Data</label>
                        <input type="date" id="data_appuntamento" name="data_appuntamento" required>

                        <label for="titolo">Titolo</label>
                        <input type="text" id="titolo" name="titolo" placeholder="Es. Visita paziente" required>

                        <label for="ora_inizio">Ora inizio</label>
                        <input type="time" id="ora_inizio" name="ora_inizio" required>

                        <label for="ora_fine">Ora fine</label>
                        <input type="time" id="ora_fine" name="ora_fine" required>

                        <div class="modal-actions">
                            <button type="button" class="btn-cancel" id="cancelAppointment">Annulla</button>
                            <button type="button" class="btn-cancel" id="deleteAppointment" style="display:none;">Elimina</button>
                            <button type="submit" class="btn-save">Salva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="left-column">
                <div class="card-cruscotto">
                    <h2>Attività Recenti</h2>
                    <ul class="lista-attivita">
                        <?php 
                        if ($result_attivita && $result_attivita->num_rows > 0) {
                            while($row = $result_attivita->fetch_assoc()) {
                                $data_formattata = date("d/m/Y", strtotime($row['data']));
                                $nome_paziente = htmlspecialchars($row['nome'] . " " . $row['cognome']);
                                $note = !empty($row['note']) ? htmlspecialchars($row['note']) : "Nessuna nota registrata.";
                                
                                echo "<li>";
                                echo "<span style='font-size: 0.85rem; color: var(--primary-color); font-weight: bold;'>" . $data_formattata . "</span>";
                                echo "<strong style='display: block; font-size: 1.05rem; margin: 4px 0;'>" . $nome_paziente . "</strong>";
                                echo "<span style='font-size: 0.9rem; color: var(--text-muted);'>" . $note . "</span>";
                                echo "</li>";
                            }
                        } else {
                            echo "<li style='color: var(--text-muted);'>Nessuna attività recente.</li>";
                        }
                        ?>
                    </ul>
                </div>

                <div class="card-cruscotto">
                    <h2>Prossimi Appuntamenti</h2>
                    <ul class="lista-attivita">
                        <?php
                        $query_appuntamenti_prossimi = "
                            SELECT data_appuntamento, titolo, ora_inizio, ora_fine
                            FROM appuntamenti
                            WHERE data_appuntamento >= CURDATE()
                            ORDER BY data_appuntamento ASC, ora_inizio ASC
                            LIMIT 6
                        ";
                        $result_appuntamenti_prossimi = $conn->query($query_appuntamenti_prossimi);

                        if ($result_appuntamenti_prossimi && $result_appuntamenti_prossimi->num_rows > 0) {
                            while($row = $result_appuntamenti_prossimi->fetch_assoc()) {
                                $data_formattata = date("d/m/Y", strtotime($row['data_appuntamento']));
                                $orario = substr($row['ora_inizio'], 0, 5) . ' - ' . substr($row['ora_fine'], 0, 5);
                                echo "<li>";
                                echo "<span style='font-size: 0.85rem; color: var(--primary-color); font-weight: bold;'>" . $data_formattata . "</span>";
                                echo "<strong style='display: block; font-size: 1.05rem; margin: 4px 0;'>" . htmlspecialchars($row['titolo']) . "</strong>";
                                echo "<span style='font-size: 0.9rem; color: var(--text-muted);'>" . $orario . "</span>";
                                echo "</li>";
                            }
                        } else {
                            echo "<li style='color: var(--text-muted);'>Nessun appuntamento programmato.</li>";
                        }
                        ?>
                    </ul>
                </div>

                <div class="card-cruscotto">
                    <h2>Statistiche Visite</h2>
                    <div class="chart-header">
                        <button id="btnPrevYear" class="chart-btn">&laquo; Anno Prec.</button>
                        <span id="chartYearDisplay" class="chart-title"></span>
                        <button id="btnNextYear" class="chart-btn">Anno Succ. &raquo;</button>
                    </div>
                    <div class="chart-container">
                        <canvas id="visiteChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card-cruscotto calendar-card">
                <div id="calendar"></div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ==========================================
            // 1. INIZIALIZZAZIONE CALENDARIO (Solo Lettura)
            // ==========================================
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'it',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                selectable: true,
                dateClick: function(info) {
                    openAppointmentModal(info.dateStr);
                },
                eventClick: function(info) {
                    if (info.event.extendedProps.tipo === 'appuntamento') {
                        openAppointmentModal(info.event.startStr.slice(0, 10), {
                            id: info.event.id,
                            titolo: info.event.title,
                            data_appuntamento: info.event.extendedProps.data_appuntamento,
                            ora_inizio: info.event.extendedProps.ora_inizio,
                            ora_fine: info.event.extendedProps.ora_fine
                        });
                    }
                },
                events: <?php echo $eventi_json; ?>,
                eventDisplay: 'block',
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                height: '100%'
            });
            calendar.render();

            function openAppointmentModal(dateStr, eventData = null) {
                var modal = document.getElementById('appointmentModal');
                var dataInput = document.getElementById('data_appuntamento');
                var titoloInput = document.getElementById('titolo');
                var oraInizioInput = document.getElementById('ora_inizio');
                var oraFineInput = document.getElementById('ora_fine');
                var appointmentIdInput = document.getElementById('appointment_id');
                var formActionInput = document.getElementById('form_action');
                var deleteButton = document.getElementById('deleteAppointment');

                if (eventData) {
                    dataInput.value = eventData.data_appuntamento;
                    titoloInput.value = eventData.titolo;
                    oraInizioInput.value = eventData.ora_inizio;
                    oraFineInput.value = eventData.ora_fine;
                    appointmentIdInput.value = eventData.id.replace('app_', '');
                    formActionInput.value = 'edit';
                    deleteButton.style.display = 'inline-flex';
                } else {
                    dataInput.value = dateStr;
                    titoloInput.value = '';
                    oraInizioInput.value = '';
                    oraFineInput.value = '';
                    appointmentIdInput.value = '';
                    formActionInput.value = 'save';
                    deleteButton.style.display = 'none';
                }

                modal.classList.add('active');
            }

            function closeAppointmentModal() {
                var modal = document.getElementById('appointmentModal');
                modal.classList.remove('active');
            }

            document.getElementById('openAppointmentModalBtn').addEventListener('click', function(event) {
                event.preventDefault();
                openAppointmentModal(new Date().toISOString().slice(0, 10));
            });

            document.getElementById('closeAppointmentModal').addEventListener('click', closeAppointmentModal);
            document.getElementById('cancelAppointment').addEventListener('click', closeAppointmentModal);
            document.getElementById('appointmentModal').addEventListener('click', function(event) {
                if (event.target === this) {
                    closeAppointmentModal();
                }
            });

            var appointmentForm = document.getElementById('appointmentForm');
            var appointmentIdInput = document.getElementById('appointment_id');
            var formActionInput = document.getElementById('form_action');
            var deleteAppointmentBtn = document.getElementById('deleteAppointment');

            appointmentForm.addEventListener('submit', function() {
                if (appointmentIdInput.value) {
                    formActionInput.value = 'edit';
                } else {
                    formActionInput.value = 'save';
                }
            });

            deleteAppointmentBtn.addEventListener('click', function() {
                if (!appointmentIdInput.value) {
                    return;
                }
                if (confirm('Sei sicuro di eliminare questo appuntamento?')) {
                    formActionInput.value = 'delete';
                    appointmentForm.submit();
                }
            });

            // ==========================================
            // 2. INIZIALIZZAZIONE E GESTIONE GRAFICO
            // ==========================================
            let currentChartYear = new Date().getFullYear();
            let visiteChartInstance = null;

            function caricaDatiGrafico(anno) {
                document.getElementById('chartYearDisplay').innerText = anno;

                fetch('index.php?ajax_chart=1&anno=' + anno)
                    .then(response => response.json())
                    .then(json => {
                        const chartData = json.data;
                        const chartLabels = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];

                        if (visiteChartInstance) {
                            visiteChartInstance.data.datasets[0].data = chartData;
                            visiteChartInstance.update();
                        } else {
                            const ctx = document.getElementById('visiteChart').getContext('2d');
                            Chart.defaults.color = getComputedStyle(document.body).getPropertyValue('--text-muted').trim();
                            Chart.defaults.borderColor = getComputedStyle(document.body).getPropertyValue('--border-color').trim();

                            visiteChartInstance = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: chartLabels,
                                    datasets: [{
                                        label: 'Visite effettuate',
                                        data: chartData,
                                        backgroundColor: '#6366f1',
                                        borderRadius: 4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: { 
                                        y: { 
                                            beginAtZero: true, 
                                            ticks: { stepSize: 1, precision: 0 } 
                                        } 
                                    }
                                }
                            });
                        }
                    })
                    .catch(error => console.error("Errore nel caricamento dei dati del grafico:", error));
            }

            caricaDatiGrafico(currentChartYear);

            document.getElementById('btnPrevYear').addEventListener('click', () => {
                currentChartYear--;
                caricaDatiGrafico(currentChartYear);
            });
            document.getElementById('btnNextYear').addEventListener('click', () => {
                currentChartYear++;
                caricaDatiGrafico(currentChartYear);
            });

            // ==========================================
            // 3. LOGICA DARK/LIGHT MODE CON SALVATAGGIO
            // ==========================================
            const themeToggleBtn = document.getElementById('themeToggle');
            const bodyClass = document.body.classList;

            themeToggleBtn.addEventListener('click', () => {
                bodyClass.toggle('dark-mode');
                const isDark = bodyClass.contains('dark-mode');
                
                // Salva nel browser la preferenza in modo che valga per tutte le pagine
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                
                Chart.defaults.color = isDark ? '#94a3b8' : '#475569';
                Chart.defaults.borderColor = isDark ? '#334155' : '#e2e8f0';
                if(visiteChartInstance) visiteChartInstance.update();
            });
        });

        // ==========================================
        // 4. FUNZIONE RICERCA PAZIENTE
        // ==========================================
        function cerca() {
            var testo = document.getElementById("cercaPaziente").value;
            var risultatiBox = document.getElementById("risultatiRicerca");
            
            if (testo.length > 0) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "ajax_cerca_paziente.php?q=" + testo, true);
                xhr.onload = function() {
                    if (this.status == 200) {
                        risultatiBox.innerHTML = this.responseText;
                    }
                }
                xhr.send();
            } else {
                risultatiBox.innerHTML = "";
            }
        }

        // Chiude i risultati di ricerca se si clicca fuori
        document.addEventListener("click", function(event) {
            var inputCerca = document.getElementById("cercaPaziente");
            var risultatiBox = document.getElementById("risultatiRicerca");
            if (event.target !== inputCerca && event.target !== risultatiBox) {
                risultatiBox.innerHTML = ""; 
                inputCerca.value = ""; // Opzionale: svuota il campo se annulli
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>