<?php
$frage = $_POST['frage'];
$antworttyp = $_POST['antworttyp'];

echo"geht net";

$conn = new mysqli('127.0.0.1','013083erika', '123', 'itprojekt');
if($conn->connect_error){
	die('connection Failed : '.$conn->connect_error);
}else{
	$stmt = $$conn->prepare("eingeben der Frage(frage, antworttyp)values(?,?)");
    $stmt->bind_param("ss", $frage, $antworttyp);
	$stmt->execute();
	echo"Sucessfull";
	$stmt->clonse();
	$conn->clonse();
}
?>