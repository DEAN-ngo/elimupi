<?php
  // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Retrieve form data
        $name = $_GET['name'];
        $code = $_GET['code'];
        $school_code = $_GET['school_code'];
        $connect_id = $_GET['connect_id'];
        $description = $_GET['description'];
        $ip_range = $_GET['ip_range'];
        // Construct the API URL with the form data
        $apiUrl = "http://DEANVPN001.elimupi.dean/api/connect/update?school_code=$school_code&name=$name&code=$code&connect_id=$connect_id&description=$description&ip_range=$ip_range";
          $ch = curl_init($apiUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt'); 
          $response = curl_exec($ch);

          // Handle the response
          if ($response === false) {
              // API request failed
              echo '';
          } else {
              // API request successful, do something with the response
            echo '<script type="text/javascript"> window.onload = function () { alert(' . json_encode($response) . '); } </script>';
              // Convert the API response to JSON format (optional, but recommended for structured data)
              $jsonResponse = json_encode($response);

              // Set the custom HTTP header to relay the API response
              header('X-MyAPI-Response: ' . $jsonResponse);
              // ...
          }
          curl_close($ch);
    }

?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./assets/css/tailwind.output.css" />
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="./assets/js/init-alpine.js"></script>
</head>

<body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen}">
        <!-- Desktop sidebar -->
       <?php include ("nav.php"); ?> 
        <div class="flex flex-col flex-1">
          <?php include ("header.php"); ?>
          
            <main class="h-full pb-16 overflow-y-auto">
                <div class="container px-6 mx-auto grid">
                    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                        Update A Connect Device
                    </h2>
                    <!-- General elements -->
                    <h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">
                        This part updates a connect device 
                    </h4>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" METHOD='GET'>
                    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
                   
                        <label class="block mt-4 text-sm">
                          <span class="text-gray-700 dark:text-gray-400">Name</span>
                          <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Test1" name='name'/>
                        </label>
                        <label class="block mt-4 text-sm">
                          <span class="text-gray-700 dark:text-gray-400">Code</span>
                          <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="connect-abc-001" name='code'/>
                        </label>
                        <label class="block mt-4 text-sm">
                          <span class="text-gray-700 dark:text-gray-400">School Code</span>
                          <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="abc" name="school_code"/>
                        </label>
                        <label class="block mt-4 text-sm">
                          <span class="text-gray-700 dark:text-gray-400">Connect Id</span>
                          <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="001" name="connect_id"/>
                        </label>
                        <label class="block mt-4 text-sm">
                          <span class="text-gray-700 dark:text-gray-400">Description</span>
                          <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="New2" name="description"/>
                        </label>
                        <label class="block mt-4 text-sm">
                          <span class="text-gray-700 dark:text-gray-400">Ip Range</span>
                          <input class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="250" name="ip_range"/>
                        </label>
        
                        <button class="flex items-center justify-between w-full mt-4 px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                          Update
                          <span class="ml-2" aria-hidden="true">-></span>
                        </button>
                    </div>
                  </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
