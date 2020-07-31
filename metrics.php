<?php
    if (strcmp($_COOKIE['pass'],'123') != 0) {
        header("Location:login.php");
    }
 
$dataPointsTempLR = array();
$dataPointsHumidityLR = array();
$dataPointsTempK = array();
$dataPointsHumidityK = array();
//Best practice is to create a separate file for handling connection to database
try{
     // Creating a new connection.
    // Replace your-hostname, your-db, your-username, your-password according to your database
    $link = new \PDO(   'mysql:host=localhost;dbname=myDB;charset=utf8mb4', //'mysql:host=localhost;dbname=canvasjs_db;charset=utf8mb4',
                        'root', //'root',
                        '', //'',
                        array(
                            \PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_PERSISTENT => false
                        )
                    );
	
    $sql_temp_LivingRoom = $link->prepare('SELECT * 
    FROM LivingRoom
    ORDER BY reg_date DESC
    LIMIT 8640'); 
    $sql_temp_LivingRoom->execute(); 
    $result = $sql_temp_LivingRoom->fetchAll(\PDO::FETCH_OBJ);
		
    foreach($result as $row){
        $timestamp = strtotime($row->reg_date)*1000;
        // echo $timestamp;
        // $date = date('d-m-Y', $timestamp);
        // $time = date('Gi.s', $timestamp);

        // $datetimearray = explode(" ", $row->reg_date);
        // $date = $datetimearray[0];
        // $time = $datetimearray[1];
		array_push($dataPointsTempLR, array("x"=> $timestamp, "y"=> $row->temp));
		array_push($dataPointsHumidityLR, array("x"=> $timestamp, "y"=> $row->humidity));
	}
	
	$sql_temp_Kitchen = $link->prepare('SELECT * 
    FROM Kitchen
    ORDER BY reg_date DESC
    LIMIT 8640'); 
    $sql_temp_Kitchen->execute(); 
    $result = $sql_temp_Kitchen->fetchAll(\PDO::FETCH_OBJ);
	$link = null;

	foreach($result as $row){
        $timestamp = strtotime($row->reg_date)*1000;
        // echo $timestamp;
        // $date = date('d-m-Y', $timestamp);
        // $time = date('Gi.s', $timestamp);

        // $datetimearray = explode(" ", $row->reg_date);
        // $date = $datetimearray[0];
        // $time = $datetimearray[1];
		array_push($dataPointsTempK, array("x"=> $timestamp, "y"=> $row->temp));
		array_push($dataPointsHumidityK, array("x"=> $timestamp, "y"=> $row->humidity));
	}
}
catch(\PDOException $ex){
    print($ex->getMessage());
}


?>
<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function () {
 
var chart1 = new CanvasJS.Chart("chartContainer1", {
	animationEnabled: true,
	title:{
		text: "Temperature Living Room 12H"
	},
	axisY: {
		title: "temperature in Celcius",
		valueFormatString: "",
		suffix: "",
		prefix: ""
	},
	data: [{
		type: "spline",
		color: "rgba(255,12,32,.3)",
		markerSize: 5,
		xValueFormatString: "hh:mm",
		yValueFormatString: "",
		xValueType: "dateTime",
		dataPoints: <?php echo json_encode($dataPointsTempLR, JSON_NUMERIC_CHECK); ?>
	}]
});

var chart2 = new CanvasJS.Chart("chartContainer2", {
	animationEnabled: true,
	title:{
		text: "Temperature Kitchen 12H"
	},
	axisY: {
		title: "temperature in Celcius",
		valueFormatString: "",
		suffix: "",
		prefix: ""
	},
	data: [{
		type: "spline",
		color: "rgba(255,12,32,.3)",
		markerSize: 5,
		xValueFormatString: "hh:mm",
		yValueFormatString: "",
		xValueType: "dateTime",
		dataPoints: <?php echo json_encode($dataPointsTempK, JSON_NUMERIC_CHECK); ?>
	}]
});

var chart3 = new CanvasJS.Chart("chartContainer3", {
	animationEnabled: true,
	title:{
		text: "Humidity Living Room 12H"
	},
	axisY: {
		title: "humidity in percentage",
		valueFormatString: "",
		suffix: "",
		prefix: ""
	},
	data: [{
		type: "spline",
		markerSize: 5,
		xValueFormatString: "hh:mm",
		yValueFormatString: "",
		xValueType: "dateTime",
		dataPoints: <?php echo json_encode($dataPointsHumidityLR, JSON_NUMERIC_CHECK); ?>
	}]
});

var chart4 = new CanvasJS.Chart("chartContainer4", {
	animationEnabled: true,
	title:{
		text: "Humidity Kitchen 12H"
	},
	axisY: {
		title: "humidity in percentage",
		valueFormatString: "",
		suffix: "",
		prefix: ""
	},
	data: [{
		type: "spline",
		markerSize: 5,
		xValueFormatString: "hh:mm",
		yValueFormatString: "",
		xValueType: "dateTime",
		dataPoints: <?php echo json_encode($dataPointsHumidityK, JSON_NUMERIC_CHECK); ?>
	}]
});
 
chart1.render();
chart2.render();
chart3.render();
chart4.render();
 
}
</script>
</head>
<body>
<div id="chartContainer1" style="width: 45%; height: 300px;display: inline-block;"></div> 
<div id="chartContainer2" style="width: 45%; height: 300px;display: inline-block;"></div><br/><br/><br/><br/><br/><br/>
<div id="chartContainer3" style="width: 45%; height: 300px;display: inline-block;"></div>
<div id="chartContainer4" style="width: 45%; height: 300px;display: inline-block;"></div>
<!-- <div id="chartContainer1" style="height: 370px; width: 100%;"></div>
<div id="chartContainer2" style="height: 370px; width: 100%;"></div> -->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html> 
<!-- 
?>
<!DOCTYPE HTML>
<html>
<head>  
<script>
window.onload = function () {
 
var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	exportEnabled: true,
	theme: "light1", // "light1", "light2", "dark1", "dark2"
	title:{
		text: "PHP Column Chart from Database"
	},
	data: [{
		type: "column", //change type to bar, line, area, pie, etc  
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();
 
}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>                               -->

