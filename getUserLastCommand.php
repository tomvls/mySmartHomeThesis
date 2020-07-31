
<?php

	// db configs
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT id,room,lampSwitch,acSwitch,heatingSwitch,tentsSwitch
                                FROM Commands
                                ORDER BY reg_date DESC
                                LIMIT 1");
        $stmt->execute(); 
        $data = $stmt->fetch();
    }catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

    $commandObj = (object) [
        'id' => $data['id'],
        'room' => $data['room'],
        'lampSwitch' => $data['lampSwitch'],
        'heatingSwitch' => $data['heatingSwitch'],
        'acSwitch' => $data['acSwitch'],
        'tentsSwitch' => $data['tentsSwitch'],
    ];

    echo json_encode($commandObj);
?>