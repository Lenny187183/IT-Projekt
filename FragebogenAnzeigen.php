<?php
require_once 'Klassen/Fragebogen.php';
require_once 'Klassen/Frage.php';
require_once 'Klassen/Antwort.php';
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
</head>
<body>
    <div class="container"> 
        <h1><?php echo $fragebogenTitel; ?></h1>

        <form method="post" action="FragebogenVerarbeiten.php"> 

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
                        <label>
                            <input type="radio" name="antworten[<?php echo $frage['id']; ?>]" value="<?php echo $antwort['id']; ?>" required> 
                            <?php echo $antwort['antworttext']; ?>
                            <?php if (isset($antwortkombinationenMap[$antwort['id']])): ?>
                                <span class="weiterleitungs-urls">(Weiterleitungen: <?php echo implode(', ', $antwortkombinationenMap[$antwort['id']]); ?>)</span>
                            <?php endif; ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit">Weiterleiten</button> 

        <a href="FragebogenErstellen.php" class="btn">Zurück zur Auswahl</a>
        </form> 
    </div>
</body>
</html>