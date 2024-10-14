<?php
require_once 'config.php';


$fragetext = $_POST['fragetext'];
$fragebogenidFK = $_POST['fragebogen_id'];
$antwort = $_POST['antworttext'];
$frage_id = $_POST['frage_id'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if($conn->connect_error){
    die('connection Failed : '.$conn->connect_error);
}else{
    $stmt = $conn->prepare("insert into frage(fragetext, fragebogen_id )
    values(?, ?)");
    $stmt->bind_param("si", $fragetext, $fragebogenidFK );
    $stmt->execute();
    header("Location: AdminSicht.php?fragebogen_id=" . $fragebogenidFK); // $fragebogenidFK muss übergeben werden, dass Admin Sicht weiß um welchen Fragebogen es geht
    exit();
}
?>


