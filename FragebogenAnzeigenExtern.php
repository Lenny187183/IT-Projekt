<?php
require_once 'Klassen/fragebogen.php';
require_once 'Klassen/frage.php';
require_once 'Klassen/antwort.php';
require_once 'Klassen/verzweigung.php'; 
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
                                <input type="radio" name="antworten[<?php echo $frage['id']; ?>]" value="<?php echo $antwort->getId(); ?>" onchange="zeigeNaechsteFrage(this)"> 
                                <?php echo $antwort->getAntworttext(); ?>

                                <?php if ($antwort->getZielUrl()): ?>
                                    <span class="ziel-url">(Ziel-URL: <?php echo $antwort->getZielUrl(); ?>)</span> 
                                <?php endif; ?>
                            </label><br> 
                        <?php endforeach; ?>
                    </div>

                    <?php 
                    // Ermittle die ID der vorherigen Frage (Parent-Frage)
                    $verzweigung = new Verzweigung();
                    // Wichtig: Hier die letzte Antwort der aktuellen Frage verwenden
                    //$lastAntwort = end($antworten); 
                    //$verzweigung->ladenAusDatenbankMitAntwortId($conn, $lastAntwort->getId()); 
                    $parentFrageId = $verzweigung->getParentFrageId();
                    ?>

                    <?php if ($parentFrageId): ?>
                        <button type="button" class="zurueck-button" data-parent-frage-id="<?php echo $parentFrageId; ?>">Zurück</button> 
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit">Weiterleiten</button> 
        </form> 
    </div>

    <script>
        function zeigeNaechsteFrage(radio) {
            // ID der aktuellen Frage ermitteln
            var aktuelleFrageId = $(radio).closest('.frage').attr('id');

            // ID der nächsten Frage und Ziel-URL ermitteln
            var naechsteFrageId = null;
            var zielUrl = null;
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
                    if ($verzweigung->ladenAusDatenbankMitAntwortId($conn, $antwort->getId())) {
                        $folgefrageId = $verzweigung->getFolgefrageId();
                        if ($folgefrageId) {
                            echo "if (antwortId == " . $antwort->getId() . " && aktuelleFrageId == 'frage_" . $frage['id'] . "') { naechsteFrageId = " . $folgefrageId . "; }";
                        }
                    }

                    // Weiterleitungs-URL hinzufügen
                    $zielUrl = $antwort->getZielUrl();
                    if (!empty($zielUrl)) {
                        echo "if (antwortId == " . $antwort->getId() . ") { zielUrl = '" . $zielUrl . "'; }";
                    }
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            // Aktuelle Frage verstecken
            $('#' + aktuelleFrageId).hide();

            // Weiterleitung, falls eine Ziel-URL definiert ist
            if (zielUrl) {
                window.location.href = zielUrl;
            } else if (naechsteFrageId) {
                $('#frage_' + naechsteFrageId).show();
            } else {
                alert('Ende des Fragebogens erreicht!');
            }
        }

        $(document).ready(function() {
            $(".zurueck-button").click(function() {
                var aktuelleFrageId = $(this).closest('.frage').attr('id');
                var parentFrageId = $(this).data('parent-frage-id');

                $('#' + aktuelleFrageId).hide();
                $('#frage_' + parentFrageId).show();
            });
        });
    </script>
</body>
</html>