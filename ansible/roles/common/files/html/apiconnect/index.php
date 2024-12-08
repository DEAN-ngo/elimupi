<?php
session_start();
//chdir(dirname(__FILE__));
// Function to perform API authentication and return the session cookie
function authenticateAPI($user, $password) {
    $apiUrl = "http://DEANVPN001.elimupi.dean/api/authenticate/logon?user=$user&password=$password";
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt'); // Store session cookies

    $response = curl_exec($ch);
    return $response;
    curl_close($ch);
}

// Check if form is submitted for API authentication
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user']) && isset($_GET['password'])) {
    $user = $_GET['user'];
    $password = $_GET['password'];

    $authResponse = authenticateAPI($user, $password);

    if ($authResponse === false) {
        // API request failed
        echo '<script type="text/javascript"> window.onload = function () { alert("Authentication failed"); } </script>';

      } else if($authResponse === 'Incorrect password'){
        echo '<script type="text/javascript"> window.onload = function () { alert("Authentication failed"); } </script>';
    }
     else {
        // Decode JSON response into an associative array
        // Convert the API response to JSON format (optional, but recommended for structured data)
        $jsonResponse = json_encode($authResponse);
        //echo '<script type="text/javascript"> window.onload = function () { alert(' . json_encode($authData) . '); } </script>';
        $_SESSION['api_auth_data'] = $authResponse;
        // Redirect to admin.php
        header('Location: admin.php');
        exit(); // Make sure to exit after redirection
    }
}
?>
<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../assets/css/tailwind.output.css" />
    <script
      src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"
      defer
    ></script>
    <script src="../assets/js/init-alpine.js"></script>
  </head>
  <body>
    <div class="flex items-center min-h-screen p-6 bg-gray-50 dark:bg-gray-900">
      <div class="flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl dark:bg-gray-800" >
        <div class="flex flex-col overflow-y-auto md:flex-row">
          <div class="h-32 md:h-auto md:w-1/2">
            <img
              aria-hidden="true"
              class="object-cover w-full h-full dark:hidden"
              src="../assets/img/login-office.jpeg"
              alt="Office"
            />
            <img
              aria-hidden="true"
              class="hidden object-cover w-full h-full dark:block"
              src="../assets/img/login-office-dark.jpeg"
              alt="Office"
            />
          </div>
          <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
            <div class="w-full">
              <h1
                class="mb-4 text-xl font-semibold text-gray-700 dark:text-gray-200"
              >
                Authenticate To The API
              </h1>
              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">username</span>
                <input
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="Doe"
                  id="user"
                  name='user'
                />
              </label>
              <label class="block mt-4 text-sm">
                <span class="text-gray-700 dark:text-gray-400">Password</span>
                <input
                  class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="***************"
                  type="password"
                  id="password"
                  name='password'
                />
              </label>

              <!-- You should use a button here, as the anchor is only used for the example  -->
              <button class="block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Submit
              </button>
              </form>
              <p class="mt-4">
                <a
                  class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline"
                  href="./forgot-password.html"
                >
                  Forgot your password?
                </a>
              </p>
              <p class="mt-1">
                <a
                  class="text-sm font-medium text-purple-600 dark:text-purple-400 hover:underline"
                  href="./create-account.php"
                >
                  Create account
                </a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
</body>
</html>