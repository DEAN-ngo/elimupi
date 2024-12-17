<?php
// Function to perform other API roles
function performAPIRole($action, $code) {
    // Extract the session token from the authentication data
    #$sessionToken = $_SESSION['api_auth_data']['sessionToken'];

    // Construct the API URL based on the $action parameter and use $sessionToken
    switch ($action) {
        case 'download_ovpn':
            // Retrieve form data
            $connect_id = $_GET['connect_id'];
            $apiUrl = "http://DEANVPN001.elimupi.dean/api/connect/download/ovpn?school_code=$code&connect_id=$connect_id";
            break;
        case 'download_dnsmasq':
            $apiUrl = "http://DEANVPN001.elimupi.dean/api/connect/download/dnsmasq?school_code=$code";
            break;

        case 'ssl_key':
            // Retrieve form data
            $apiUrl = "http://DEANVPN001.elimupi.dean/api/connect/download/key?school_code=$code";
            break;

        case 'ssl_cert':
            // Retrieve form data
            $apiUrl = "http://DEANVPN001.elimupi.dean/api/connect/download/crt?school_code=$code";
            break;
        default:
            echo 'Invalid action.';
            return;

    }
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt'); 
    $response = curl_exec($ch);
    return $response;
    // Close cURL session
    curl_close($ch);
}

// Check if other roles are requested
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['code'])) {
    $action = $_GET['action'];
    $code = $_GET['code'];
    header('action-Response: ' . $action);
    $authResponse = performAPIRole($action, $code);
    // Handle the response
    if ($authResponse === false) {
        echo 'NO response';
    }else{ 
        $output=null;
        $retval=null;
        if ($action === 'download_ovpn'){
            $fileExtension = '.ovpn';
            $url = "/etc/openvpn/client.ovpn";
            $fileName = 'client' . $fileExtension;
            file_put_contents($fileName, $authResponse);
            exec("sudo /var/www/html/apiconnect/copyscript.sh", $output,$retval);
            echo "Returned with status $retval and output:\n";
            print_r($output);
            header('Location: config_connect.php');
            exit();
        }elseif ($action === 'download_dnsmasq') {
            # code...
                // Save the response to a file with the corresponding extension
            $fileExtension = '.conf';
            $url = "/etc/dnsmasq.conf";
            $fileName = 'dnsmasq' . $fileExtension;
            file_put_contents($fileName, $authResponse);
            exec("sudo /var/www/html/apiconnect/copyscript.sh", $output,$retval);
            echo "Returned with status $retval and output:\n";
            print_r($output);
            header('Location: config_connect.php');
            exit();
        }elseif ($action === 'ssl_key') {
            # code..
            $fileExtension = '.key';
            $fileName = 'ssl' . $fileExtension;
            $url = "/etc/pki/tls/key/ssl.key";
            file_put_contents($fileName, $authResponse);
            exec("sudo /var/www/html/apiconnect/copyscript.sh", $output,$retval);
            echo "Returned with status $retval and output:\n";
            print_r($output);
            header('Location: config_connect.php');
            exit();
        }elseif ($action === 'ssl_cert') {
            # code...
            $fileExtension = '.crt';
            $fileName = 'ssl' . $fileExtension;
            $url = "/etc/pki/tls/certs/ssl.crt";
            file_put_contents($fileName, $authResponse);
            exec("sudo /var/www/html/apiconnect/copyscript.sh", $output,$retval);
            echo "Returned with status $retval and output:\n";
            print_r($output);
            header('Location: config_connect.php');
            exit();
        }
    }

}

?>    