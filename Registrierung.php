<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validierung der Eingaben (hier kannst du weitere Validierungen hinzufügen, z.B. für Passwortkomplexität)
    if (empty($username) || empty($password)) {
        die("Bitte füllen Sie alle Felder aus.");
    }

    // Datenbankverbindung
	$conn = new mysqli($db_host, $db_user, $db_pass, $db_name); // Passe die Verbindungsdaten an
    if ($conn->connect_error) {
        die("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }

    // Überprüfen, ob der Benutzername bereits existiert
    $checkSql = "SELECT id FROM mitarbeiter WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "Benutzername bereits vorhanden.";
    } else {
        // Passwort hashen
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Neuen Mitarbeiter in die Datenbank einfügen
        $sql = "INSERT INTO mitarbeiter (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo "Registrierung erfolgreich!";
            // Optional: Weiterleitung zur Login-Seite
            // header("Location: login.php");
            // exit();
        } else {
            echo "Fehler bei der Registrierung: " . $stmt->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Mitarbeiter registrieren</title>
    <link rel="stylesheet" href="FragebogenErstellen.css"> </head>
<body>
    <h1>Mitarbeiter registrieren</h1>
    <form action="" method="post">
        <div class="form-group">
            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username"   
 required>
        </div>
        <div class="form-group">
            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" required>   

        </div>
        <button type="submit">Registrieren</button>   

    </form>
</body>
</html>