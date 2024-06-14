<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sensormidterm";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle data insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $light = $_POST['light'];

    $sql = "INSERT INTO sensordata (temperature, humidity, light) VALUES ('$temperature', '$humidity', '$light')";

    if ($conn->query($sql) === TRUE) {
        echo "Data inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    exit;
}

// Fetch and return data as JSON for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchData'])) {
    $sql = "SELECT id, temperature, humidity, light, timestamp FROM sensordata ORDER BY timestamp DESC";
    $result = $conn->query($sql);

    $data = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Environmental Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Environmental Data</h2>

    <table id="data-table">
        <tr>
            <th>ID</th>
            <th>Temperature</th>
            <th>Humidity</th>
            <th>Light</th>
            <th>Timestamp</th>
        </tr>
    </table>
</div>

<script>
    function fetchData() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "data_handler.php?fetchData=true", true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                var table = document.getElementById("data-table");

                // Clear the existing rows except the header
                table.innerHTML = `
                    <tr>
                        <th>ID</th>
                        <th>Temperature</th>
                        <th>Humidity</th>
                        <th>Light</th>
                        <th>Timestamp</th>
                    </tr>
                `;

                // Populate the table with new data
                data.forEach(function(row) {
                    var tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.temperature}</td>
                        <td>${row.humidity}</td>
                        <td>${row.light}</td>
                        <td>${row.timestamp}</td>
                    `;
                    table.appendChild(tr);
                });
            }
        };
        xhr.send();
    }

    // Fetch data every 10 seconds
    setInterval(fetchData, 10000);

    // Initial data fetch
    fetchData();
</script>

</body>
</html>
