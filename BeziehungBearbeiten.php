<?php
require_once 'Klassen/verzweigung.php'; 
require_once 'Klassen/fragebogen.php';
require_once 'Klassen/frage.php';
require_once 'Klassen/antwort.php';
require_once 'config.php';

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus der URL holen
$fragebogenId = isset($_GET['fragebogen_id']) ? $_GET['fragebogen_id'] : null;

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

        // Fragen für den Fragebogen laden
        $frage = new Frage();
        $fragen = $frage->ladenFragenFuerFragebogen($conn, $fragebogenId);

        // Formular zum Speichern der Beziehungen
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['folgefrage'] as $antwortId => $folgefrageId) {
                $verzweigung = new Verzweigung();

                // Wenn die Verzweigung bereits existiert, lade sie aus der Datenbank
                if (!$verzweigung->ladenAusDatenbankMitAntwortId($conn, $antwortId)) {
                    // Ansonsten erstelle eine neue Verzweigung
                    $verzweigung->setAntwortId($antwortId);
                }

                $verzweigung->setFolgefrageId($folgefrageId);
                $verzweigung->setParentFrageIdAusAntwortId($conn, $antwortId); 

                if ($verzweigung->speichernInDatenbank($conn)) {
                    echo "Beziehungen erfolgreich gespeichert.";
                } else {
                    echo "Fehler beim Speichern der Beziehungen.";
                }
            }
        }

        ?>

        <!DOCTYPE html>
        <html>
        <head>
            <title>Beziehungen bearbeiten</title>
            <link rel="stylesheet" href="schön.css"> 
        </head>
        <body>
            <div class="container">
                <form action="" method="post"> 
                    <h1>Beziehungen für Fragebogen "<?php echo $fragebogenTitel; ?>" bearbeiten</h1>

                    <?php foreach ($fragen as $frage): ?>
                        <div class="frage">
                            <h3><?php echo $frage['fragetext']; ?></h3>

                            <?php
                            // Antworten zur Frage laden
                            $antwort = new Antwort();
                            $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);
                            ?>
                            <div class="antworten">
                                <?php foreach ($antworten as $antwort): ?>
                                    <div class="antwort">
                                        <span><?php echo $antwort['antworttext']; ?></span>
                                        <label for="folgefrage_<?php echo $antwort['id']; ?>">Folgefrage:</label>
                                        <select id="folgefrage_<?php echo $antwort['id']; ?>" name="folgefrage[<?php echo $antwort['id']; ?>]">
                                            <option value="">Keine Folgefrage</option>
                                            <?php foreach ($fragen as $folgefrage): ?>
                                                <option value="<?php echo $folgefrage['id']; ?>"><?php echo $folgefrage['fragetext']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit">Speichern</button>
                </form> 
            </div>
        </body>
        </html>

        <?php
    } else {
        echo "Fragebogen nicht gefunden.";
    }
} else {
    echo "Keine Fragebogen ID angegeben.";
}

$conn->close();
?>