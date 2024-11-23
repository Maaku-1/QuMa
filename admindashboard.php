<?php
include 'dbcon.php';
session_start();

include 'add_subject.php';
include 'change_password.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

try {
    // Fetch admin details from the database
    $stmt = $pdoConnect->prepare("SELECT fname, mi, lname, admin_id, profile_image FROM admin WHERE admin_id = :admin_id");
    $stmt->bindParam(':admin_id', $_SESSION['user_id']); // Assuming the admin ID is stored in the session
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $admin_name = $admin['fname'] . ' ' . $admin['mi'] . ' ' . $admin['lname'];
        $admin_id = $admin['admin_id'];
        $admin_image = $admin['profile_image'] ? 'data:image/jpeg;base64,' . base64_encode($admin['profile_image']) : 'assets/pictures/QM Logo.png';
        // Adjust default image as necessary
    } else {
        $admin_name = "Name N. Name";
        $admin_id = "1234567890";
        $admin_image = 'assets/pictures/default_profile_image.jpg'; // Default image if none is set
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload-image']) && $_FILES['upload-image']['error'] === UPLOAD_ERR_OK) {
    // Get the image file data
    $imageData = file_get_contents($_FILES['upload-image']['tmp_name']);
    $imageType = $_FILES['upload-image']['type']; // Get the MIME type

    try {
        // Update the profile image data in the database
        $stmt = $pdoConnect->prepare("UPDATE admin SET profile_image = :image WHERE admin_id = :admin_id");
        $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
        $stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $stmt->execute();

        // Refresh the page to show the new image
        header("Location: admindashboard.php");
        exit;
    } catch (PDOException $e) {
        echo "Error updating the database: " . $e->getMessage();
        exit;
    }
} else if (isset($_FILES['upload-image']['error']) && $_FILES['upload-image']['error'] !== UPLOAD_ERR_OK) {
    echo "Upload error code: " . $_FILES['upload-image']['error'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="source/css/bsf-admin-dashboard.css">
    <link rel="stylesheet" href="source/css/admin-dashboard.css">
    <link rel="stylesheet" href="source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="assets/pictures/QM Logo.png">
    <title>QuMa</title>
</head>

<body>
    <div class="admin-dashboard-wrapper" id="admin-dashboard">
        <div class="admin-dashboard-content">

            <!-- Sidebar -->
            <div class="sidebar-wrapper" id="sidebar">
                <div class="sidebar-content">
                    <div class="sidebar-profile-wrapper">
                        <div class="sidebar-profile-picture-wrapper">
                            <div class="sidebar-profile-picture-image-wrapper">
                                <img src="<?php echo htmlspecialchars($admin_image); ?>" alt="profile-picture"
                                    class="sidebar-profile-picture-user-image" id="profile-picture">
                                <!-- Display $admin_image --><img src="<?php echo htmlspecialchars($admin_image); ?>"
                                    alt="Admin Profile Picture" class="admin-profile-image"
                                    style="display: block; width: 50px; height: 50px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="sidebar-profile-details">
                            <span class="sidebar-profile-name font-rubik-medium" id="name"></span>
                        </div>
                    </div>

                    <div class="sidebar-divider">
                        <hr>
                    </div>

                    <div class="nav nav-pills" id="sidebar-tabs" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active font-rubik-medium" id="sidebar-subjects" data-bs-toggle="pill"
                            data-bs-target="#subject-content" " type=" button" role="tab"
                            aria-controls="sidebar-subjects" aria-selected="true">Subjects</button>
                        <button class="nav-link font-rubik-medium" id="sidebar-profile" data-bs-toggle="pill"
                            data-bs-target="#profile-content" " type=" button" role="tab"
                            aria-controls="sidebar-profile" aria-selected="false">Profile</button>
                        <button type="button" class="btn responsive-sidebar-settings-btn" data-bs-toggle="modal"
                            data-bs-target="#settings-modal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-tabs-footer-icon"
                                viewBox="0 -960 960 960">
                                <path
                                    d="M433-80q-27 0-46.5-18T363-142l-9-66q-13-5-24.5-12T307-235l-62 26q-25 11-50 2t-39-32l-47-82q-14-23-8-49t27-43l53-40q-1-7-1-13.5v-27q0-6.5 1-13.5l-53-40q-21-17-27-43t8-49l47-82q14-23 39-32t50 2l62 26q11-8 23-15t24-12l9-66q4-26 23.5-44t46.5-18h94q27 0 46.5 18t23.5 44l9 66q13 5 24.5 12t22.5 15l62-26q25-11 50-2t39 32l47 82q14 23 8 49t-27 43l-53 40q1 7 1 13.5v27q0 6.5-2 13.5l53 40q21 17 27 43t-8 49l-48 82q-14 23-39 32t-50-2l-60-26q-11 8-23 15t-24 12l-9 66q-4 26-23.5 44T527-80h-94Zm49-260q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Z" />
                            </svg>
                        </button>
                        <button type="button" class="btn sidebar-logout-btn" data-bs-toggle="modal"
                            data-bs-target="#logout-modal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-tabs-footer-icon"
                                viewBox="0 -960 960 960">
                                <path
                                    d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240q17 0 28.5 11.5T480-800q0 17-11.5 28.5T440-760H200v560h240q17 0 28.5 11.5T480-160q0 17-11.5 28.5T440-120H200Zm487-320H400q-17 0-28.5-11.5T360-480q0-17 11.5-28.5T400-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L669-309q-12 12-28.5 11.5T612-310q-11-12-10.5-28.5T613-366l74-74Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- HEADER -->
            <div class="header-wrapper" id="header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="header-sidebar-left-btn header-hamburger-btn" type="button"
                            id="toggle-sidebar-btn">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="header-sidebar-left-btn-icon header-hamburger-icon" viewBox="0 -960 960 960">
                                <path
                                    d="M160-240q-17 0-28.5-11.5T120-280q0-17 11.5-28.5T160-320h640q17 0 28.5 11.5T840-280q0 17-11.5 28.5T800-240H160Zm0-200q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h640q17 0 28.5 11.5T840-480q0 17-11.5 28.5T800-440H160Zm0-200q-17 0-28.5-11.5T120-680q0-17 11.5-28.5T160-720h640q17 0 28.5 11.5T840-680q0 17-11.5 28.5T800-640H160Z" />
                            </svg>
                        </button>
                        <div class="header-hero-wrapper">
                            <span class="header-hero font-poppins-bold">QM</span>
                        </div>
                    </div>

                    <div class="header-right">
                        <div class="dropdown">
                            <button type="button" class="header-create-join-button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="header-create-join-button-icon"
                                    viewBox="0 -960 960 960">>
                                    <path
                                        d="M440-440v120q0 17 11.5 28.5T480-280q17 0 28.5-11.5T520-320v-120h120q17 0 28.5-11.5T680-480q0-17-11.5-28.5T640-520H520v-120q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640v120H320q-17 0-28.5 11.5T280-480q0 17 11.5 28.5T320-440h120ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z" />
                                </svg>
                            </button>

                            <div class="dropdown-menu">
                                <div class="header-create-join-menu">
                                    <button type="button" class="header-create-join-menu-btn font-poppins-regular"
                                        data-bs-toggle="modal" data-bs-target="#create-subject-modal">
                                        <!-- Change the target here -->
                                        Create Subject
                                    </button>

                                    <!--
                                    <button type="button" class="header-create-join-menu-btn font-poppins-regular"
                                        data-bs-toggle="modal" data-bs-target="#join-subject-modal">
                                        Join Subject
                                    </button>
                                    -->
                                    
                                </div>
                            </div>
                        </div>
                        <button type="button" class="header-sidebar-right-btn header-hamburger-btn"
                            data-bs-toggle="offcanvas" data-bs-target="#responsive-sidebar"
                            aria-controls="offcanvasRight">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="header-sidebar-right-btn-icon header-hamburger-icon" viewBox="0 -960 960 960">
                                <path
                                    d="M160-240q-17 0-28.5-11.5T120-280q0-17 11.5-28.5T160-320h640q17 0 28.5 11.5T840-280q0 17-11.5 28.5T800-240H160Zm0-200q-17 0-28.5-11.5T120-480q0-17 11.5-28.5T160-520h640q17 0 28.5 11.5T840-480q0 17-11.5 28.5T800-440H160Zm0-200q-17 0-28.5-11.5T120-680q0-17 11.5-28.5T160-720h640q17 0 28.5 11.5T840-680q0 17-11.5 28.5T800-640H160Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main -->
            <div class="main-wrapper" id="main">
                <div class="main-content">
                    <div class="tab-content" id="main-tabs">
                        <div class="tab-pane fade show active" id="subject-content" role="tabpanel"
                            aria-labelledby="sidebar-subjects">
                            <?php include 'subject_list.php'; ?>
                        </div>
                        <div class="tab-pane fade" id="profile-content" role="tabpanel"
                            aria-labelledby="sidebar-profile">
                            <?php include 'profile.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMALL SCREEN | SIDEBAR -->
    <div class="offcanvas offcanvas-end" id="responsive-sidebar" aria-labelledby="Responsive Sidebar">
        <div class="offcanvas-header responsive-sidebar-header">
            <div class="responsive-sidebar-header-content" data-bs-dismiss="offcanvas">
                <div class="responsive-sidebar-close-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" class="responsive-sidebar-close-icon"
                        viewBox="0 -960 960 960">
                        <path
                            d="M504-480 348-636q-11-11-11-28t11-28q11-11 28-11t28 11l184 184q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L404-268q-11 11-28 11t-28-11q-11-11-11-28t11-28l156-156Z" />
                    </svg>
                </div>
                <div class="responsive-sidebar-profile-wrapper">
                    <div class="responsive-sidebar-profile-picture-image-wrapper">
                        <img src="<?php echo htmlspecialchars($admin_image); ?>" alt="profile-picture"
                            class="responsive-sidebar-profile-picture-user-image" id="responsive-profile-picture">
                        <!-- Display $admin_image --><img src="<?php echo htmlspecialchars($admin_image); ?>"
                            alt="Admin Profile Picture" class="admin-profile-image"
                            style="display: block; width: 50px; height: 50px; object-fit: cover;">
                    </div>
                    <div class="responsive-sidebar-profile-details">
                        <span class="sidebar-profile-name font-rubik-medium"
                            id="name"><?php echo htmlspecialchars($admin_name); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="responsive-sidebar-divider">
            <hr>
        </div>

        <div class="offcanvas-body responsive-sidebar-body">
            <div class="nav nav-pills" id="responsive-sidebar-tabs" role="tablist" aria-orientation="vertical">
                <button class="nav-link active font-rubik-medium" id="responsive-sidebar-subjects" data-bs-toggle="pill"
                    data-bs-target="#subject-content" " type=" button" role="tab" aria-controls="sidebar-subjects"
                    aria-selected="true">Subjects</button>
                <button class="nav-link font-rubik-medium" id="responsive-sidebar-profile" data-bs-toggle="pill"
                    data-bs-target="#profile-content" " type=" button" role="tab" aria-controls="sidebar-profile"
                    aria-selected="false">Profile</button>
                <button type="button" class="btn responsive-sidebar-settings-btn" data-bs-toggle="modal"
                    data-bs-target="#settings-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="responsive-sidebar-tabs-footer-icon"
                        viewBox="0 -960 960 960">
                        <path
                            d="M433-80q-27 0-46.5-18T363-142l-9-66q-13-5-24.5-12T307-235l-62 26q-25 11-50 2t-39-32l-47-82q-14-23-8-49t27-43l53-40q-1-7-1-13.5v-27q0-6.5 1-13.5l-53-40q-21-17-27-43t8-49l47-82q14-23 39-32t50 2l62 26q11-8 23-15t24-12l9-66q4-26 23.5-44t46.5-18h94q27 0 46.5 18t23.5 44l9 66q13 5 24.5 12t22.5 15l62-26q25-11 50-2t39 32l47 82q14 23 8 49t-27 43l-53 40q1 7 1 13.5v27q0 6.5-2 13.5l53 40q21 17 27 43t-8 49l-48 82q-14 23-39 32t-50-2l-60-26q-11 8-23 15t-24 12l-9 66q-4 26-23.5 44T527-80h-94Zm49-260q58 0 99-41t41-99q0-58-41-99t-99-41q-59 0-99.5 41T342-480q0 58 40.5 99t99.5 41Z" />
                    </svg>
                </button>
                <button type="button" class="btn responsive-sidebar-logout-btn" data-bs-toggle="modal"
                    data-bs-target="#logout-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="responsive-sidebar-tabs-footer-icon"
                        viewBox="0 -960 960 960">
                        <path
                            d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240q17 0 28.5 11.5T480-800q0 17-11.5 28.5T440-760H200v560h240q17 0 28.5 11.5T480-160q0 17-11.5 28.5T440-120H200Zm487-320H400q-17 0-28.5-11.5T360-480q0-17 11.5-28.5T400-520h287l-75-75q-11-11-11-27t11-28q11-12 28-12.5t29 11.5l143 143q12 12 12 28t-12 28L669-309q-12 12-28.5 11.5T612-310q-11-12-10.5-28.5T613-366l74-74Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- LOGOUT -->
    <div class="modal fade" id="logout-modal" data-bs-keyboard="false" aria-labelledby="Logout" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content logout-modal-content">
                <div class="modal-header logout-modal-header">
                    <span class="logout-header font-rubik-medium">Logout</span>
                </div>
                <div class="modal-body logout-modal-body">
                    <span class="logout-text font-rubik">Are you sure? You will be logged out.</span>
                </div>
                <div class="modal-footer logout-modal-footer">
                    <div class="logout-modal-buttons">
                        <button type="button" class="btn logout-modal-cancel font-rubik"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn logout-modal-confirm font-rubik"
                            onclick="location.href='logout.php'">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Functions -->
    <script src="source/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="source/js/inputPreventE.js"></script>
    <script src="source/js/clearModalFIelds.js"></script>
    <script src="source/js/toggleSidebar.js"></script>

    <script src="source/js/checkSubjectCards.js"></script>
    <script src="source/js/editProfile.js"></script>
    <script src="source/js/sidebarProfileCenter.js"></script>
    <script src="source/js/syncSidebar.js"></script>
    <script src="source/js/stickyBackground.js"></script>
    <script>
        document.getElementById('profile-picture').src = '<?php echo $admin_image; ?>';
        document.getElementById('responsive-profile-picture').src = '<?php echo $admin_image; ?>';
        document.getElementById('name').textContent = '<?php echo $admin_name; ?>';
    </script>


</body>

</html>