<?php
require_once 'Klassen/Antwortkombination.php';
require_once 'config.php';


// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Antworten aus dem Formular holen
$antworten = isset($_POST['antworten']) ? $_POST['antworten'] : []; 

if (is_array($antworten) && !empty($antworten)) {
    $antwortkombinationen = isset($_POST['antwortkombinationen']) ? $_POST['antwortkombinationen'] : [];

    // Alle relevanten Antwortkombinationen abrufen
    $sql = "SELECT ak.id, ak.ziel_url 
            FROM antwortkombination ak
            JOIN antwortkombination_antwort aka ON ak.id = aka.antwortkombination_id
            WHERE aka.antwort_id IN (" . implode(',', $antworten) . ")
            GROUP BY ak.id, ak.ziel_url";

    $result = $conn->query($sql);
    $weiterleitungGefunden = false;

    while ($row = $result->fetch_assoc()) {
        $kombinationId = $row['id'];
        $zielUrl = $row['ziel_url'];

        // Überprüfen, ob alle Antworten dieser Kombination ausgewählt wurden
        $alleAntwortenAusgewaehlt = true;
        foreach ($antwortkombinationen as $antwortId => $kombinationIds) {
            if (in_array($kombinationId, $kombinationIds) && !in_array($antwortId, $antworten)) {
                $alleAntwortenAusgewaehlt = false;
                break;
            }
        }

        if ($alleAntwortenAusgewaehlt) {
            $weiterleitungGefunden = true;
            header("Location: " . $zielUrl);
            exit();
        }
    }

    if (!$weiterleitungGefunden) {
        echo "Keine passende oder eindeutige Weiterleitung gefunden."; 
    }
} else {
    // Fehlerbehandlung
    echo "Fehler: Bitte wählen Sie mindestens eine Antwort aus.";
}

$conn->close();
?>