<?php
session_start(); 
require_once 'Klassen/fragebogen.php';
require_once 'config.php';


// Datenbankverbindung
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); // Passe die Verbindungsdaten an

// Verbindung prüfen
if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

// Aktiven Fragebogen laden
$sql = "SELECT id FROM fragebogen WHERE aktiv = TRUE";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $_SESSION['fragebogen_id'] = $row['id'];
} else {
    // Fehlerbehandlung, falls kein oder mehrere aktive Fragebögen gefunden wurden
    $_SESSION['fragebogen_id'] = null; 
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fragebogen Sozialamt Nürnberg</title>
    <link rel="stylesheet" href="Startseite.css">
</head>
<body>
    <header>
        <h1>Willkommen beim Fragebogen des Sozialamts Nürnberg</h1>
    </header>

    <main>
        <section id="user">
            <h2>Für Bürgerinnen und Bürger</h2>
            <p>Bitte beantworten Sie die folgenden Fragen, um Ihre Ansprüche auf Sozialleistungen zu ermitteln.</p>
            <button onclick="window.location.href='FragebogenAnzeigenExtern.php?fragebogen_id=<?php echo $_SESSION['fragebogen_id']; ?>'">Fragebogen starten</button>
        </section>

        <section id="mitarbeiter">
            <h2>Für Mitarbeiter des Sozialamts</h2>
            <button onclick="window.location.href='Login.html'">Anmelden</button>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Sozialamt Nürnberg</p>
    </footer>
</body>
</html>