<?php
    if (strcmp($_COOKIE['pass'],'123') != 0) {
        header("Location:login.php");
    }

    $room = $_GET["room"];
    $lampSwitch = $_GET["lampSwitch"];
    $acSwitch = $_GET["acSwitch"];
    $heatingSwitch = $_GET["heatingSwitch"];
    $tentsSwitch = $_GET["tentsSwitch"];

    // db configs
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("INSERT INTO Commands (room,lampSwitch,heatingSwitch,acSwitch,tentsSwitch) VALUES ('$room','$lampSwitch','$heatingSwitch','$acSwitch','$tentsSwitch')");
        $stmt->execute();
        echo 'done';
        }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    
?>
