<?php
$frage = $_POST['frage'];
$antworttyp = $_POST['antworttyp'];



$conn = new mysqli('localhost', 'testserver', '123', 'fragen');
if($conn->connect_error){
	die('connection Failed : '.$conn->connect_error);
}else{
	$stmt = $conn->prepare("insert into fragen(frage, antworttyp)
	values(?,?)");
    $stmt->bind_param("ss", $frage, $antworttyp);
	$stmt->execute();
	echo"Sucessfull";
	echo"Die Frage wurde übermittelt";
	$stmt->close();
	$conn->close();
}
?>