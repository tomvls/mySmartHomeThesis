<?php
    if (strcmp($_COOKIE['pass'],'123') != 0) {
        header("Location:login.php");
    }

    $newMode = $_GET["mode"];

    // db configs
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "UPDATE SmartHomeInfo SET 
                mode='{$newMode}'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        echo 'done';
        }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    
?>