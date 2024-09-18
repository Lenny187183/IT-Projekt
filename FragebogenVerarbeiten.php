<?php
require_once 'Klassen/Antwortkombination.php';

// Datenbankverbindung
$conn = new mysqli('localhost', 'testserver', '123', 'fragen'); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Antworten aus dem Formular holen
$antworten = isset($_POST['antworten']) ? $_POST['antworten'] : []; 

if (is_array($antworten) && !empty($antworten)) {
    // Antwortkombinationen abrufen, die zu den ausgewählten Antworten passen
    $sql = "SELECT ak.ziel_url 
            FROM antwortkombination ak
            JOIN antwortkombination_antwort aka ON ak.id = aka.antwortkombination_id
            JOIN antwort a ON aka.antwort_id = a.id 
            WHERE ";

    $whereClauses = [];
    $params = [];
    $types = "";

    foreach ($antworten as $frageId => $antwortIds) {
        // Ensure $antwortIds is an array
        if (!is_array($antwortIds)) {
            $antwortIds = [$antwortIds]; 
        }

        $whereClauses[] = "aka.antwort_id IN (" . implode(',', $antwortIds) . ") AND a.frage_id = ?";
        $params[] = $frageId;
        $types .= "i"; 
    }

    $sql .= implode(" AND ", $whereClauses);
    $sql .= " GROUP BY ak.id
              HAVING COUNT(DISTINCT aka.antwort_id) = " . count($antworten); 

    $stmt = $conn->prepare($sql);

    // Check if prepared statement is successful
    if($stmt === false) {
        die('Fehler bei der Abfragevorbereitung: ' . $conn->error);
    }

    $stmt->bind_param($types, ...$params); 
    $stmt->execute();
    $result = $stmt->get_result();

    // Weiterleitung zur Ziel-URL (falls eine passende Kombination gefunden wurde)
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header("Location: " . $row['ziel_url']);
        exit();
    } else {
        // Keine passende Kombination gefunden, zeige eine Meldung an oder leite zu einer Standardseite weiter
        echo "Keine passende Weiterleitung gefunden."; 
    }
} else {
    // Fehlerbehandlung, falls $antworten kein Array ist oder leer
    echo "Fehler: Bitte wählen Sie mindestens eine Antwort aus.";
}

$conn->close();
?>
Verwende den Code mit Vorsicht.