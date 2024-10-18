
<?php
require_once 'Klassen/fragebogen.php'; // Stellen Sie sicher, dass die Klasse eingebunden wird
require_once 'config.php';

session_start();

// Datenbankverbindung (wie im vorherigen Skript)
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); 

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Fragebogen-ID aus URL-Parametern abrufen
if (isset($_GET['fragebogen_id'])) {
    $fragebogenId = $_GET['fragebogen_id'];

    // Fragebogen laden
    $fragebogenObjekt = new Fragebogen();
    $fragebogenObjekt->ladenAusDatenbank($conn, $fragebogenId); 

    // Überprüfen, ob der Fragebogen erfolgreich geladen wurde
    if ($fragebogenObjekt->getId() !== null) {
        $fragebogenTitel = $fragebogenObjekt->getTitel();
    } else {
        $fragebogenTitel = "Fragebogen nicht gefunden"; // Oder eine andere Fehlermeldung
    }
} else {
    $fragebogenTitel = "Kein Fragebogen ausgewählt"; // Oder eine andere Fehlermeldung
}



  
?>

<?php
if ($fragebogenId) {
    $sqlFragen = "SELECT id, fragetext FROM frage WHERE fragebogen_id = ?";
    $stmtFragen = $conn->prepare($sqlFragen);
    $stmtFragen->bind_param("i", $fragebogenId);
    $stmtFragen->execute();
    $resultFragen = $stmtFragen->get_result();
    $fragen = $resultFragen->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin-Bereich</title>
    <link rel="stylesheet" href="schön.css">
</head>
<body>
    <h1>Fragebogen bearbeiten</h1>

    <h2>Aktueller Fragebogen: <?php echo $fragebogenTitel; ?></h2> 

    <?php if ($fragebogenId): ?>
        <div class="form-group">
            <form action="NewFrageEingabe.php" method="post">
                <div class="form-group">
                    <label for="fragetext">Fragetext:</label>
                    <input type="text" id="fragetext" name="fragetext" required>
                </div>

                <input type="hidden" id="fragebogen_id" name="fragebogen_id" value="<?php echo $fragebogenId; ?>">

                <button type="submit">Frage hinzufügen</button>
            </form>
        </div>

        <h2>Vorhandene Fragen</h2>
        <?php if (!empty($fragen)): ?> 
            <form action="AntwortBearbeitung.php" method="get">
                <select name="frage_id">
                    <?php foreach ($fragen as $frage): ?>
                        <option value="<?= $frage['id'] ?>"><?= $frage['fragetext'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="fragebogen_id" value="<?php echo $fragebogenId; ?>">
                <button type="submit">Antworten bearbeiten</button>
            </form>
        <?php else: ?>
            <p>Keine Fragen gefunden.</p>
        <?php endif; ?>

    <?php else: ?>
        </form>
    <?php endif; ?>

    <a href="FragebogenErstellen.php">Zurück zur Hauptseite</a>
    <link rel="stylesheet" href="FragebogenErstellen.css">
</body>
</html>