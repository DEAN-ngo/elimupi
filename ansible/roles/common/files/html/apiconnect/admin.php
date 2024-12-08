<?php

session_start();

// Check if the API response data exists in the session
if (isset($_SESSION['api_auth_data'])) {
    // Retrieve the API response data from the session
    $authData = $_SESSION['api_auth_data'];
    // Display the API response in a popup window or anywhere on the page
    echo '<script type="text/javascript"> window.onload = function () { alert(' . json_encode($authData) . '); } </script>';
    // Clear the session data after displaying the response
    unset($_SESSION['api_auth_data']);
} else {
    // Handle the case where the API response data is not available in the session
    echo '';
}
// Construct the API URL with the form data
$apiUrl = "http://DEANVPN001.elimupi.dean/api/schools/list?";
// Initialize cURL session
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt'); 
$response = curl_exec($ch);
$lines = explode(" ", $response);
// Handle the response
if ($response === false) {
    // API request failed
    echo '';
} elseif ($response ==='No valid session'){
    echo '<script type="text/javascript"> window.onload = function () { alert(' . json_encode($response) . '); } </script>';

}else {
    // API request successful, do something with the response
    // Convert the API response to JSON format (optional, but recommended for structured data)
    $jsonResponse = json_encode($response);
    echo '<script type="text/javascript">var jsonResponse = ' . $jsonResponse . ';</script>';

    // Set the custom HTTP header to relay the API response
    // Print the response data as JSON
    //header('Content-Type: application/json');

}
curl_close($ch);

?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./assets/css/tailwind.output.css" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></>
    <script src="./assets/js/init-alpine.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" defer></script>
    <script src="./assets/js/charts-lines.js" defer></script>
    <script src="./assets/js/charts-pie.js" defer></script>
</head>

<body onload="processData()">
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <!-- Desktop sidebar -->
        <?php include ("nav.php"); ?>
        <!-- Mobile sidebar -->
        <!-- Backdrop -->
        <div class="flex flex-col flex-1 w-full">
            <?php include ("header.php"); ?>
            <main class="h-full overflow-y-auto">
                <div class="container px-6 mx-auto grid">
                    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                        Dashboard
                    </h2>
                    <!-- CTA -->

                    <!-- Cards -->
                    <div class="grid gap-6 mb-8 md:grid-cols-3 xl:grid-cols-6">
                        <!-- Card -->
                        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500">
                                <img class="w-5 h-5" src="images/school.png" alt="" loading="lazy"/>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Schools
                                </p>
                                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                                    200
                                </p>
                            </div>
                        </div>
                        <!-- Card -->
                        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full dark:text-green-100 dark:bg-green-500">
                               <img class="w-5 h-5"  src="images/router.png" alt="" loading="lazy"/>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Elimupi-Connect
                                </p>
                                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                                    50
                                </p>
                            </div>
                        </div>
                        <!-- Card -->
                        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full dark:text-blue-100 dark:bg-blue-500">
                              <img class="w-5 h-5" src="images/lifeline.png" alt="" loading="lazy"/>

                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Elimupi Devices
                                </p>
                                <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                                    376
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- New Table -->
                    <div class="w-full overflow-hidden rounded-lg shadow-xs">
                        <div class="w-full overflow-x-auto">
                        <table class="w-full whitespace-no-wrap">
                            <thead>
                                <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                    <th class="px-4 py-3" >School Code</th>
                                    <th class="px-4 py-3">School Name</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800" id="mytable">

                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
<script type="text/javascript">
    function processData() {
        const csvData = jsonResponse;

        // Split the CSV data into an array of strings.
        const lines = csvData.split("\r\n");

        // Create an empty array to store the data.
        const data = [];
        // Iterate over the lines array.
        for (const line of lines) {
            // Split the line into an array of values.
            var values = line.split(",");
            //document.getElementById('col1').innerHTML = values[0];
            //document.getElementById('col2').innerHTML = values[1];
            // Create a new object with the values.
            const school = {
                code: values[0],
                name: values[1],
            };
            data.push(school);
            // Add the school object to the data array.
        }

        // Print the data array.
        console.log(data);
        var tbody = document.getElementById("mytable");

        // Populate the table with the data
        for (var i = 1; i < data.length; i++) {
            var row = tbody.insertRow(i - 1);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            cell1.innerHTML = data[i]['code'];
            cell2.innerHTML = data[i]['name'];
        }
    }
</script>

</html>