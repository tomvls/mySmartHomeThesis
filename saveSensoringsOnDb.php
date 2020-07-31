<?php
    
    // Get metrics from all sensors and devices of the House
    // Living room
    $tempLivingRoom = $_GET["tempLR"];
    $humidityLivingRoom = $_GET["humidityLR"];
    $lightLivingRoom = $_GET["lightLR"];
    $motion = $_GET["motion"];
    $doorIsOpen = $_GET['door'];
    $heating = $_GET['heating'];
    $ac = $_GET["ac"];
    $lampLR = $_GET["lampLR"];
    // Kitchen
    $tempKitchen = $_GET["tempK"];
    $humidityKitchen = $_GET["humidityK"];
    $lightKitchen = $_GET["lightK"];
    $gas = $_GET["gas"];
    $flame = $_GET["flame"]; 
    $lampK = $_GET["lampK"];
    // Outside
    $tempOutside = $_GET["tempO"];
    $lightOutside = $_GET["lightO"];
    $humidity = $_GET["humidity"];
    $raining = $_GET["rain"]; 
    $lampO = $_GET["lampO"];
    $tents = $_GET["tents"];

    // TODO

	// $decoded = json_decode($actor, true);
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";

	 try {
        echo 'start';
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt_livingRoom = $conn->prepare("INSERT INTO LivingRoom (temp,humidity,light,motion,doorIsOpen,heating,ac,lamp) VALUES ('$tempLivingRoom','$humidityLivingRoom','$lightLivingRoom','$motion','$doorIsOpen','$heating','$ac','$lampLR')");
        $stmt_kitchen = $conn->prepare("INSERT INTO kitchen (temp,humidity,light,gas,flame,lamp) VALUES ('$tempKitchen','$humidityKitchen','$lightKitchen','$gas','$flame','$lampK')");
        $stmt_outside = $conn->prepare("INSERT INTO Outside (temp,light,humidity,raining,lamp,tents) VALUES ('$tempOutside','$lightOutside','$humidity','$raining','$lampO','$tents')");
        $stmt_livingRoom->execute();
        // echo 'done';
        $stmt_kitchen->execute();
        echo 'done';
        $stmt_outside->execute();
        echo 'done';
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

?>