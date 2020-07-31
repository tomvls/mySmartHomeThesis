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
        .column1-livingRoom {
        float: left;
        width: 25%;
        padding: 10px;
        height: 220px; /* Should be removed. Only for demonstration */
        border: 1px solid black;
        }



        /* Create four equal columns that floats next to each other */
        .column2-livingRoom {
        float: left;
        width: 33.33%;
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
        .column1-livingRoom {
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
          // Get Living-room sensorings from Db
          $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $stmt_sensorings = $conn->prepare("SELECT * 
                                  FROM LivingRoom
                                  ORDER BY reg_date DESC
                                  LIMIT 1");
          $stmt_sensorings->execute(); 
          $data = $stmt_sensorings->fetch();

          // Get Home Mode and Living-room desired Temperature (for Auto mode)
          $stmt_desiredTemp = $conn->prepare("SELECT mode,desiredTempLivingRoom 
                                  FROM SmartHomeInfo");
          $stmt_desiredTemp->execute(); 
          $home_info = $stmt_desiredTemp->fetch();
          $mode = $home_info["mode"];
          $desiredTemp = $home_info['desiredTempLivingRoom'];

          echo "</br>";
          echo "<form class='form-inline' id='desiredTempLivingRoom'>";
          echo "Desired Temperature: &nbsp;" .$desiredTemp. "°C&nbsp;&nbsp;";
          echo '<input type="text" class="form-control" id="newDesiredTempLR" placeholder="new temperature value">';
          echo "<button type='button' class='btn btn-success' onClick='changeDesiredTempLR()'>Change</button>";
          echo "</form>";
          echo "</br>";

        }catch(PDOException $e) {
          echo "Error: " . $e->getMessage();
      }
      $conn = null;
      
  
    
echo'<div class="row">';
echo'  <div class="column1-livingRoom" style="background-color:#aaa;">';
echo'     <h2>Temperature</h2>';
echo'    <p><img src="icons\\thermometer-2.svg" alt="temperature icon" style="float:left;width:87px;height:110px;"><h2>' .$data["temp"]. ' °C</h2></p>';
echo'  </div>';
echo'  <div class="column1-livingRoom" style="background-color:#bbb;">';
echo'    <h2>Humidity</h2>';
echo'    <p><img src="icons\\humidity-1.svg" alt="humidity icon" style="float:left;width:87px;height:110px;"><h2>' .$data["humidity"]. ' %</h2></p>';
echo'  </div>';
echo'  <div class="column1-livingRoom" style="background-color:#ccc;">';
echo'    <h2>Movement</h2>';
if ($data["motion"]) {
  echo'    <p><img src="icons\\move-sensor.svg" alt="motion detected icon" style="float:left;width:87px;height:110px;"><h2>&nbsp; YES </h2></p>';
}else {
  echo'    <p><img src="icons\\move-sensor.svg" alt="motion detected icon" style="float:left;width:87px;height:110px;"><h2>&nbsp; NO </h2></p>';
}
echo'  </div>';
echo'  <div class="column1-livingRoom" style="background-color:#ccc;">';
echo'    <h2>Main Door</h2>';
if ($data["doorIsOpen"]) {
  echo'    <p><img src="icons\\door.svg" alt="door icon" style="float:left;width:87px;height:110px;"><h2> OPEN </h2></p>';
}else {
  echo'    <p><img src="icons\\door.svg" alt="door icon" style="float:left;width:87px;height:110px;"><h2> CLOSED </h2></p>';
}
echo'  </div>';
echo'</div>';

echo'<div class="row">';
echo'  <div class="column2-livingRoom" style="background-color:#aaa;">';
echo'    <h2>Lights</h2>';
if ($data["lamp"] == "NONE") {
    echo'    <p><img src="icons\\lightbulb.svg" alt="lamp icon" style="float:left;width:87px;height:110px;"><h2>&nbsp; UNKNOWN </h2></p>';
    echo'    &nbsp;&nbsp;<button id="lampOn" type="button" class="btn btn-success" onClick="controlDevicesLR(this.id)">TURN ON</button>';
    echo'    &nbsp;&nbsp;<button id="lampOff" type="button" class="btn btn-danger" onClick="controlDevicesLR(this.id)">TURN OFF</button>';
}else {
    echo'    <p><img src="icons\\lightbulb.svg" alt="lamp icon" style="float:left;width:87px;height:110px;"><h2>&nbsp;' .$data["lamp"]. ' </h2></p>';
    if ($data["lamp"] == "OFF") {
        echo'    &nbsp;&nbsp;<button id="lampOn" type="button" class="btn btn-success" onClick="controlDevicesLR(this.id)">TURN ON</button>';
    }else if ($data["lamp"] == "ON") {
        echo'    &nbsp;&nbsp;<button id="lampOff" type="button" class="btn btn-danger" onClick="controlDevicesLR(this.id)">TURN OFF</button>';
    }
}
echo'  </div>';
echo'  <div class="column2-livingRoom" style="background-color:#bbb;">';
echo'    <h2>Heating</h2>';
if ($data["heating"] == "NONE") {
    echo'    <p><img src="icons\\heater-1.svg" alt="heater icon" style="float:left;width:87px;height:110px;"><h2>&nbsp;&nbsp; UNKNOWN </h2></p>';
    echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="heaterOn" type="button" class="btn btn-success" onClick="controlDevicesLR(this.id)">TURN ON</button>';
    echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="heaterOff" type="button" class="btn btn-danger" onClick="controlDevicesLR(this.id)">TURN OFF</button>';
} else {
    echo'    <p><img src="icons\\heater-1.svg" alt="heater icon" style="float:left;width:87px;height:110px;"><h2>&nbsp;&nbsp;' .$data["heating"]. ' </h2></p>';
    if ($data["heating"] == "OFF") {
        echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="heaterOn" type="button" class="btn btn-success" onClick="controlDevicesLR(this.id)">TURN ON</button>';
    }else if ($data["heating"] == "ON") {
        echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="heaterOff" type="button" class="btn btn-danger" onClick="controlDevicesLR(this.id)">TURN OFF</button>';
    }
}
echo'  </div>';
echo'  <div class="column2-livingRoom" style="background-color:#ccc;">';
echo'    <h2>Air Condition</h2>';
if ($data["ac"] == "NONE") {
    echo'    <p><img src="icons\\air-conditioner-1.svg" alt="ac icon" style="float:left;width:87px;height:110px;"><h2>&nbsp;&nbsp; UNKNOWN </h2></p>';
    echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="acOn" type="button" class="btn btn-success" onClick="controlDevicesLR(this.id)">TURN ON</button>';
    echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="acOff" type="button" class="btn btn-danger" onClick="controlDevicesLR(this.id)">TURN OFF</button>';
}else {
    echo'    <p><img src="icons\\air-conditioner-1.svg" alt="ac icon" style="float:left;width:87px;height:110px;"><h2>&nbsp;&nbsp;' .$data["ac"]. ' </h2></p>';
    if ($data["ac"] == "OFF") {
        echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="acOn" type="button" class="btn btn-success" onClick="controlDevicesLR(this.id)">TURN ON</button>';
    }else if ($data["ac"] == "ON") {
        echo'    &nbsp;&nbsp;&nbsp;&nbsp;<button id="acOff" type="button" class="btn btn-danger" onClick="controlDevicesLR(this.id)">TURN OFF</button>';
    }
}
echo'  </div>';
echo'</div>';

?>

<script>
    function controlDevicesLR(buttonId) {
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
                case "lampOn":
                    lampStatus = "ON";
                    break;
                case "lampOff":
                    lampStatus = "OFF";
                    break;
                case "heaterOn":
                    heatingStatus = "ON";
                    break;
                case "heaterOff":
                    heatingStatus = "OFF";
                    break;
                case "acOn":
                    acStatus = "ON";
                    break;
                case "acOff":
                    acStatus = "OFF";
                    break; 
                default:
                    if(!alert("Something was wrong.\nTry again later...")){window.location.reload();}
                    return;
            }

            
            let page = "saveCommandOnDb.php";
            let parameters = 'room=' + 'LivingRoom' + '&' + 'lampSwitch=' + lampStatus + '&' + 'heatingSwitch=' + heatingStatus + '&' + 'acSwitch=' + acStatus + '&' + 'tentsSwitch=' + tentsStatus;
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
    function changeDesiredTempLR() {
        //  Allowed temperature limits
        highTempLimit = 30;
        lowTempLimit = 18;

        newDesiredTempVal = document.getElementById("newDesiredTempLR").value;
        console.log("new desired temp: ",newDesiredTempVal);
        // check if new value is in the allowed limits
        if (newDesiredTempVal <= highTempLimit && newDesiredTempVal >= lowTempLimit) {
            // update desired room temperature on DB
            console.log("SUCCESS");

            let page = "updateRoomDesiredTemp.php";
            let parameters = 'room=' + 'LivingRoom' + '&' + 'desiredTemp=' + newDesiredTempVal;
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

</script>

</body>
</html> 


