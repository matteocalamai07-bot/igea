<?php
session_start();

// Connessione database
$conn = new mysqli("localhost", "root", "", "terranova");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// 1. QUERY PER IL CALENDARIO
$query_visite = "
    SELECT v.data, p.nome, p.cognome 
    FROM visita v 
    JOIN paziente p ON v.fk_paziente = p.id
";
$result_visite = $conn->query($query_visite);

$eventi_calendario = [];
if ($result_visite && $result_visite->num_rows > 0) {
    while($row = $result_visite->fetch_assoc()) {
        $eventi_calendario[] = [
            'title' => 'Visita: ' . $row['nome'] . ' ' . $row['cognome'],
            'start' => $row['data'],
            'allDay' => true,
            'color' => '#6366f1' // Colore dei pallini in tinta col sito
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
    <link rel="stylesheet" href="style.css">
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          locale: 'it',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
          },
          events: <?php echo $eventi_json; ?>,
          height: 600
        });
        calendar.render();
      });
    </script>

    <style>
        /* Layout per Bottone e Ricerca affiancati */
        .action-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        /* Stile barra di ricerca identico al bottone */
        .search-box-btn-style {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid rgba(15,23,42,0.15);
            font-size: 0.9rem;
            width: 250px;
            background: rgba(255,255,255,0.8);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            height: 35px;
            box-sizing: border-box;
        }

        .search-box-btn-style:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.2);
        }

        .search-wrapper {
            position: relative;
            height: 35px;
        }

        #risultatiRicerca {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            margin-top: 5px;
            z-index: 1000;
        }

        /* =========================================
           OVERRIDE COLORI E SPAZI FULLCALENDAR
        ========================================= */
        .fc {
            --fc-today-bg-color: rgba(99, 102, 241, 0.08); 
        }
        
        /* Stile base dei bottoni */
        .fc .fc-button-primary {
            background: linear-gradient(135deg, #6366f1, #0ea5e9) !important;
            border: none !important;
            box-shadow: 0 4px 10px rgba(99,102,241,0.2) !important;
            border-radius: 5px !important;
            text-transform: capitalize;
            font-weight: 600 !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
            margin: 0 !important; /* Rimuove i margini nativi di fullcalendar */
        }

        /* Hover dei bottoni */
        .fc .fc-button-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(14,165,233,0.4) !important;
        }

        /* Stato attivo dei bottoni */
        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: #4f46e5 !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2) !important;
        }

        /* SPAZIO TRA I BOTTONI (La modifica che hai chiesto!) */
        .fc .fc-button-group {
            display: flex;
            gap: 10px; /* Aggiunge spazio tra i tasti uniti */
        }
        
        /* Assicura che anche i bottoni interni al gruppo abbiano i bordi arrotondati */
        .fc .fc-button-group > .fc-button {
            border-radius: 5px !important; 
        }

        /* Spazio tra il blocco frecce e il tasto 'oggi' */
        .fc-toolbar-chunk {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>

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
        
        <div>
            <h1 style="font-size: 2rem; color: #0f172a; margin-bottom: 15px; margin-top: 0;">Dashboard</h1>
            
            <div class="action-bar">
                <a href="nuovo_paziente.php" class="btn-azione" style="height: 35px; box-sizing: border-box; display: flex; align-items: center; justify-content: center;">+ Nuovo Paziente</a>
                
                <div class="search-wrapper">
                    <input type="text" id="cercaPaziente" class="search-box-btn-style" placeholder="Cerca paziente..." onkeyup="cerca()">
                    <div id="risultatiRicerca"></div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            
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
                            echo "<span style='font-size: 0.85rem; color: #6366f1; font-weight: bold;'>" . $data_formattata . "</span>";
                            echo "<strong style='display: block; font-size: 1.05rem; color: #0f172a; margin: 4px 0;'>" . $nome_paziente . "</strong>";
                            echo "<span style='font-size: 0.9rem; color: #475569;'>" . $note . "</span>";
                            echo "</li>";
                        }
                    } else {
                        echo "<li>Nessuna attività recente registrata.</li>";
                    }
                    ?>
                </ul>
            </div>

            <div class="card-cruscotto">
                <div id="calendar"></div>
            </div>

        </div>

    </main>

    <script>
        function cerca() {
            var testo = document.getElementById("cercaPaziente").value;
            if (testo.length > 1) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "ajax_cerca_paziente.php?q=" + testo, true);
                xhr.onload = function() {
                    if (this.status == 200) {
                        document.getElementById("risultatiRicerca").innerHTML = this.responseText;
                    }
                }
                xhr.send();
            } else {
                document.getElementById("risultatiRicerca").innerHTML = "";
            }
        }
    </script>

</body>
</html>
<?php $conn->close(); ?>
