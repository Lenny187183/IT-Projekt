<?php
require_once 'Klassen/fragebogen.php';
require_once 'Klassen/frage.php';
require_once 'Klassen/antwort.php';
require_once 'Klassen/verzweigung.php'; // Verzweigung Klasse einbinden
require_once 'Klassen/antwortkombination.php';
require_once 'Klassen/antwortkombination_antwort.php'; 
require_once 'config.php';

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

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

// Antwortkombinationen und zugehörige Antworten laden
$sqlAntwortkombinationen = "SELECT 
                                ak.id as antwortkombination_id, 
                                ak.ziel_url, 
                                aka.antwort_id
                            FROM antwortkombination ak
                            JOIN antwortkombination_antwort aka ON ak.id = aka.antwortkombination_id
                            WHERE aka.antwort_id IN (
                                SELECT id 
                                FROM antwort 
                                WHERE frage_id IN (
                                    SELECT id 
                                    FROM frage 
                                    WHERE fragebogen_id = ?
                                )
                            )";

$stmtAntwortkombinationen = $conn->prepare($sqlAntwortkombinationen);
$stmtAntwortkombinationen->bind_param("i", $fragebogenId);
$stmtAntwortkombinationen->execute();
$resultAntwortkombinationen = $stmtAntwortkombinationen->get_result();
$antwortkombinationen = $resultAntwortkombinationen->fetch_all(MYSQLI_ASSOC);

// Antwortkombinationen nach Antwort-IDs indizieren
$antwortkombinationenMap = [];
foreach ($antwortkombinationen as $kombination) {
    $antwortId = $kombination['antwort_id'];
    $zielUrl = $kombination['ziel_url'];

    if (!isset($antwortkombinationenMap[$antwortId])) {
        $antwortkombinationenMap[$antwortId] = [];
    }
    $antwortkombinationenMap[$antwortId][] = $zielUrl; 
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fragebogen anzeigen</title>
    <link rel="stylesheet" href="schön.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <style>
        .frage {
            display: none; /* Alle Fragen initial verstecken */
        }
        #frage_<?php echo $fragen[0]['id']; ?> { /* Die erste Frage anzeigen */ 
            display: block;
        }
    </style>
</head>
<body>
    <div class="container"> 
        <h1><?php echo $fragebogenTitel; ?></h1>

        <form id="fragebogenForm" method="post" action="FragebogenVerarbeiten.php">
            <?php foreach ($fragen as $frage): ?>
                <div class="frage" id="frage_<?php echo $frage['id']; ?>"> 
                    <h3><?php echo $frage['fragetext']; ?></h3>

                    <?php
                    // Antworten zur Frage laden
                    $antwort = new Antwort();
                    $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);
                    ?>
                    <div class="antworten">
                        <?php foreach ($antworten as $antwort): ?>
                            <label>
                                <input type="radio" name="antworten[<?php echo $frage['id']; ?>]" value="<?php echo $antwort['id']; ?>" onchange="zeigeNaechsteFrage(this)"> 
                                <?php echo $antwort['antworttext']; ?>
                            </label><br> 
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit">Weiterleiten</button> 
            <button type="button" onclick="zeigeVorherigeFrage(this)">Zurück</button>
            </form> 
    </div>

    <script>
    function zeigeNaechsteFrage(radio) {
        // ID der aktuellen Frage ermitteln
        var aktuelleFrageId = $(radio).closest('.frage').attr('id');
        console.log("Aktuelle Frage ID:", aktuelleFrageId); // Debugging

        // ID der nächsten Frage ermitteln
        var naechsteFrageId = null;
        var antwortId = $(radio).val();
        <?php foreach ($fragen as $frage): ?>
            <?php 
            // Antworten zur Frage laden (innerhalb der Schleife)
            $antwort = new Antwort();
            $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);
            ?>
            <?php foreach ($antworten as $antwort): ?>
                <?php
                $verzweigung = new Verzweigung();
                if ($verzweigung->ladenAusDatenbankMitAntwortId($conn, $antwort['id'])) {
                    $folgefrageId = $verzweigung->getFolgefrageId();
                    if ($folgefrageId) {
                        echo "if (antwortId == " . $antwort['id'] . " && aktuelleFrageId == 'frage_" . $frage['id'] . "') { naechsteFrageId = " . $folgefrageId . "; console.log('Bedingung erfüllt für Antwort ' + antwortId + ' und Frage ' + aktuelleFrageId + ', nächste Frage ID: ' + naechsteFrageId);}";
                    }
                }
                ?>
            <?php endforeach; ?>
        <?php endforeach; ?>

        console.log("Naechste Frage ID:", naechsteFrageId); // Debugging

        // Aktuelle Frage verstecken
        $('#' + aktuelleFrageId).hide();

        // Nächste Frage anzeigen (falls vorhanden)
        if (naechsteFrageId) {
            $('#frage_' + naechsteFrageId).show();
        } else {
            alert('Ende des Fragebogens erreicht!');
        }
    }


    function zeigeVorherigeFrage(radio) {
    // ID der aktuellen Frage ermitteln
    var aktuelleFrageId = $(radio).closest('.frage').attr('id');
    console.log("Aktuelle Frage ID:", aktuelleFrageId); // Debugging

    // ID der vorherigen Frage (Parent-Frage) ermitteln
    var vorherigeFrageId = null;
    var antwortId = $(radio).val(); // Die ID der aktuellen Antwort

    <?php foreach ($fragen as $frage): ?>
        <?php 
        // Antworten zur Frage laden (innerhalb der Schleife)
        $antwort = new Antwort();
        $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);
        ?>
        <?php foreach ($antworten as $antwort): ?>
            <?php
            $verzweigung = new Verzweigung();
            if ($verzweigung->ladenAusDatenbankMitAntwortId($conn, $antwort['id'])) {
                $folgefrageId = $verzweigung->getFolgefrageId(); // ID der Folgefrage
                if ($folgefrageId) {
                    // JavaScript-Code generieren, um die vorherige Frage zu ermitteln
                    echo "if (antwortId == " . $antwort['id'] . " && aktuelleFrageId == 'frage_" . $folgefrageId . "') { vorherigeFrageId = " . $frage['id'] . "; console.log('Bedingung erfüllt für Antwort ' + antwortId + ' und Frage ' + aktuelleFrageId + ', vorherige Frage ID: ' + vorherigeFrageId);}";
                }
            }
            ?>
        <?php endforeach; ?>
    <?php endforeach; ?>

    console.log("Vorherige Frage ID:", vorherigeFrageId); // Debugging

    // Aktuelle Frage verstecken
    $('#' + aktuelleFrageId).hide();

    // Vorherige Frage anzeigen (falls vorhanden)
    if (vorherigeFrageId) {
        $('#frage_' + vorherigeFrageId).show();
    } else {
        alert('Dies ist die erste Frage!'); // Oder andere Aktion
    }
}


    </script>
</body>
</html>