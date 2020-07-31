
<?php

	// db configs
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT * 
                                FROM SmartHomeInfo");
        $stmt->execute(); 
        $data = $stmt->fetch();
    }catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

    // $tempLivingRoom=null;
    // $tempKitchen=null;
    // foreach ($data as $room) {
    //     if ($room['name'] == "LivingRoom") {
    //         $tempLivingRoom = $room['desiredTemp'];
    //     }else if ($room['name'] == "Kitchen") {
    //         $tempKitchen = $room['desiredTemp'];
    //     }
        
    // }

    // $desiredTemperaturesObj = (object) [
    //     'LivingRoom' => $tempLivingRoom,
    //     'Kitchen' => $tempKitchen
    // ];

    $obj = (object) [
        'mode' => $data['mode'],
        'desiredTempLivingRoom' => $data['desiredTempLivingRoom'],
        'desiredTempKitchen' => $data['desiredTempKitchen']
    ];

    echo json_encode($obj);
?>