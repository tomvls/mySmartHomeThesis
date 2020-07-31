<?php
    if (strcmp($_COOKIE['pass'],'123') != 0) {
        header("Location:login.php");
    }
?>

<!DOCTYPE html>
<html>
    <head>

        <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <style>
    
            /* Create three equal columns that floats next to each other */
            .column1-kitchen {
            float: left;
            width: 33.33%;
            padding: 10px;
            height: 220px; /* Should be removed. Only for demonstration */
            border: 1px solid black;
            }



            /* Create four equal columns that floats next to each other */
            .column2-kitchen {
            float: left;
            width: 50%;
            padding: 10px;
            height: 220px; /* Should be removed. Only for demonstration */
            border: 1px solid black;
            }

            /* Clear floats after the columns */
            .row:after {
            content: "";
            display: table;
            clear: both;
            }

            /* Responsive layout - makes the three columns stack on top of each other instead of next to each other */
            @media screen and (max-width: 600px) {
            .column1-kitchen {
                width: 100%;
            }
            }
        </style>
    </head>
<body>
<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "myDB";
    

    try {
        // Get Kitchen sensorings from Db
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt_sensorings = $conn->prepare("SELECT *
                                FROM Kitchen
                                ORDER BY reg_date DESC
                                LIMIT 1");
        $stmt_sensorings->execute(); 
        $data = $stmt_sensorings->fetch();

        // Get Home Mode and Kitchen desired Temperature (for Auto mode)
        $stmt_desiredTemp = $conn->prepare("SELECT mode,desiredTempKitchen
                                FROM SmartHomeInfo");
        $stmt_desiredTemp->execute(); 
        $home_info = $stmt_desiredTemp->fetch();
        $mode = $home_info["mode"];
        $desiredTemp = $home_info['desiredTempKitchen'];

        echo "</br>";
        echo "<form class='form-inline' id='desiredTempKitchen'>";
        echo "Desired Temperature: &nbsp;" .$desiredTemp. "°C&nbsp;&nbsp;";
        echo '<input type="text" class="form-control" id="newDesiredTempKitchen" placeholder="new temperature value">';
        echo "<button type='button' class='btn btn-success' onClick='changeDesiredTempKitchen()'>Change</button>";
        echo "</form>";
        echo "</br>";

        echo'<div class="row">';
        echo'  <div class="column1-kitchen" style="background-color:#aaa;">';
        echo'     <h2>Temperature</h2>';
        echo'    <p><img src="icons\\thermometer-2.svg" alt="temperature icon" style="float:left;width:87px;height:110px;"><h2>' .$data["temp"]. ' °C</h2></p>';
        echo'  </div>';
        echo'  <div class="column1-kitchen" style="background-color:#bbb;">';
        echo'    <h2>Humidity</h2>';
        echo'    <p><img src="icons\\humidity-1.svg" alt="humidity icon" style="float:left;width:87px;height:110px;"><h2>' .$data["humidity"]. ' %</h2></p>';
        echo'  </div>';
        echo'  <div class="column1-kitchen" style="background-color:#ccc;">';
        echo'    <h2>Fire</h2>';
        if ($data["flame"]) {
        echo'    <p><img src="icons\\fire.svg" alt="flame icon" style="float:left;width:87px;height:110px;"><h2>&nbsp; YES </h2></p>';
        }else {
        echo'    <p><img src="icons\\fire.svg" alt="flame icon" style="float:left;width:87px;height:110px;"><h2>&nbsp; NO </h2></p>';
        }
        echo'  </div>';
        echo'</div>';

        echo'<div class="row">';
        echo'  <div class="column2-kitchen" style="background-color:#ccc;">';
        echo'    <h2>Gas leakage</h2>';
        if ($data["gas"]) {
            echo'    <p><img src="icons\\gas-bottle.svg" alt="gas icon" style="float:left;width:87px;height:110px;"><h2> YES </h2></p>';
        }else {
            echo'    <p><img src="icons\\gas-bottle.svg" alt="gas icon" style="float:left;width:87px;height:110px;"><h2> NO </h2></p>';
        }
        echo'  </div>';
        echo'  <div class="column2-kitchen" style="background-color:#aaa;">';
        echo'    <h2>Lights</h2>';
        if ($data["lamp"] == "NONE") {
            echo'    <p><img src="icons\\lightbulb.svg" alt="lamp icon" style="float:left;width:87px;height:110px;"><h2>&nbsp; UNKNOWN </h2></p>';
            echo'    &nbsp;&nbsp;<button id="lampKitchenOn" type="button" class="btn btn-success" onClick="controlDevicesKitchen(this.id)">TURN ON</button>';
            echo'    &nbsp;&nbsp;<button id="lampKitchenOff" type="button" class="btn btn-danger" onClick="controlDevicesKitchen(this.id)">TURN OFF</button>';
        }else {
            echo'    <p><img src="icons\\lightbulb.svg" alt="lamp icon" style="float:left;width:87px;height:110px;"><h2>&nbsp;' .$data["lamp"]. ' </h2></p>';
            if ($data["lamp"] == "OFF") {
                echo'    &nbsp;&nbsp;<button id="lampKitchenOn" type="button" class="btn btn-success" onClick="controlDevicesKitchen(this.id)">TURN ON</button>';
            }else if ($data["lamp"] == "ON") {
                echo'    &nbsp;&nbsp;<button id="lampKitchenOff" type="button" class="btn btn-danger" onClick="controlDevicesKitchen(this.id)">TURN OFF</button>';
            }
        }
        echo'  </div>';
        echo'</div>';

    }catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    
?>

<script>
    function controlDevicesKitchen(buttonId) {
        console.log("User submited a change of devices statuses");

        // If Smart-Home is in Auto mode don't allow user to control devices
        let mode  = '<?php echo $mode;?>';
        if (mode === "AUTO" || mode === "POWER-SAVING") {
            console.log("On 'AUTO' and 'POWER-SAVING' mode you can't control home devices");
            // alert("When Home is in 'AUTO' mode you can't control home devices.\nChange mode to 'Manual' to gain control again");
            if(!alert("When Home is in 'AUTO' or 'POWER-SAVING' mode you can't control home devices.\nChange mode to 'Manual' or 'AWAY' to gain control again")){window.location.reload();}
        }else {
            let lampStatus = null;
            let heatingStatus = null;
            let acStatus = null;
            let tentsStatus = null;
            switch (buttonId) {
                case "lampKitchenOn":
                    lampStatus = "ON";
                    break;
                case "lampKitchenOff":
                    lampStatus = "OFF";
                    break;
                default:
                    if(!alert("Something was wrong.\nTry again later...")){window.location.reload();}
                    return;
            }

            let page = "saveCommandOnDb.php";
            let parameters = 'room=' + 'Kitchen' + '&' + 'lampSwitch=' + lampStatus + '&' + 'heatingSwitch=' + heatingStatus + '&' + 'acSwitch=' + acStatus + '&' + 'tentsSwitch=' + tentsStatus;
            let xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function() {
                if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
                    console.log(this.responseText);
                    if (this.responseText == 'done'){
                        location.reload();
                    } else {
                        console.log(this.responseText);
                        alert(this.responseText);
                    }
                }
            }
            xmlhttp.open("GET", page + '?' + parameters, true);
            xmlhttp.send(parameters);
        }
    }
        
</script>

<script>
    function changeDesiredTempKitchen() {
        //  Allowed temperature limits
        highTempLimit = 30;
        lowTempLimit = 18;

        newDesiredTempVal = document.getElementById("newDesiredTempKitchen").value;
        console.log("new desired temp: ",newDesiredTempVal);
        // check if new value is in the allowed limits
        if (newDesiredTempVal <= highTempLimit && newDesiredTempVal >= lowTempLimit) {
            // update desired room temperature on DB
            console.log("SUCCESS");

            let page = "updateRoomDesiredTemp.php";
            let parameters = 'room=' + 'Kitchen' + '&' + 'desiredTemp=' + newDesiredTempVal;
            console.log(parameters);
            
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
                    console.log(this.responseText);
                    if (this.responseText == 'done'){
                        location.reload();
                    } else {
                        alert(this.responseText);
                    }
                }
            }
            xmlhttp.open("GET", page + '?' + parameters, true);
            xmlhttp.send(parameters);
        } else {
            // Temp out of limits. Don't update and send warning message
            console.log(`Temperature ${newDesiredTempVal} is out of limits.`);
            // document.getElementById("tempOfLimitsWarning").removeAttribute("hidden"); 
            if(!alert('Requested temperature is out of limits! Allowed values are between 18 and 30°C')){window.location.reload();}
            
        }
    }

    // $('#tempOfLimitsWarningK').on('closed.bs.alert', function () {
    //     // do something…
    //     if(!alert('The alert message is now closed.')){window.location.reload();};
    //     location.reload();
        
    // })
</script>


    </body>
</html>
