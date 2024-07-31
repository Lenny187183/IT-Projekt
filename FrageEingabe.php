<?php


$frage = $_POST['frage'];
$antworttyp = $_POST['antworttyp'];
$ueberschrift = $_POST['überschrift'];
$antwortmoeglichkeit = $_POST['antwortmöglichkeit'];



$conn = new mysqli('localhost', 'testserver', '123', 'fragen');
if($conn->connect_error){
	die('connection Failed : '.$conn->connect_error);
}else{
	$stmt = $conn->prepare("insert into fragen(frage, antworttyp, Überschrift, Antwortmöglichkeit)
	values(?,?,?,?)");
    $stmt->bind_param("ssss", $frage, $antworttyp, $ueberschrift, $antwortmoeglichkeit);
	$stmt->execute();
	header("Location: AdminSicht.php");
	$stmt->close();
	$conn->close();
}
?>