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
    <script>
        var aktuelleFrageId = <?php echo $fragen[0]['id']; ?>; // ID der ersten Frage

        function zeigeNaechsteFrage(antwortId) {
            // Aktuelle Frage verstecken
            $('#frage_' + aktuelleFrageId).hide();
            console.log(antwortId);

            // AJAX-Request an den Server, um die Folgefrage zu ermitteln
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: { antwortId: antwortId },
                success: function(response) {
                    
                    // ID der nächsten Frage aus der Antwort extrahieren
                    var naechsteFrageId = response.folgefrage_id;
                    console.log("Antwort von ajax.php:", response);
                    console.log("Naechste Frage ID:", naechsteFrageId); // Debugging-Ausgabe

                    // Nächste Frage anzeigen (falls vorhanden)
                    if (naechsteFrageId) {
                        $('#frage_' + naechsteFrageId).show();
                        aktuelleFrageId = naechsteFrageId; 
                    } else {
                        alert('Ende des Fragebogens erreicht!');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX-Fehler:", status, error);
                }
            });
        }
    </script>
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
                                <input type="radio" name="antworten[<?php echo $frage['id']; ?>]" value="<?php echo $antwort['id']; ?>" onchange="zeigeNaechsteFrage(<?php echo $antwort['id']; ?>)"> 
                                <?php echo $antwort['antworttext']; ?>
                            </label><br> 
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit">Weiterleiten</button> 
        </form> 
    </div>
</body>
</html>

