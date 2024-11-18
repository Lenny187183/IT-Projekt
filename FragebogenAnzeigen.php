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
        var_dump($fragen);        
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
        #frage_1 { /* Die erste Frage anzeigen */
            display: block;
        }
    </style>
    <script>
       var aktuelleFrageId = 1; // Starte mit der ersten Frage

function zeigeNaechsteFrage(antwortId) {
    // Aktuelle Frage verstecken
    $('#frage_' + aktuelleFrageId).hide();

    // AJAX-Request an den Server
    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        data: { antwortId: antwortId },
        dataType: 'json',
        success: function(response) {
            var naechsteFrageId = response.folgefrage_id;

            if (naechsteFrageId) {
                // Nächste Frage einblenden
                $('#frage_' + naechsteFrageId).show();
                aktuelleFrageId = naechsteFrageId;
            } else {
                alert('Ende des Fragebogens erreicht oder keine Folgefrage definiert.');
            }
        },
        error: function(xhr, status, error) {
            console.error("Fehler beim Laden der Folgefrage:", error);
        }
    });
}


        function resetRadioButtons() {
            // Alle Radio-Buttons im Formular abrufen
            const radios = document.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                radio.checked = false; // Radio-Button deaktivieren
            });

            // Setze den Fragebogen zurück zur ersten Frage
            $('.frage').hide();
            $('#frage_1').show();
            aktuelleFrageId = 1; // Zurücksetzen der aktuellenFrageId
        }
    </script>
</head>
<body>
<div class="container">
    <h1 style='white-space: pre-wrap;'><?php echo $fragebogenTitel; ?></h1>

    <form method="post" action="FragebogenVerarbeiten.php">
        <?php foreach ($fragen as $frage): ?>
            <div class="frage" id="frage_<?php echo $frage['id']; ?>" style="display: none;">
                <h3 style='white-space: pre-wrap;'><?php echo $frage['fragetext']; ?></h3>

                <?php
                $antwort = new Antwort();
                $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);
                ?>
                <div class="antworten">
                    <?php foreach ($antworten as $antwort): ?>
                        <label>
                            <input type="radio" name="antworten[<?php echo $frage['id']; ?>]" value="<?php echo $antwort['id']; ?>" onchange="zeigeNaechsteFrage(<?php echo $antwort['id']; ?>)">
                            <?php echo $antwort['antworttext']; ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <button type="submit">Weiterleiten</button>
        <button type="button" onclick="resetRadioButtons()">Zurücksetzen</button>
        <a href="FragebogenErstellen.php" class="btn">Zurück zur Auswahl</a>
    </form>
</div>
</body>
</html>