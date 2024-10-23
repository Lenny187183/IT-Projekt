
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

        // Aktiven Fragebogen setzen
        $sqlAktivSetzen = "UPDATE fragebogen SET aktiv = IF(id = ?, TRUE, FALSE)";
        $stmtAktivSetzen = $conn->prepare($sqlAktivSetzen);
        $stmtAktivSetzen->bind_param("i", $_SESSION['neuer_fragebogen_id']);
        $stmtAktivSetzen->execute();

        // Weiterleitung zur Startseite mit der fragebogen_id in der Session
        header("Location: Startseite.php"); 
        exit();
    }
}

// Fragebögen laden
$sql = "SELECT id, titel FROM fragebogen";
$result = $conn->query($sql);
$fragebogen = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<link rel="stylesheet" href="FragebogenErstellen.css">

<body>
    <h1>Fragebogen erstellen oder auswählen</h1>

    <h2>Neuen Fragebogen erstellen</h2>
    <form method="post">
        <input type="text" name="titel" placeholder="Titel" required>
        <button type="submit" name="neuer_fragebogen">Erstellen</button>
    </form>

    <h2>Aktiven Fragebogen auswählen</h2>
    <form method="post" action="aktivenFragebogenSetzen.php"> 
        <select name="fragebogen_id">
            <?php 
            $dropdownFragebogen = new Fragebogen(); 
            echo $dropdownFragebogen->getFragebogenDropdownOptions($conn); 
            ?> 
        </select>
        <button type="submit">Aktiven Fragebogen setzen</button>
    </form>

    <h2>Vorhandene Fragebögen</h2>
    <form action="AdminSicht.php" method="get"> 
        <select name="fragebogen_id">
            <?php 
            echo $dropdownFragebogen->getFragebogenDropdownOptions($conn); 
            ?> 
        </select>
        <button type="submit">Bearbeiten</button>
        <button type="button" onclick="anzeigenFragebogen()">Anzeigen</button> 
        <button type="button" onclick="weiterleitungBearbeiten()">Weiterleitung bearbeiten</button>

        
    </form>

    <a href="Startseite.php">Zurück zur Hauptseite</a>

    <script>
        function anzeigenFragebogen() {
            const selectedFragebogenId = document.querySelector('select[name="fragebogen_id"]').value;
            if (selectedFragebogenId) {
                window.location.href = `FragebogenAnzeigen.php?fragebogen_id=${selectedFragebogenId}`; 
            } else {
                alert("Bitte wählen Sie einen Fragebogen aus.");
            }
        }

        function weiterleitungBearbeiten() {
            const selectedFragebogenId = document.querySelector('select[name="fragebogen_id"]').value;
            if (selectedFragebogenId) {
                window.location.href = `WeiterleitungKonfigurieren.php?fragebogen_id=${selectedFragebogenId}`;
            } else {
                alert("Bitte wählen Sie einen Fragebogen aus.");
            }
        }

        function setFragebogenId(form) {
            const selectedFragebogenId = document.querySelector('select[name="fragebogen_id"]').value;
            form.querySelector('input[name="fragebogen_id"]').value = selectedFragebogenId;
            return true; // Formular absenden erlauben
        }
    </script>
</body>
</html>