<?php
require_once 'Klassen/fragebogen.php';
require_once 'config.php';


// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus URL-Parametern abrufen
if (isset($_GET['fragebogen_id'])) {
    $fragebogenId = $_GET['fragebogen_id'];

    // Transaktion starten
    $conn->begin_transaction();

    try {
        // 1. Antwortenkombination_antwort löschen
        $sqlAntwortenKombinationAntwortLoeschen = "DELETE FROM antwortkombination_antwort WHERE antwortkombination_id IN (SELECT id FROM antwortkombination WHERE frage_id IN (SELECT id FROM frage WHERE fragebogen_id = ?))";
        $stmtAntwortenKombinationAntwortLoeschen = $conn->prepare($sqlAntwortenKombinationAntwortLoeschen);
        $stmtAntwortenKombinationAntwortLoeschen->bind_param("i", $fragebogenId);
        if (!$stmtAntwortenKombinationAntwortLoeschen->execute()) {
            throw new Exception("Fehler beim Löschen von Antwortenkombination_antwort: " . $stmtAntwortenKombinationAntwortLoeschen->error);
        }

        // 2. Antwortkombination löschen
        $sqlAntwortkombinationLoeschen = "DELETE FROM antwortkombination WHERE frage_id IN (SELECT id FROM frage WHERE fragebogen_id = ?)";
        $stmtAntwortkombinationLoeschen = $conn->prepare($sqlAntwortkombinationLoeschen);
        $stmtAntwortkombinationLoeschen->bind_param("i", $fragebogenId);
        if (!$stmtAntwortkombinationLoeschen->execute()) {
            throw new Exception("Fehler beim Löschen von Antwortkombination: " . $stmtAntwortkombinationLoeschen->error);
        }

        // 3. Antworten löschen
        $sqlAntwortenLoeschen = "DELETE FROM antwort WHERE frage_id IN (SELECT id FROM frage WHERE fragebogen_id = ?)";
        $stmtAntwortenLoeschen = $conn->prepare($sqlAntwortenLoeschen);
        $stmtAntwortenLoeschen->bind_param("i", $fragebogenId);
        if (!$stmtAntwortenLoeschen->execute()) {
            throw new Exception("Fehler beim Löschen von Antworten: " . $stmtAntwortenLoeschen->error);
        }

        // 4. Fragen löschen
        $sqlFragenLoeschen = "DELETE FROM frage WHERE fragebogen_id = ?";
        $stmtFragenLoeschen = $conn->prepare($sqlFragenLoeschen);
        $stmtFragenLoeschen->bind_param("i", $fragebogenId);
        if (!$stmtFragenLoeschen->execute()) {
            throw new Exception("Fehler beim Löschen von Fragen: " . $stmtFragenLoeschen->error);
        }

        // 5. Fragebogen löschen
        $sqlFragebogenLoeschen = "DELETE FROM fragebogen WHERE id = ?";
        $stmtFragebogenLoeschen = $conn->prepare($sqlFragebogenLoeschen);
        $stmtFragebogenLoeschen->bind_param("i", $fragebogenId);
        if (!$stmtFragebogenLoeschen->execute()) {
            throw new Exception("Fehler beim Löschen des Fragebogens: " . $stmtFragebogenLoeschen->error);
        }

        // Transaktion bestätigen
        $conn->commit();
        echo "Fragebogen und alle zugehörigen Daten erfolgreich gelöscht.";
    } catch (Exception $e) {
        // Bei einem Fehler die Transaktion rückgängig machen
        $conn->rollback();
        echo "Fehler beim Löschen des Fragebogens: " . $e->getMessage();
    }

} else {
    echo "Keine Fragebogen-ID angegeben.";
}

$conn->close();
?>