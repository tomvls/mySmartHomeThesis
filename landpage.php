<?php
    if (strcmp($_COOKIE['pass'],'123') != 0) {
        header("Location:login.php");
    }
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <style>
    
    body {font-family: Arial;}

    /* Style the tab */
    .tab {
      overflow: hidden;
      border: 1px solid #ccc;
      background-color: #f1f1f1;
    }

    /* Style the buttons inside the tab */
    .tab button {
      background-color: inherit;
      float: left;
      border: none;
      outline: none;
      cursor: pointer;
      padding: 14px 36px;
      transition: 0.3s;
      font-size: 17px;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
      background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
      background-color: #ccc;
    }

    /* Style the tab content */
    .tabcontent {
      display: none;
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-top: none;
      animation: fadeEffect 0.5s; /* Fading effect takes 1 second */
    }

    .sensor {
        height: 50;
        width: 63;
    }

    /* Go from zero to full opacity */
    @keyframes fadeEffect {
        from {opacity: 0;}
        to {opacity: 1;}
    }
    </style>
</head>
<body>
    
    <br/>
    <center><h1>My Smart Home <img src="icons\home.svg" alt="Flowers in Chania" height="67" width="80"></h1></center>
    <br/>
    <?php

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "myDB";
        
        try {
            // Get Smart Home Mode from db
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("SELECT mode 
                                    FROM SmartHomeInfo");
            $stmt->execute(); 
            $data = $stmt->fetch();
            $homeMode = $data['mode'];

            echo "Home Mode: &nbsp;" . $homeMode;
            echo "<form class='form-inline'>";
            echo "    <select name='modes' id='mode'>";
            echo "        <option value='MANUAL'>MANUAL</option>";
            echo "        <option value='AUTO'>AUTO</option>";
            echo "        <option value='POWER-SAVING'>POWER-SAVING</option>";
            echo "        <option value='AWAY'>AWAY</option>";
            echo "    </select>";
            // echo "    <button type='button' class='btn btn-sm btn-success' onClick='updateHomeMode()'>Update</button>";
            echo "      <button onClick='updateHomeMode()'>Update</button>";
            echo "</form>";
            echo "</br>";
        }catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $conn = null;
        
    ?>

    <div class="tab">
        <button class="tablinks" onclick="openTab(event, 'livingRoom')" id="tab1">Living Room</button>
        <button class="tablinks" onclick="openTab(event, 'kitchen')" id="tab2">Kitchen</button>
        <button class="tablinks" onclick="openTab(event, 'outside')" id="tab3">Outside</button>
        <button class="tablinks" onclick="openTab(event, 'metrics')" id="tab4">Metrics</button>
        <!-- <button class="tablinks" onclick="openTab1(event, 'metrics')">Metrics</button> -->
		<!-- <button class="tablinks" onclick="openTab(event, 'modes')">Modes</button> -->
		<button type="button" class="btn btn-outline-danger" onClick='onLogOut()' style="float: right;">Logout</button>
    </div>

    
    <div id="livingRoom" class="tabcontent">
        <?php 
            include 'livingRoom.php';
        ?>
    </div>

    <div id="kitchen" class="tabcontent">
        <?php 
            include 'kitchen.php';
        ?>
    </div>

    <div id="outside" class="tabcontent">
        <?php 
            include 'outside.php';
        ?>
    </div>

	<div id="metrics" class="tabcontent">
        <?php 
            include 'metrics.php';
        ?>
    </div>

    <!-- <div id="metrics" class="tabcontent">
        <?php 
            include 'metrics.php';
        ?>
    </div> -->
    
    <script>
        function openTab1(event, tab) {
            document.location.href = '/smartHome/metrics.php';
        }

        function openTab(event, tab) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tab).style.display = "block";
            event.currentTarget.className += " active";
            let activeTab;
            switch (tab) {
                case "livingRoom":
                    activeTab = "tab1";
                    break;
                case "kitchen":
                    activeTab = "tab2";
                    break;
                case "outside":
                    activeTab = "tab3";
                    break;
                case "metrics":
                    activeTab = "tab4";
                    break;
                default:
            }
            localStorage.setItem('activeTab', activeTab);
        }

        // Get the element with id="defaultOpen" and click on it
        // document.getElementById("tab1").click();
        
        async function onLogOut() {
            if (confirm('Are you sure you want to logout?') == true) {
                // await eraseCookie("pass");
                eraseCookie("pass");
                document.location.href = '/smartHome/login.php';
            }
        }

    </script>

    <script>
        function updateHomeMode() {

            newMode = document.getElementById("mode").value;
            console.log("New mode: ",newMode);
            // check if new value is in the allowed limits
            if (newMode === "MANUAL" || newMode == "AUTO" || newMode == "AWAY" || newMode == "POWER-SAVING") {
                // update desired room temperature on DB
                console.log("SUCCESS");

                let page = "updateHomeMode.php";
                let parameters = 'mode=' + newMode;
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
                console.log(`Mode ${newMode} is invalid!`);
                // document.getElementById("tempOfLimitsWarning").removeAttribute("hidden"); 
                alert('Invalid requested mode! Allowed values are "MANUAL", "AUTO", "AWAY" and "POWER-SAVING".');
                
            }
        }

        $(document).ready(function() {
            let tabToOpen = localStorage.getItem("activeTab");
            if(  typeof tabToOpen !== 'undefined' ) {
                document.getElementById(tabToOpen).click();
            }else {
                document.getElementById("tab1").click();
            }
        });
    </script>

    <!-- <script>
    function autorefresh() {
        window.location.reload();
        // window.location = window.location.href;
    }

    setInterval(autorefresh, 5000);
    </script> -->
   
   <script>
        function eraseCookie(name) {   
            document.cookie = name + '=; Max-Age=0; path=/; domain=' + location.host; 
        }
    </script>

</body>
</html> 
