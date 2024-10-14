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
        echo "Aktiver Fragebogen erfolgreich gesetzt.";
    } else {
        echo "Fehler beim Setzen des aktiven Fragebogens: " . $stmtAktivSetzen->error;
    }
} else {
    echo "Keine Fragebogen-ID angegeben.";
}

$conn->close();
?>