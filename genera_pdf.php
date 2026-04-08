<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

$conn = new mysqli("localhost","root","","terranova");
if ($conn->connect_error) die("Errore connessione");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID non valido");
}

$id = intval($_GET['id']);

/* =========================
   QUERY DATI
========================= */
$visita = $conn->query("
SELECT v.*, p.nome, p.cognome
FROM visita v
JOIN paziente p ON v.fk_paziente=p.id
WHERE v.id=$id
")->fetch_assoc();

$sonno = $conn->query("SELECT * FROM sonno WHERE fk_visita=$id")->fetch_assoc();
$stato = $conn->query("SELECT * FROM `stato_psico-fisico` WHERE fk_visita=$id")->fetch_assoc();

$domande = $conn->query("SELECT * FROM domande WHERE fk_visita=$id");
$osservazioni = $conn->query("SELECT * FROM osservazioni_finali WHERE fk_visita=$id");

/* DATA */
$data = date("d/m/Y");

/* =========================
   HTML PRO
========================= */
$html = '
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #2c3e50;
}

.header {
    border-bottom: 2px solid #3498db;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

.logo {
    font-size: 20px;
    font-weight: bold;
    color: #3498db;
}

.title {
    font-size: 18px;
    margin-top: 5px;
}

.section {
    margin-bottom: 20px;
}

.section h2 {
    background: #3498db;
    color: white;
    padding: 5px;
    font-size: 14px;
}

.box {
    border: 1px solid #ddd;
    padding: 10px;
}

.row {
    margin-bottom: 5px;
}

.label {
    font-weight: bold;
}

ul {
    margin: 0;
    padding-left: 15px;
}

.footer {
    position: fixed;
    bottom: 0;
    text-align: center;
    font-size: 10px;
    color: gray;
}
</style>

<div class="header">
    <div class="logo">IGEA</div>
    <div class="title">Referto Visita Paziente</div>
    <div>Data: '.$data.'</div>
</div>

<div class="section">
<h2>Dati Paziente</h2>
<div class="box">
<div class="row"><span class="label">Nome:</span> '.$visita['nome'].' '.$visita['cognome'].'</div>
</div>
</div>

<div class="section">
<h2>Valutazione Generale</h2>
<div class="box">
<div class="row"><span class="label">Stress:</span> '.$visita['livello_stress'].'</div>
<div class="row"><span class="label">Alimentazione:</span> '.$visita['alimentazione'].'</div>
</div>
</div>

<div class="section">
<h2>Qualità del Sonno</h2>
<div class="box">
<div class="row">Ore: '.$sonno['ore'].'</div>
<div class="row">Risvegli: '.$sonno['risvegli'].'</div>
<div class="row">Difficoltà: '.$sonno['difficolta'].'</div>
<div class="row">Qualità: '.$sonno['qualita'].'</div>
</div>
</div>

<div class="section">
<h2>Stato Psico-Fisico</h2>
<div class="box">
<div class="row">Ansia: '.$stato['ansia'].'</div>
<div class="row">Umore: '.$stato['umore'].'</div>
<div class="row">Motivazione: '.$stato['motivazione'].'</div>
<div class="row">Concentrazione: '.$stato['concentrazione'].'</div>
</div>
</div>

<div class="section">
<h2>Domande e Risposte</h2>
<div class="box">
<ul>';

while($d = $domande->fetch_assoc()){
    $html .= '<li><b>'.$d['domanda'].'</b><br>Risposta: '.$d['risposta'].'</li>';
}

$html .= '</ul>
</div>
</div>

<div class="section">
<h2>Osservazioni Finali</h2>
<div class="box">
<ul>';

while($o = $osservazioni->fetch_assoc()){
    $html .= '<li>'.$o['osservazione'].'</li>';
}

$html .= '</ul>
</div>
</div>

<div class="footer">
Referto generato automaticamente - IGEA
</div>
';

/* =========================
   GENERAZIONE PDF
========================= */
$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper("A4", "portrait");
$pdf->render();

/* DOWNLOAD */
$pdf->stream("referto_visita_$id.pdf", ["Attachment"=>true]);
exit;