<?php


$history = "";
// $dashboard = "";
$home = "";
$settings = "";
$live = "";
$noOfRooms = 12;
$noOfParameters = 5;
$pagesLocations = array("../main/live.php,../main/dashboard.php,../main/history.php,../main/home.php,../main/login.php");

$history =  "style='background-color:#fed215;font-weight:bolder;'";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link href="../css/history2.css" rel="stylesheet" />
    <link href="../css/bootstrap.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <link href="../images/icon1.png" rel="icon" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php

            require "myNav.php";

            ?>
            <div class="col-12">
                <form>
                    <div class="row text-center tempChart p-4">
                        <h3 id="device" class="badge bg-dark mx-auto fs-4" style="width:fit-content;">DEVICE 0007</h3>
                        <h1 id="topic">Temperature</h1>
                        <canvas id="paraChart" style="width:100%;max-width: 1500px;margin-left:auto;margin-right:auto;"></canvas>
                        <div class="d-block d-sm-flex my-3 col-12 p-1 justify-content-center">
                            <!-- duration -->
                            <select name="duration" id="timeInterval" class="form-select mx-auto my-3" style="width:200px;">
                                <option value="oneH">Last One Hour</option>
                                <option value="24H">Last 24 Hours</option>
                                <option value="oneW">Last One Week</option>
                                <option value="all">All</option>
                            </select>
                            <!-- duration -->
                            <!-- parameter -->
                            <select name="parameter" id="parameter" class="form-select mx-auto my-3" style="width:200px;margin-left:20px;">
                                <option value="Temp">Temperature</option>
                                <option value="Humidity">Relative Humidity</option>
                                <option value="Pressure">Biometric Air Pressure</option>
                                <option value="CO2">CO2</option>
                                <option value="TVOC">Total Volatile Organic Compounds (TVOC)</option>
                            </select>
                            <!-- parameter -->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/9.22.1/firebase-app.js";
        import {
            getAnalytics
        } from "https://www.gstatic.com/firebasejs/9.22.1/firebase-analytics.js";
        import {
            getDatabase,
            ref,
            child,
            get
        } from "https://www.gstatic.com/firebasejs/9.22.1/firebase-database.js";

        console.log('running');

        const firebaseConfig = {
            apiKey: "AIzaSyCZV35Sd2Qo14fz3XORPncs7TudDTVRFLk",
            authDomain: "airqualitymonitoringsyst-87ae7.firebaseapp.com",
            databaseURL: "https://airqualitymonitoringsyst-87ae7-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "airqualitymonitoringsyst-87ae7",
            storageBucket: "airqualitymonitoringsyst-87ae7.appspot.com",
            messagingSenderId: "451013569860",
            appId: "1:451013569860:web:bf8e9bc4946c3b13f3bb11",
            measurementId: "G-NBW598T1DB"
        };

        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);

        const dbRef = ref(getDatabase());

        var interval = "oneH";
        var para = "Temp";

        var paraName = "Temp";
        var paraValue = "31";

        var paraMin = 0;
        var paraMax = 100;

        var chart;

        UpdateData();

        var selectedInterval = document.getElementById('timeInterval');
        selectedInterval.addEventListener('change', function() {
            interval = selectedInterval.value;
            chart.destroy();
            UpdateData();
        });

        var selectedParameter = document.getElementById("parameter");
        selectedParameter.addEventListener('change', function() {
            para = selectedParameter.value;

            chart.destroy();

            switch (para) {
                case "Temp": {
                    paraMin = 0;
                    paraMax = 125;
                    document.getElementById("topic").innerHTML = "Temperature";
                    break;
                }
                case "Humidity": {
                    paraMin = 0;
                    paraMax = 100;
                    document.getElementById("topic").innerHTML = "Relative Humidity";
                    break;
                }
                case "Pressure": {
                    paraMin = 30000;
                    paraMax = 110000;
                    document.getElementById("topic").innerHTML = "Biometric Air Pressure";
                    break;
                }
                case "CO2": {
                    paraMin = 400;
                    paraMax = 60000;
                    document.getElementById("topic").innerHTML = "CO2";
                    break;
                }
                case "TVOC": {
                    paraMin = 0;
                    paraMax = 60000;
                    document.getElementById("topic").innerHTML = "Total Volatile Organic Compounds (TVOC)";
                    break;
                }
            }

            UpdateData();
        });

        function UpdateData() {
            var xValues = [];
            var yValues = [];

            get(child(dbRef, "User02/Room08")).then((snapshot) => {
                if (snapshot.exists()) {

                    var maximumValue = 0;

                    snapshot.forEach(function(value) {
                        var childObject = value.val();

                        Object.keys(childObject).forEach(e => {
                            paraName = e.toString();
                            if (paraName == para) {
                                paraValue = childObject[e];

                                switch (para) {
                                    case "Temp": {
                                        if (Number(paraValue) > -50) {
                                            xValues.push(value.key);
                                            yValues.push(paraValue);
                                        }
                                        break;
                                    }
                                    case "Humidity": {
                                        if (Number(paraValue) > 0) {
                                            xValues.push(value.key);
                                            yValues.push(paraValue);
                                        }
                                        break;
                                    }
                                    case "Pressure": {
                                        if (Number(paraValue) > 30000) {
                                            xValues.push(value.key);
                                            yValues.push(paraValue);
                                        }
                                        break;
                                    }
                                    case "CO2": {
                                        if (Number(paraValue) > 400) {
                                            xValues.push(value.key);
                                            yValues.push(paraValue);

                                            if (maximumValue < paraValue) {
                                                maximumValue = paraValue;
                                            }
                                        }
                                        break;
                                    }
                                    case "TVOC": {
                                        if (Number(paraValue) >= 0) {
                                            xValues.push(value.key);
                                            yValues.push(paraValue);

                                            if (maximumValue < paraValue) {
                                                maximumValue = paraValue;
                                            }
                                        }
                                        break;
                                    }
                                }

                            }
                        });
                    });
                } else {
                    alert("Invalid");
                }

                if (para == "TVOC" || para == "CO2") {
                    paraMax = maximumValue;
                }
                DrawChart(xValues, yValues);

            }).catch((error) => {
                alert(error);
            });
        }

        function DrawChart(xValues, yValues) {
            var backgroundColor;
            var borderColor;

            switch (para) {
                case "Temp":
                    backgroundColor = "rgba(255,0,0,1.0)";
                    borderColor = "rgba(255,0,0,0.8)";
                    break;
                case "Humidity":
                    backgroundColor = "rgba(30,144,255,1.0)";
                    borderColor = "rgba(30,144,255,0.8)";
                    break;
                case "Pressure":
                    backgroundColor = "rgba(205,133,63,1.0)";
                    borderColor = "rgba(205,133,63,0.8)";
                    break;
                case "CO2":
                    backgroundColor = "rgba(34,139,34,1.0)";
                    borderColor = "rgba(34,139,34,0.8)";
                    break;
                case "TVOC":
                    backgroundColor = "rgba(255,140,0,1.0)";
                    borderColor = "rgba(255,140,0,0.8)";
                    break;
            }

            chart = new Chart("paraChart", {
                type: "line",
                data: {
                    labels: xValues,
                    datasets: [{
                        fill: false,
                        lineTension: 0,
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        data: yValues
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                min: paraMin,
                                max: paraMax
                            }
                        }]
                    }
                }
            });
        }
    </script>
    <script src="../js/script.js"></script>
</body>

</html>