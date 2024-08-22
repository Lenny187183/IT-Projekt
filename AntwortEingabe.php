<?php
$antwort = $_POST['antworttext'];
$frage_id = $_POST['frage_id'];


$conn = new mysqli('localhost', 'testserver', '123', 'fragen');

if($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
} else {
    // Formularverarbeitung
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Daten aus dem Formular empfangen
        $frage_id = $_POST['frage_id']; 
        $antworttext = $_POST['antworttext'];

        // Antwort speichern
        $stmt = $conn->prepare("INSERT INTO antwort (frage_id, antworttext) VALUES (?, ?)");
        $stmt->bind_param("is", $frage_id, $antworttext);
        $stmt->execute();

        // Weiterleitung nach erfolgreichem Speichern (mit frage_id und fragebogen_id)
        $fragebogenId = $_POST['fragebogen_id']; // Fragebogen-ID aus dem Formular holen
        header("Location: AntwortBearbeitung.php?fragebogen_id=$fragebogenId&frage_id=$frage_id"); 
        exit();
    }
}
?>