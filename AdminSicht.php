
<?php

require_once 'Klassen/fragebogen.php'; // Stellen Sie sicher, dass die Klasse eingebunden wird

session_start();

// Datenbankverbindung (wie im vorherigen Skript)
$conn = new mysqli('localhost', 'testserver', '123', 'fragen'); 

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


<!DOCTYPE html>
<html lang="de">
    
<head>
    <meta charset="UTF-8">
    <title>Admin-Bereich</title>
</head>
<body>
    <h1>Fragebogen bearbeiten</h1>

    <h2>Aktueller Fragebogen: <?php echo $fragebogenTitel; ?></h2> 

    
    <div class="form-group">
    <form action="NewFrageEingabe.php" method="post">
    <div class="form-group">
        <label for="fragetext">fragetext:</label>
        <input type="text" id="fragetext" name="fragetext" required>
    </div>

    

    <input type="hidden" id="fragebogen_id" name="fragebogen_id" value="<?php echo $fragebogenId; ?>">

    <button type="submit">Frage hinzufügen</button>
    <button type="button" onclick="window.location.href='fragen_anzeigen.php?fragebogen_id=<?php echo $fragebogenId; ?>'">Fragen anzeigen</button> </form>
    </form>
    </div>

    <a href="FragebogenErstellen.php">Zurück zur Hauptseite</a>
    <link rel="stylesheet" href="schön.css">
</body>
</html>
