<?php
require_once 'Klassen/Fragebogen.php';
require_once 'Klassen/Frage.php';
require_once 'Klassen/Antwort.php';

// Datenbankverbindung
$conn = new mysqli('localhost', 'testserver', '123', 'fragen');

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus der URL holen
$fragebogenId = isset($_GET['fragebogen_id']) ? $_GET['fragebogen_id'] : null;

if ($fragebogenId) {
    // Fragebogen laden
    $fragebogenObjekt = new Fragebogen();
    $fragebogenObjekt->ladenAusDatenbank($conn, $fragebogenId);

    // Überprüfen, ob der Fragebogen erfolgreich geladen wurde
    if ($fragebogenObjekt->getId() !== null) {
        $fragebogenTitel = $fragebogenObjekt->getTitel();

        // Fragen für den Fragebogen laden
        $frage = new Frage();
        $fragen = $frage->ladenFragenFuerFragebogen($conn, $fragebogenId);
    } else {
        $fragebogenTitel = "Fragebogen nicht gefunden";
        $fragen = [];
    }
} else {
    $fragebogenTitel = "Kein Fragebogen ausgewählt";
    $fragen = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fragebogen anzeigen</title>
    <link rel="stylesheet" href="schön.css">
</head>
<body>
    <h1><?php echo $fragebogenTitel; ?></h1>

    <?php foreach ($fragen as $frage): ?>
        <div class="frage">
            <h3><?php echo $frage['fragetext']; ?></h3>

            <ul>
                <?php
                // Antworten zur Frage laden
                $antwort = new Antwort();
                $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);

                foreach ($antworten as $antwort):
                ?>
                    <li><?php echo $antwort['antworttext']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>

    <a href="FragebogenErstellen.php">Zurück zur Auswahl</a>
</body>
</html>