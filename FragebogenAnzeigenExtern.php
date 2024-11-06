<?php
require_once 'Klassen/fragebogen.php';
require_once 'Klassen/frage.php';
require_once 'Klassen/antwort.php';
require_once 'Klassen/antwortkombination.php';
require_once 'Klassen/antwortkombination_antwort.php'; 
require_once 'config.php';


// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Aktiven Fragebogen laden
$sql = "SELECT id FROM fragebogen WHERE aktiv = TRUE";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $fragebogenId = $row['id'];

    // Fragebogen laden
    $fragebogenObjekt = new Fragebogen();
    $fragebogenObjekt->ladenAusDatenbank($conn, $fragebogenId);

    // Überprüfen, ob der Fragebogen erfolgreich geladen wurde
    if ($fragebogenObjekt->getId() !== null) {
        $fragebogenTitel = $fragebogenObjekt->getTitel();

        // Fragen für den Fragebogen laden
        $frage = new Frage();
        $fragen = $frage->ladenFragenFuerFragebogen($conn, $fragebogenId);

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
        </head>
        <body>
            <div class="container"> 
                <h1><?php echo $fragebogenTitel; ?></h1>

                <form id="fragebogenForm" method="post" action="FragebogenVerarbeiten.php">

                    <?php 
                    // Initialisiere den Zähler für die Fragen
                    $frageCounter = 1; 
                    ?>

                    <?php foreach ($fragen as $frage): ?>
                        <div class="frage" id="frage_<?php echo $frage['id']; ?>" style="<?php echo $frageCounter > 1 ? 'display: none;' : ''; ?>">
                            <h3><?php echo $frage['fragetext']; ?></h3>

                            <?php
                            // Antworten zur Frage laden
                            $antwort = new Antwort();
                            $antworten = $antwort->ladenAntwortenFuerFrage($conn, $frage['id']);
                            ?>
                            <div class="antworten">
                                <?php foreach ($antworten as $antwort): ?>
                                    <label>
                                        <input type="radio" name="antworten[<?php echo $frage['id']; ?>]" value="<?php echo $antwort['id']; ?>"  onchange="zeigeNaechsteFrage(<?php echo $frage['id']; ?>, <?php echo $antwort['id']; ?>)"> 
                                        <?php echo $antwort['antworttext']; ?>
                                        <?php if (isset($antwortkombinationenMap[$antwort['id']])): ?>
                                            <span class="weiterleitungs-urls">(Weiterleitungen: <?php echo implode(', ', $antwortkombinationenMap[$antwort['id']]); ?>)</span>
                                        <?php endif; ?>
                                    </label><br>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php 
                        // Erhöhe den Zähler für die Fragen
                        $frageCounter++; 
                        ?>

                    <?php endforeach; ?>

                    <button type="submit">Weiterleiten</button>
                </form> 
            </div>

            <script>
            function zeigeNaechsteFrage(frageId, antwortId) {
                // Ermittle die nächste Frage-ID (hier musst du deine Logik implementieren)
                // ... (Beispiel: nächste Frage hat die ID frageId + 1) ...
                var naechsteFrageId = frageId + 1;

                // Zeige die nächste Frage an, falls vorhanden
                var naechsteFrage = document.getElementById('frage_' + naechsteFrageId);
                if (naechsteFrage) {
                    naechsteFrage.style.display = 'block';
                } else {
                    // Wenn keine nächste Frage existiert, zeige den Submit-Button an
                    document.querySelector('#fragebogenForm button[type="submit"]').style.display = 'block';
                }
            }
            </script>
        </body>
        </html>

        <?php
    } else {
        echo "Fragebogen nicht gefunden.";
    }
} else {
    echo "Kein aktiver Fragebogen gefunden.";
}

$conn->close();
?>


