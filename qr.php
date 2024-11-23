<?php
// Include the PHPQRCode library
require_once __DIR__ . '/phpqrcode/qrlib.php'; // Ensure the path is correct

// Function to get the local IP address that can be accessed externally
function getLocalIpAddress() {
    $output = [];
    exec('ipconfig', $output, $returnCode);

    if ($returnCode !== 0) {
        echo "Error executing command: ipconfig";
        exit(1);
    }

    $currentIp = null;
    $connectedAdapter = null;

    foreach ($output as $line) {
        // Skip disconnected adapters
        if (strpos($line, 'Media disconnected') !== false) {
            $connectedAdapter = null;
            continue;
        }

        // Detect Wireless LAN or Ethernet adapter
        if (preg_match('/adapter (Ethernet|Wi-Fi|Wireless):/i', $line, $matches)) {
            $connectedAdapter = $matches[1];
            continue;
        }

        // Extract the IPv4 address for the active adapter
        if ($connectedAdapter && strpos($line, 'IPv4 Address') !== false) {
            $currentIp = trim(substr($line, strpos($line, ':') + 1));
            break;
        }
    }

    return $currentIp;
}

// Get the local IP address
$ip = getLocalIpAddress();

// Output the IP address
if ($ip) {
    // Generate the QR code
    $qrCodePath = __DIR__ . '/assets/qr_code/qr.png'; // Use relative path for portability
    QRcode::png('http://' . $ip . '/QuizMaker1', $qrCodePath, 'L', 4, 2);

    // Display the QR code
    $link = 'http://' . $ip . '/QuizMaker1';
} else {
    echo "Unable to determine IP address";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        img {
            margin-bottom: 20px;
            width: 200px; /* Set the width of the QR code */
            height: 200px; /* Set the height of the QR code */
        }
        button {
            padding: 10px 15px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <img src="assets/qr_code/qr.png" alt="QR Code">
    <button onclick="copyLink()">Copy Link</button>

    <script>
        function copyLink() {
            const link = "<?php echo $link; ?>"; // Link to copy
            navigator.clipboard.writeText(link).then(() => {
                alert("Link copied to clipboard: " + link);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>

</body>
</html>
