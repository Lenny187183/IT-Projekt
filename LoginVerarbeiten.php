<?php
require_once 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){

  
     
    $username = $_POST['username'];
    $password = $_POST['password'];



    // Database Connection

   


    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if($conn->connect_error){
        die("Connection failed: ". $conn->connect_error);
    }

    

    // Validate login

    $query = "SELECT *FROM mitarbeiter WHERE username ='$username' AND password ='$password'";

    $result = $conn->query($query);

   


    if($result -> num_rows == 1){

        
        header("Location: FragebogenErstellen.php");
        exit();


    }else{

        header("Location: Startseite.html");
        exit();

    }

    $conn->close();



}
?>