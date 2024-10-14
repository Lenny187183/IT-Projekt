<?php
require_once 'Klassen/Fragebogen.php';
require_once 'Klassen/Frage.php';
require_once 'Klassen/Antwort.php';
require_once 'config.php';


// Datenbankverbindung 
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID und Frage-ID aus URL-Parametern abrufen
$fragebogenId = isset($_GET['fragebogen_id']) ? $_GET['fragebogen_id'] : null;
$frageId = isset($_GET['frage_id']) ? $_GET['frage_id'] : null;

// Fragebogen-Titel und Fragetext laden
if ($fragebogenId && $frageId) {
    // Fragebogen-Titel laden
    $fragebogenObjekt = new Fragebogen();
    $fragebogenObjekt->ladenAusDatenbank($conn, $fragebogenId);
    $fragebogenTitel = $fragebogenObjekt->getTitel();

    // Fragetext laden
    $frageObjekt = new Frage();
    $frageObjekt->ladenAusDatenbank($conn, $frageId);
    $fragetext = $frageObjekt->getFragetext();

    // Antworten zur Frage laden
    $antwort = new Antwort(null, $frageId, null); // Neue Antwort, daher id = null, frage_id wird übergeben, antworttext ist noch leer
$antworten = $antwort->ladenAntwortenFuerFrage($conn, $frageId);

    // Formularverarbeitung zum Hinzufügen einer neuen Antwort
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['neue_antwort'])) {
        $neueAntwortText = $_POST['antworttext'];

        // Neue Antwort erstellen und speichern
        $neueAntwort = new Antwort(null, $frageId, $neueAntwortText);
        $neueAntwort->speichernInDatenbank($conn);

        // Seite neu laden, um die neue Antwort im Dropdown anzuzeigen
        header("Location: AntwortBearbeiten.php?fragebogen_id=$fragebogenId&frage_id=$frageId");
        exit();
    }
} else {
    $fragebogenTitel = "Kein Fragebogen ausgewählt";
    $fragetext = "Keine Frage ausgewählt";
    $antworten = [];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Antworten bearbeiten</title>
    <link rel="stylesheet" href="FragebogenErstellen.css">
</head>
<body>
    <h1>Antworten bearbeiten</h1>

    <h2>Aktueller Fragebogen: <?php echo $fragebogenTitel; ?></h2>
    <h2>Aktuelle Frage: <?php echo $fragetext; ?></h2>

    <?php if ($frageId): ?>
        <form action="AntwortEingabe.php" method="post"> 
            <input type="hidden" name="frage_id" value="<?php echo $frageId; ?>">
            <input type="hidden" name="fragebogen_id" value="<?php echo $fragebogenId; ?>">

            <div class="form-group">
                <label for="antworttext">Antworttext:</label>
                <input type="text" id="antworttext" name="antworttext" required>
            </div>

            <button type="submit" name="neue_antwort">Antwort hinzufügen</button>
        </form>

        <h2>Vorhandene Antworten</h2>
        <?php if (!empty($antworten)): ?>
            <select id="antwortenDropdown">
                <?php foreach ($antworten as $antwort): ?>
                    <option value="<?= $antwort['id'] ?>"><?= $antwort['antworttext'] ?></option>
                <?php endforeach; ?>
            </select>
            </form> </div>
        <?php else: ?>
            <p>Keine Antworten gefunden.</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="AdminSicht.php?fragebogen_id=<?php echo $fragebogenId; ?>">Zurück zur Fragenübersicht</a>
</body>
</html>
