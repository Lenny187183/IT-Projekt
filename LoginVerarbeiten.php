<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){

  
     
    $username = $_POST['username'];
    $password = $_POST['password'];



    // Database Connection

    $host = "localhost";
    $dbusername = "testserver";
    $dbpassword = "123";
    $dbname = "fragen";


    $conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

    if($conn->connect_error){
        die("Connection failed: ". $conn->connect_error);
    }

    

    // Validate login

    $query = "SELECT *FROM mitarbeiter WHERE username ='$username' AND password ='$password'";

    $result = $conn->query($query);

   


    if($result -> num_rows == 1){

        
        header("Location: AdminSicht.html");
        exit();


    }else{

        header("Location: Startseite.html");
        exit();

    }

    $conn->close();



}
?>