<?php
// Datenbankverbindung
$conn = new mysqli('localhost', 'testserver', '123', 'fragen'); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus der URL holen (falls vorhanden)
$fragebogenId = isset($_GET['fragebogen_id']) ? $_GET['fragebogen_id'] : null;

// Frage-ID aus der URL holen (falls vorhanden)
$frageId = isset($_GET['frage_id']) ? $_GET['frage_id'] : null;

// Fragebogen-Titel und Fragetext laden (falls IDs vorhanden sind)
if ($fragebogenId && $frageId) {
    // Fragebogen-Titel laden
    $sqlFragebogen = "SELECT titel FROM fragebogen WHERE id = ?";
    $stmtFragebogen = $conn->prepare($sqlFragebogen);
    $stmtFragebogen->bind_param("i", $fragebogenId);
    $stmtFragebogen->execute();
    $resultFragebogen = $stmtFragebogen->get_result();
    $fragebogenTitel = $resultFragebogen->fetch_assoc()['titel'];

    // Fragetext laden
    $sqlFrage = "SELECT fragetext FROM frage WHERE id = ?";
    $stmtFrage = $conn->prepare($sqlFrage);
    $stmtFrage->bind_param("i", $frageId);
    $stmtFrage->execute();
    $resultFrage = $stmtFrage->get_result();
    $fragetext = $resultFrage->fetch_assoc()['fragetext'];

    // Antworten zur Frage laden
    $sqlAntworten = "SELECT id, antworttext FROM antwort WHERE frage_id = ?";
    $stmtAntworten = $conn->prepare($sqlAntworten);
    $stmtAntworten->bind_param("i", $frageId);
    $stmtAntworten->execute();
    $resultAntworten = $stmtAntworten->get_result();
    $antworten = $resultAntworten->fetch_all(MYSQLI_ASSOC);
} 

// Fragebögen laden (für das erste Dropdown)
$sqlFrageboegen = "SELECT id, titel FROM fragebogen";
$resultFrageboegen = $conn->query($sqlFrageboegen);
$fragebogenListe = $resultFrageboegen->fetch_all(MYSQLI_ASSOC);

// Fragen laden (für das zweite Dropdown, abhängig vom ausgewählten Fragebogen)
$fragenListe = [];
if ($fragebogenId) {
    $sqlFragenDropdown = "SELECT id, fragetext FROM frage WHERE fragebogen_id = ?";
    $stmtFragenDropdown = $conn->prepare($sqlFragenDropdown);
    $stmtFragenDropdown->bind_param("i", $fragebogenId);
    $stmtFragenDropdown->execute();
    $resultFragenDropdown = $stmtFragenDropdown->get_result();
    $fragenListe = $resultFragenDropdown->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Antwortzuteilung</title>
    <link rel="stylesheet" href="schön.css">
</head>
<body>
    <h1>Antwortzuteilung</h1>

    <h2>Fragebogen auswählen</h2>
    <form method="get">
        <select name="fragebogen_id" onchange="this.form.submit()">
            <option value="">-- Bitte auswählen --</option>
            <?php foreach ($fragebogenListe as $fb): ?>
                <option value="<?= $fb['id'] ?>" <?php if ($fragebogenId == $fb['id']) echo 'selected'; ?>><?= $fb['titel'] ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($fragebogenId): ?>
        <h2>Frage auswählen (<?php echo $fragetext; ?>)</h2>
        <form method="get">
            <input type="hidden" name="fragebogen_id" value="<?php echo $fragebogenId; ?>">
            <select name="frage_id" onchange="this.form.submit()">
                <option value="">-- Bitte auswählen --</option>
                <?php foreach ($fragenListe as $frage): ?>
                    <option value="<?= $frage['id'] ?>" <?php if ($frageId == $frage['id']) echo 'selected'; ?>><?= $frage['fragetext'] ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($frageId): ?>
            <h2>Antwort hinzufügen (zur Frage: <?php echo $fragetext; ?>)</h2>
            <form action="AntwortHinzufuegen.php" method="post"> 
                <input type="hidden" name="frage_id" value="<?php echo $frageId; ?>">
                <input type="hidden" name="fragebogen_id" value="<?php echo $fragebogenId; ?>">

                <div class="form-group">
                    <label for="antworttext">Antworttext:</label>
                    <input type="text" id="antworttext" name="antworttext" required>
                </div>

                <button type="submit">Antwort speichern</button>
            </form>

            <h2>Vorhandene Antworten</h2>
            <ul>
                <?php foreach ($antworten as $antwort): ?>
                    <li><?php echo $antwort['antworttext']; ?></li> 
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <a href="index.html">Zurück zur Hauptseite</a>
</body>
</html>