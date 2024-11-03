<?php
require_once 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Datenbankverbindung
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if($conn->connect_error){
        die("Connection failed: ". $conn->connect_error);
    }

    // Prepared Statement verwenden, um SQL-Injection zu verhindern
    $sql = "SELECT * FROM mitarbeiter WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $row = $result->fetch_assoc(); 


        // Passwort mit password_verify() überprüfen
        if(password_verify($password, $row['password'])) { 
            header("Location: FragebogenErstellen.php");
            exit();
        } else {
            echo "Falsches Passwort.";
        }
    } else {
        echo "Benutzername nicht gefunden.";
    }

    $conn->close();
}
?>