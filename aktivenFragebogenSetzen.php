<?php
require_once 'Klassen/fragebogen.php';
require_once 'config.php';

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); // Passe die Verbindungsdaten an

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

if (isset($_POST['fragebogen_id'])) {
    $fragebogenId = $_POST['fragebogen_id'];

    // Aktiven Fragebogen setzen
    $sqlAktivSetzen = "UPDATE fragebogen SET aktiv = IF(id = ?, TRUE, FALSE)";
    $stmtAktivSetzen = $conn->prepare($sqlAktivSetzen);
    $stmtAktivSetzen->bind_param("i", $fragebogenId);

    if ($stmtAktivSetzen->execute()) {
        // Weiterleitung zu FragebogenErstellen.php
        header("Location: FragebogenErstellen.php"); 
        exit();
    } else {
        // JavaScript-Code zum Anzeigen des Popups mit Fehlermeldung
        echo '<script>alert("Fehler beim Setzen des aktiven Fragebogens: ' . $stmtAktivSetzen->error . '");</script>'; 
    }
} else {
    // JavaScript-Code zum Anzeigen des Popups mit Fehlermeldung
    echo '<script>alert("Keine Fragebogen-ID angegeben.");</script>'; 
}

$conn->close();
?>