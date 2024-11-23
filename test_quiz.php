<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Testing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            max-width: 600px;
        }
        h1 {
            color: #333;
        }
    </style>
    <script>
        // Disable right-click and context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            alert('Right-click is disabled on this page.');
        });

        // Detect when the tab is inactive or the page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                alert('You cannot leave this quiz. Please stay on this page.');
                // Optionally, redirect or take action here
                // window.location.reload(); // Uncomment to reload page
            }
        });

        // Prevent common keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + R, Ctrl + Shift + R
            if ((e.ctrlKey && (e.key === 'r' || e.key === 'R')) || (e.ctrlKey && e.shiftKey && (e.key === 'R'))) {
                e.preventDefault();
                alert('Refreshing the page is disabled.');
            }
            // Ctrl + T, Ctrl + N
            if ((e.ctrlKey && (e.key === 't' || e.key === 'T')) || (e.ctrlKey && (e.key === 'n' || e.key === 'N'))) {
                e.preventDefault();
                alert('Opening a new tab/window is disabled.');
            }
            // Prevent F5
            if (e.key === 'F5') {
                e.preventDefault();
                alert('Refreshing the page is disabled.');
            }
            // Prevent Ctrl + W
            if (e.ctrlKey && e.key === 'w') {
                e.preventDefault();
                alert('Closing the tab/window is disabled.');
            }
        });

        // Prevent navigation away from the page
        window.addEventListener('beforeunload', function (e) {
            e.preventDefault();
            e.returnValue = ''; // This is required for some browsers to show a confirmation dialog
        });

        // Prevent going back in history
        function preventBack() {
            window.history.pushState(null, document.title, window.location.href);
        }

        preventBack(); // Initial push
        window.addEventListener('popstate', function(event) {
            preventBack(); // Push state again
            alert('You cannot navigate back from this quiz page.');
        });

        // Additional handling for mobile back button
        window.addEventListener('load', function() {
            preventBack(); // Push state on load

            // Use a touch event to prevent the back action
            document.addEventListener('touchstart', function() {
                preventBack();
            }, { passive: true });
        });
    </script>
</head>
<body>
    <h1>Quiz Testing Page</h1>
    <p>This is a test page for simulating quiz behavior.</p>
    <p>Please try right-clicking, changing tabs, or using keyboard shortcuts to see the restrictions in action.</p>
</body>
</html>
