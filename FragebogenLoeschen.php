<?php
require_once 'Klassen/fragebogen.php';
require_once 'config.php';

session_start(); // Sitzung starten, um auf Benutzerdaten zuzugreifen

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus POST-Daten abrufen
if (isset($_POST['fragebogen_id'])) { 
    $fragebogenId = $_POST['fragebogen_id'];

    try {
        // Fragebogen löschen
        $sql = "DELETE FROM fragebogen WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $fragebogenId);

        if ($stmt->execute()) {
            // Erfolgsmeldung mit Fragebogentitel
            $fragebogen = new Fragebogen();
            $fragebogen->ladenAusDatenbank($conn, $fragebogenId);
            $fragebogenTitel = $fragebogen->getTitel();
            echo "Fragebogen '$fragebogenTitel' erfolgreich gelöscht!";

            // Weiterleitung zur Fragebogenübersicht
            header("Location: FragebogenErstellen.php"); 
            exit();
        } else {
            // Fehlermeldung mit detaillierten Informationen
            throw new Exception("Fehler beim Löschen des Fragebogens: " . $stmt->error . " (SQLSTATE: " . $stmt->sqlstate . ")");
        }
    } catch (mysqli_sql_exception $e) {
        echo "Fehler beim Löschen des Fragebogens: " . $e->getMessage();
    }
} else {
    echo "Keine Fragebogen-ID angegeben.";
}

$conn->close();
?>