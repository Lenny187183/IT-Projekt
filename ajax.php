<?php
require_once 'config.php';
require_once 'Klassen/verzweigung.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

if (isset($_POST['antwortId'])) {
    $antwortId = intval($_POST['antwortId']);
    
    // Abfrage nach der Folgefrage anhand der Antwort-ID
    $stmt = $conn->prepare("SELECT folgefrage_id FROM verzweigung WHERE antwort_id = ?");
    $stmt->bind_param("i", $antwortId);
    $stmt->execute();
    $stmt->bind_result($folgefrageId);
    $stmt->fetch();

    echo json_encode(['folgefrage_id' => $folgefrageId ? $folgefrageId : null]);
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'Keine Antwort-ID übermittelt']);
}

$conn->close();
?>