
<?php
require_once 'Klassen/fragebogen.php';

session_start();

// Datenbankverbindung
$conn = new mysqli('localhost', 'testserver', '123', 'fragen'); // Passe die Verbindungsdaten an

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
} else {
    // Formularverarbeitung für neuen Fragebogen
    if (isset($_POST['neuer_fragebogen'])) {
        $titel = $_POST['titel'];
        $sql = "INSERT INTO fragebogen (titel) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $titel);
        $stmt->execute();

        
        $_SESSION['neuer_fragebogen_id'] = $stmt->insert_id;

        // Weiterleitung zur AdminSicht.html des neuen Fragebogens
        //header("Location: AdminSicht.php?fragebogen_id=" . $stmt->insert_id); 
        header("Location: FragebogenErstellen.php");
        exit();
    }}
    // Fragebögen laden
    $sql = "SELECT id, titel FROM fragebogen";
    $result = $conn->query($sql);
    $fragebogen = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<link rel="stylesheet" href="schön.css">
<head>
    <title>Fragebogen erstellen/auswählen</title>
</head>
<body>
    <h1>Fragebogen erstellen oder auswählen</h1>

    <h2>Neuen Fragebogen erstellen</h2>
    <form method="post">
        <input type="text" name="titel" placeholder="Titel" required>
        <button type="submit" name="neuer_fragebogen">Erstellen</button>
    </form>

    <h2>Vorhandene Fragebögen</h2>
<form method="get" action="AdminSicht.php"> 
    <select name="fragebogen_id">
        <?php 
        $dropdownFragebogen = new Fragebogen(); 
        echo $dropdownFragebogen->getFragebogenDropdownOptions($conn); 
        ?> 
    </select>
    <button type="submit">Bearbeiten</button>
</form>
 
</select>
 </select>

    <a href="Startseite.html">Zurück zur Hauptseite</a>
</body>


</html>