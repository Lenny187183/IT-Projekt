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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['speichern'])) {
            // Beziehungen speichern
            foreach ($_POST['folgefrage'] as $antwortId => $folgefrageId) {
                $verzweigung = new Verzweigung();

                if (!$verzweigung->ladenAusDatenbankMitAntwortId($conn, $antwortId)) {
                    $verzweigung->setAntwortId($antwortId);
                }

                $verzweigung->setFolgefrageId($folgefrageId);
                $verzweigung->setParentFrageIdAusAntwortId($conn, $antwortId); 

                if ($verzweigung->speichernInDatenbank($conn)) {
                    echo "Beziehungen erfolgreich gespeichert.<br>";
                } else {
                    echo "Fehler beim Speichern der Beziehungen.<br>";
                }
            }

            // Weiterleitungen speichern
            foreach ($_POST['ziel_url'] as $antwortId => $zielUrl) {
                $antwort = new Antwort();
                $antwort->ladenAusDatenbank($conn, $antwortId);
                $antwort->setZielUrl($zielUrl);

                if ($antwort->speichernInDatenbank($conn)) {
                    echo "Weiterleitung für Antwort $antwortId erfolgreich gespeichert.<br>";
                } else {
                    echo "Fehler beim Speichern der Weiterleitung für Antwort $antwortId.<br>";
                }
            }
        }

        // Verzweigungen löschen, wenn der Löschen-Button geklickt wurde
        if (isset($_POST['loeschen_beziehungen'])) {
            $sql = "DELETE FROM verzweigung WHERE antwort_id IN (SELECT id FROM antwort WHERE frage_id IN (SELECT id FROM frage WHERE fragebogen_id = ?))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $fragebogenId);

            if ($stmt->execute()) {
                echo "Alle Beziehungen für diesen Fragebogen wurden gelöscht.";
            } else {
                echo "Fehler beim Löschen der Beziehungen.";
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
                                        <span><?php echo $antwort->getAntworttext(); ?></span> 

                                        <label for="folgefrage_<?php echo $antwort->getId(); ?>">Folgefrage:</label>
                                        <select id="folgefrage_<?php echo $antwort->getId(); ?>" name="folgefrage[<?php echo $antwort->getId(); ?>]">
                                            <option value="">Keine Folgefrage</option>
                                            <?php foreach ($fragen as $folgefrage): ?>
                                                <option value="<?php echo $folgefrage['id']; ?>"><?php echo $folgefrage['fragetext']; ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <label for="ziel_url_<?php echo $antwort->getId(); ?>">Ziel-URL:</label>
                                        <input type="text" id="ziel_url_<?php echo $antwort->getId(); ?>" name="ziel_url[<?php echo $antwort->getId(); ?>]" value="<?php echo $antwort->getZielUrl(); ?>"> 
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" name="speichern">Speichern</button>
                    <button type="submit" name="loeschen_beziehungen" onclick="return confirm('Sind Sie sicher, dass Sie alle Beziehungen für diesen Fragebogen löschen möchten?')">Alle Beziehungen löschen</button>
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