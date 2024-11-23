<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <script src="html5-qrcode.min.js"></script> <!-- Make sure this path is correct -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        #qr-reader {
            width: 100%;
            max-width: 500px;
            height: auto;
            border: 1px solid #ccc;
        }

        #result {
            margin-top: 20px;
            word-wrap: break-word;
        }

        #open-camera-btn {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        #error-message {
            color: red;
            margin-top: 20px;
        }

        /* Modal Styles */
        #camera-access-modal {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        #modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }

        #modal-buttons {
            margin-top: 15px;
        }

        #allow-btn,
        #cancel-btn {
            padding: 10px 15px;
            margin: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1>Scan QR Code</h1>
    <div id="qr-reader"></div>
    <div id="result"></div>
    <button id="open-camera-btn">Open Camera</button>
    <div id="error-message"></div> <!-- Error message display -->

    <!-- Camera Access Modal -->
    <div id="camera-access-modal">
        <div id="modal-content">
            <h2>Camera Access Required</h2>
            <p>We need access to your camera to scan QR codes. Would you like to allow camera access?</p>
            <div id="modal-buttons">
                <button id="allow-btn">Allow</button>
                <button id="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let html5QrcodeScanner;

        function onScanSuccess(decodedText, decodedResult) {
            // Display the result
            document.getElementById('result').innerText = decodedText;

            // Optional: Stop scanning after a successful scan
            html5QrcodeScanner.clear();
        }

        function onScanError(errorMessage) {
            // Handle scan error if necessary
            console.warn(`QR Code scan error: ${errorMessage}`);
        }

        function startScanning() {
            // Create instance of the scanner
            html5QrcodeScanner = new Html5Qrcode("qr-reader");

            // Start scanning
            html5QrcodeScanner.start(
                { facingMode: "environment" }, // Use back camera
                {
                    fps: 10, // Frames per second
                    qrbox: { width: 250, height: 250 } // Size of the scanning box
                },
                onScanSuccess,
                onScanError
            ).catch(err => {
                document.getElementById('error-message').innerText = "Error starting scanner: " + err;
            });
        }

        function requestCameraAccess() {
            return navigator.mediaDevices.getUserMedia({ video: true })
                .then(() => {
                    console.log("Camera access granted");
                    return true; // Access granted
                })
                .catch(err => {
                    console.error("Camera access denied: ", err);
                    return false; // Access denied
                });
        }

        // Attach event listener to the button
        document.getElementById('open-camera-btn').addEventListener('click', function() {
            requestCameraAccess().then(isGranted => {
                if (isGranted) {
                    startScanning(); // Start scanning if access is granted
                } else {
                    document.getElementById('camera-access-modal').style.display = 'flex'; // Show modal if access is denied
                }
            });
        });

        // Allow button functionality
        document.getElementById('allow-btn').addEventListener('click', function () {
            document.getElementById('camera-access-modal').style.display = 'none';
            requestCameraAccess().then(isGranted => {
                if (isGranted) {
                    startScanning(); // Start scanning after user allows access
                }
            });
        });

        // Cancel button functionality
        document.getElementById('cancel-btn').addEventListener('click', function () {
            document.getElementById('camera-access-modal').style.display = 'none';
            document.getElementById('error-message').innerText = "Camera access denied.";
        });
    </script>
</body>

</html>