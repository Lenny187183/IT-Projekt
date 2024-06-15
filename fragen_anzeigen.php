<?php
// Datenbankverbindung (gleiche Daten wie beim Einfügen)
$conn = new mysqli('localhost', 'testserver', '123', 'fragen');

// Verbindung prüfen
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL-Abfrage zum Auslesen aller Fragen
$sql = "SELECT * FROM fragen";
$result = $conn->query($sql);

// Ergebnisse ausgeben
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Frage: " . $row["frage"] . "<br>";
        echo "Antworttyp: " . $row["antworttyp"] . "<br><br>"; 
        // Hier kannst du weitere Spalten ausgeben, falls vorhanden
    }
} else {
    echo "Keine Fragen gefunden.";
}

// Verbindung schließen
$conn->close();
?>