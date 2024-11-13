
<?php
require_once 'Klassen/fragebogen.php';
require_once 'config.php';


session_start();

// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); // Passe die Verbindungsdaten an

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
        //header("Location: Startseite.php"); 
        
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
<h1>Fragebogenverwaltung</h1>

<h2>Neuen Fragebogen erstellen</h2>
<form method="post">
    <textarea name="titel" placeholder="Titel" rows="5" cols="40" required></textarea> 
    <button type="submit" name="neuer_fragebogen">Erstellen</button>
</form>

    <h2>Aktiven Fragebogen setzen</h2>
    <form method="post" action="aktivenFragebogenSetzen.php"> 
        <select name="aktiver_fragebogen_id">
            <option value="">-- Bitte auswählen --</option> 
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
    <option value="">-- Bitte auswählen --</option>
        <?php echo $dropdownFragebogen->getFragebogenDropdownOptions($conn); ?> 
    </select>
    <button type="submit" name="bearbeiten">Bearbeiten</button>
    <button type="button" onclick="anzeigenFragebogen()">Anzeigen</button> 
    <button type="button" onclick="weiterleitungBearbeiten()">Weiterleitung bearbeiten</button>
    <button type="button" onclick="beziehungenBearbeiten()">Beziehungen bearbeiten</button>

  
</form>

<h2>Fragebogen löschen</h2>
<form action="FragebogenLoeschen.php" method="post">
    <select name="fragebogen_id">
        <option value="">-- Bitte auswählen --</option>
        <?php 
        $dropdownFragebogen = new Fragebogen(); 
        echo $dropdownFragebogen->getFragebogenDropdownOptions($conn); 
        ?> 
    </select>
    <button type="submit">Löschen</button>
</form>

    <a href="StartseiteMitarbeiter.php">Zurück zur Hauptseite</a>


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

        function beziehungenBearbeiten() {
            const selectedFragebogenId = document.querySelector('select[name="fragebogen_id"]').value;
            if (selectedFragebogenId) {
                window.location.href = `BeziehungBearbeiten.php?fragebogen_id=${selectedFragebogenId}`;
            } else {
                alert("Bitte wählen Sie einen Fragebogen aus.");
            }
        }

        function setFragebogenId(form) {
            const selectedFragebogenId = document.querySelector('select[name="fragebogen_id"]').value;
            form.querySelector('input[name="fragebogen_id"]').value = selectedFragebogenId;
            return true; // Formular absenden erlauben
        }

        function loeschenFragebogen() {
    const selectedFragebogenId = document.querySelector('select[name="fragebogen_id"]').value;
    if (selectedFragebogenId) {
        if (confirm("Möchten Sie diesen Fragebogen wirklich löschen?")) {
            // AJAX-Anfrage an FragebogenLoeschen.php
            fetch(`FragebogenLoeschen.php?fragebogen_id=${selectedFragebogenId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Netzwerkantwort war nicht ok');
                    }
                    return response.text(); 
                })
                .then(data => {
                    alert(data); 
                    location.reload(); 
                })
                .catch(error => {
                    console.error('Fehler beim Löschen:', error);
                    alert("Fehler beim Löschen des Fragebogens. Bitte versuchen Sie es erneut.");
                });
        }
    } else {
        alert("Bitte wählen Sie einen Fragebogen aus.");
    }
}


    </script>
</body>
</html>