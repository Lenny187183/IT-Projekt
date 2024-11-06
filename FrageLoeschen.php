<?php
require_once 'Klassen/Frage.php';
require_once 'config.php';

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Frage-ID aus POST-Daten abrufen
if (isset($_POST['frage_id'])) {
    $frageId = $_POST['frage_id'];

    try {
        // Frage löschen
        $sql = "DELETE FROM frage WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $frageId);

        if ($stmt->execute()) {
            // Erfolgsmeldung
            $frage = new Frage();
            $frage->ladenAusDatenbank($conn, $frageId);
            $fragetext = $frage->getFragetext();
            echo "Frage '$fragetext' erfolgreich gelöscht!";

            // Weiterleitung zur AdminSicht (optional)
            // Du musst die fragebogen_id aus der vorherigen Seite mitgeben
            if (isset($_POST['fragebogen_id'])) {
                $fragebogenId = $_POST['fragebogen_id'];
                 header("Location: AdminSicht.php?fragebogen_id=$fragebogenId"); 
                exit();
            }
        } else {
            // Fehlermeldung mit detaillierten Informationen
            throw new Exception("Fehler beim Löschen der Frage: " . $stmt->error . " (SQLSTATE: " . $stmt->sqlstate . ")");
        }
    } catch (mysqli_sql_exception $e) {
        echo "Fehler beim Löschen der Frage: " . $e->getMessage();
    }
} else {
    echo "Keine Frage-ID angegeben.";
}

$conn->close();
?>

