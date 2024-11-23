<?php
include 'dbcon.php';
session_start();

// Display message if it exists
if (isset($_SESSION['message'])) {
    echo "<div style='color: red; text-align: center; margin: 20px;'>" . htmlspecialchars($_SESSION['message']) . "</div>";
    unset($_SESSION['message']); // Unset message so it doesn't show again
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuMa - Student Dashboard</title>
    <link rel="stylesheet" href="source/css/bsf-admin-subject-view.css">
    <link rel="stylesheet" href="source/css/admin-subject-view.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* General Styles for Desktop and wider screens */
        .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .nav-links .left-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #007bff;
            padding: 12px 20px;
            border: 2px solid #007bff;
            border-radius: 5px;
            transition: background 0.3s, color 0.3s;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover {
            background: #007bff;
            color: white;
        }

        .logout-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-button:hover {
            background: #c82333;
        }

        /* Media Query for Narrow Screens (less than 768px) */
        @media (max-width: 768px) {

            /* Center h1 and navigation links */
            .container {
                text-align: center;
            }

            .nav-links {
                flex-direction: column;
                gap: 10px;
            }

            .nav-links .left-links {
                justify-content: center;
                gap: 10px;
            }

            .nav-links a {
                width: 100%;
                /* Full width links */
                text-align: center;
                justify-content: center;
            }

            /* Center the logout button when the screen is narrow */
            .subjects-container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .logout-button-container {
                display: block;
                text-align: center;
                margin-top: 20px;
            }

            /* Move the logout button to the bottom of the page */
            .logout-button {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                width: auto;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 900px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Modal responsiveness */
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 10% auto;
                padding: 10px;
            }

            .modal-content iframe {
                height: 50vh;
            }

            .close {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .modal-content iframe {
                height: 45vh;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <div class="header-wrapper" id="header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-hero-wrapper">
                    <span class="header-hero font-poppins-bold">QM</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Student Dashboard</h1>
        <div class="nav-links">
            <div class="left-links">
                <a href="sprofile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="javascript:void(0);" id="joinSubjectBtn"><i class="fas fa-book"></i> Join Subject</a>
            </div>
        </div>

        <div class="subjects-container">
            <?php
            include 'view_subjects.php';
            ?>
        </div>

        <div class="logout-button-container">
            <form action="logout.php" method="post">
                <button type="submit" class="logout-button"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </div>

    <!-- The Modal for Join Subject -->
    <div id="joinSubjectModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <iframe src="join_subject.php" width="100%" height="500px" style="border: none;"></iframe>
        </div>
    </div>

    <!-- JS Functions -->
    <script src="source/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="source/js/inputPreventE.js"></script>
    <script src="source/js/clearModalFIelds.js"></script>
    <script src="source/js/toggleSidebar.js"></script>
    <script src="source/js/profilePicture.js"></script>
    <script src="source/js/checkSubjectQuiz.js"></script>
    <script src="source/js/editProfile.js"></script>
    <script src="source/js/sidebarProfileCenter.js"></script>
    <script src="source/js/syncSidebar.js"></script>
    <script src="source/js/stickyBackground.js"></script>
    <script src="source/js/tablePagination.js"></script>

    <script>
        // Get the modal
        var modal = document.getElementById("joinSubjectModal");

        // Get the button that opens the modal
        var btn = document.getElementById("joinSubjectBtn");

        // Get the <span> element that closes the modal
        var span = document.getElementById("closeModal");

        // When the user clicks the button, open the modal
        btn.onclick = function () {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>