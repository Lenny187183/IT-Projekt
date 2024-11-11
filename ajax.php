<?php
require_once 'config.php'; 

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

if (isset($_POST['antwortId'])) {
    $antwortId = $_POST['antwortId'];

    // SQL-Abfrage, um die folgefrage_id zu ermitteln
    $sql = "SELECT folgefrage_id FROM verzweigungen WHERE antwort_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $antwortId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $folgefrageId = $row['folgefrage_id'];
    } else {
        $folgefrageId = null; // Oder eine andere Aktion, wenn keine Folgefrage gefunden wird
    }

    // Gib die folgefrage_id als JSON zurück
    echo json_encode(['folgefrage_id' => $folgefrageId]);
}

$conn->close();
?>