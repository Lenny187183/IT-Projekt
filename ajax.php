<?php
require_once 'config.php';
require_once 'Klassen/verzweigung.php';

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

if (isset($_POST['antwortId'])) {
    $antwortId = $_POST['antwortId'];

    $verzweigung = new Verzweigung();
    if ($verzweigung->ladenAusDatenbankMitAntwortId($conn, $antwortId)) {
        $folgefrageId = $verzweigung->getFolgefrageId();
    } else {
        $folgefrageId = null; // Oder eine andere Fehlerbehandlung
    }

    echo json_encode(['folgefrage_id' => $folgefrageId]);
}

$conn->close();
?>