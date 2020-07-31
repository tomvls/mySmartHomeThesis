<?php
    if (strcmp($_COOKIE['pass'],'123') != 0) {
        header("Location:login.php");
    }
    
    $room = $_GET["room"];
    $newTempVal = $_GET["desiredTemp"];

    // db configs
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($room == "LivingRoom") {
            $sql = "UPDATE SmartHomeInfo SET desiredTempLivingRoom='{$newTempVal}'";
        }else if ($room == "Kitchen") {
            $sql = "UPDATE SmartHomeInfo SET desiredTempKitchen='{$newTempVal}'";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        echo 'done';
        }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    
?>
