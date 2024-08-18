<?php



$fragetext = $_POST['fragetext'];
$fragebogenidFK = $_POST['fragebogen_id'];



$conn = new mysqli('localhost', 'testserver', '123', 'fragen');
if($conn->connect_error){
	die('connection Failed : '.$conn->connect_error);
}else{
	$stmt = $conn->prepare("insert into frage(fragetext, fragebogen_id )
	values(?, ?)");
    $stmt->bind_param("si", $fragetext, $fragebogenidFK );
	$stmt->execute();
	header("Location: AdminSicht.php");
	$stmt->close();
	$conn->close();
}
?>



