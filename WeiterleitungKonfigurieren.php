<?php
require_once 'Klassen/Fragebogen.php';
require_once 'Klassen/Frage.php';
require_once 'Klassen/Antwort.php';
require_once 'Klassen/weiterleiten.php';
require_once 'Klassen/Antwortkombination.php';
require_once 'Klassen/antwortkombination_antwort.php';

// Datenbankverbindung
$conn = new mysqli('localhost', 'testserver', '123', 'fragen'); // Passe die Verbindungsdaten an

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus der URL holen
$fragebogenId = isset($_GET['fragebogen_id']) ? $_GET['fragebogen_id'] : null;

// Frage-ID aus der URL oder dem Formular holen
$frageId = isset($_GET['frage_id']) ? $_GET['frage_id'] : (isset($_POST['frage_id']) ? $_POST['frage_id'] : null);

$fragebogenTitel = "";
$fragetext = "";
$antworten = [];

if ($fragebogenId) {
    // Fragebogen laden
    $fragebogenObjekt = new Fragebogen();
    $fragebogenObjekt->ladenAusDatenbank($conn, $fragebogenId);

    // Überprüfen, ob der Fragebogen erfolgreich geladen wurde
    if ($fragebogenObjekt->getId() !== null) {
        $fragebogenTitel = $fragebogenObjekt->getTitel();

        // Fragen für den Fragebogen laden (für das Dropdown)
        $frageObjekt = new Frage();
        $fragen = $frageObjekt->ladenFragenFuerFragebogen($conn, $fragebogenId);

        if ($frageId) {
            // Fragetext laden
            $frageObjekt->ladenAusDatenbank($conn, $frageId);
            $fragetext = $frageObjekt->getFragetext();

            // Antworten zur Frage laden
            $antwort = new Antwort();
            $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frageId);

            // Formularverarbeitung zum Speichern der Weiterleitung
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['speichern_weiterleitung'])) {
                $zielUrl = $_POST['ziel_url'];
                $ausgewaehlteAntworten = isset($_POST['antworten']) ? $_POST['antworten'] : [];
            
                // 1. Antwortkombination erstellen und speichern (direkt mit Ziel-URL)
                $antwortkombination = new Antwortkombination(null, $zielUrl);
                $antwortkombination->speichernInDatenbank($conn);
            
                // 2. Antworten zur Kombination hinzufügen (nachdem die Antwortkombination gespeichert wurde)
                foreach ($ausgewaehlteAntworten as $antwortId) {
                    $verbindung = new AntwortkombinationAntwort(null, $antwortkombination->getId(), $antwortId); // Hier die ID der Antwortkombination verwenden
                    $verbindung->speichernInDatenbank($conn);
                }
            
                echo "Weiterleitung erfolgreich gespeichert!";
            }
            
        } else {
            $fragetext = "Keine Frage ausgewählt";
            $antworten = [];
        }
    } else {
        $fragebogenTitel = "Fragebogen nicht gefunden";
        $fragen = [];
        $fragetext = "Keine Frage ausgewählt";
        $antworten = [];
    }
} else {
    $fragebogenTitel = "Kein Fragebogen ausgewählt";
    $fragen = [];
    $fragetext = "Keine Frage ausgewählt";
    $antworten = [];
}
?>

<!DOCTYPE html>
<html>
<link rel="stylesheet" href="schön.css">

<head>
    <title>Weiterleitung konfigurieren</title>
</head>
<body>
    <h1>Weiterleitung konfigurieren</h1>

    <h2>Fragebogen: <?php echo $fragebogenTitel; ?></h2>

    <form method="get"> 
        <input type="hidden" name="fragebogen_id" value="<?php echo $fragebogenId; ?>"> 
        <select name="frage_id" onchange="this.form.submit()">
            <option value="">-- Bitte auswählen --</option>
            <?php foreach ($fragen as $frage): ?>
                <option value="<?= $frage['id'] ?>" <?php if ($frageId == $frage['id']) echo 'selected'; ?>><?= $frage['fragetext'] ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($frageId): ?>
        <h2>Frage: <?php echo $fragetext; ?></h2>

        <form method="post">
            <input type="hidden" name="frage_id" value="<?php echo $frageId; ?>"> 
            <input type="hidden" name="fragebogen_id" value="<?php echo $fragebogenId; ?>"> 

            <h3>Antworten auswählen:</h3>
            <?php foreach ($antworten as $antwort): ?>
                <label>
                    <input type="checkbox" name="antworten[]" value="<?= $antwort['id'] ?>">
                    <?= $antwort['antworttext'] ?>
                </label><br>
            <?php endforeach; ?>

            <h3>Ziel-URL:</h3>
            <input type="text" name="ziel_url" required>

            <button type="submit" name="speichern_weiterleitung">Weiterleitung speichern</button>
        </form>
    <?php else: ?>
        <p>Bitte wählen Sie zuerst eine Frage aus.</p>
    <?php endif; ?>

    <a href="FragebogenErstellen.php?fragebogen_id=<?php echo $fragebogenId; ?>">Zurück zur Fragebogenübersicht</a>
</body>
</html>