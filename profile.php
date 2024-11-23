<?php
include 'dbcon.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['student', 'admin'])) {
    header("Location: index.php");
    exit;
}

// Determine the table and ID column based on user role
if ($_SESSION['user_role'] === 'student') {
    $table = 'student';
    $id_column = 'student_id';
    $user_id = $_SESSION['user_id'];
} elseif ($_SESSION['user_role'] === 'admin') {
    $table = 'admin';
    $id_column = 'admin_id';
    $user_id = $_SESSION['user_id'];
} else {
    echo "Invalid user role.";
    exit;
}

// Fetch user profile data
$query = "SELECT * FROM $table WHERE $id_column = ?";
$stmt = $pdoConnect->prepare($query);
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    echo "User not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/source/css/bsf-admin-dashboard.css">
    <link rel="stylesheet" href="/source/css/admin-dashboard.css">
    <link rel="stylesheet" href="/source/css/fonts.css">
    <link rel="icon" type="image/x-icon" href="/assets/pictures/QM Logo.png">
    <title>QuMa User Profile</title>
</head>

<body>
    <div class="main-profile-wrapper">
        <div class="card profile-card-wrapper">
            <div class="card-header profile-card-header">
                <div class="profile-picture-wrapper">
                    <label for="upload-image" class="upload-label">
                        <div class="image-wrapper">
                            <img src="<?php echo isset($user_data['profile_image']) ? 'data:image/jpeg;base64,' . base64_encode($user_data['profile_image']) : '/assets/pictures/default_profile_image.jpg'; ?>"
                                alt="user-image" class="user-image" id="user-image">
                                <img src="<?php echo htmlspecialchars($admin_image); ?>" alt="Admin Profile Picture" class="admin-profile-image" style="display: block; width: 100px; height: 100px; object-fit: cover;">
                            <div class="hover-overlay font-rubik-medium">
                                <div class="hover-text">Change Picture</div>
                            </div>
                        </div>
                    </label>
                    <input type="file" id="upload-image" accept="image/*">
                </div>
            </div>
            <div class="card-body profile-card-body">
                <div class="profile-card-details">
                    <div class="profile-card-name-wrapper">
                        <span class="profile-card-name-label font-rubik-medium">NAME</span>
                        <div class="profile-card-name-content">
                            <span class="profile-card-name font-poppins-semibold"
                                id="name"><?php echo htmlspecialchars($user_data['fname'] . ' ' . $user_data['mi'] . ' ' . $user_data['lname']); ?></span>
                        </div>
                    </div>
                    <div class="profile-card-id-wrapper">
                        <span class="profile-card-id-label font-rubik-medium">SCHOOL ID</span>
                        <div class="profile-card-id-content">
                            <span class="profile-card-id font-poppins-semibold"
                                id="id"><?php echo htmlspecialchars($user_data[$id_column]); ?></span>
                        </div>
                    </div>
                    <div class="profile-card-account-wrapper">
                        <span class="profile-card-account-label font-rubik-medium">ACCOUNT</span>
                        <div class="profile-card-account-content">
                            <span class="profile-card-account font-poppins-semibold"
                                id="account-type"><?php echo ucfirst($_SESSION['user_role']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-profile-details-wrapper">
            <div class="form-floating profile-details-last-name">
                <input type="text" class="form-control font-rubik" id="last-name" placeholder="Last Name" readonly
                    value="<?php echo htmlspecialchars($user_data['lname']); ?>">
                <label for="last-name" class="font-rubik">Last Name</label>
            </div>
            <div class="form-floating profile-details-first-name">
                <input type="text" class="form-control font-rubik" id="first-name" placeholder="First Name" readonly
                    value="<?php echo htmlspecialchars($user_data['fname']); ?>">
                <label for="first-name" class="font-rubik">First Name</label>
            </div>
            <div class="form-floating profile-details-middle-name">
                <input type="text" class="form-control font-rubik" id="middle-name" placeholder="Middle Name" readonly
                    value="<?php echo htmlspecialchars($user_data['mi']); ?>">
                <label for="middle-name" class="font-rubik">Middle Name</label>
            </div>
            <div class="form-floating profile-details-campus">
                <input type="text" class="form-control font-rubik" id="campus" placeholder="Campus" readonly
                    value="Lubao">
                <label for="campus" class="font-rubik">Campus</label>
            </div>
            <div class="form-floating profile-details-department">
                <input type="text" class="form-control font-rubik" id="department" placeholder="Department" readonly
                    value="<?php echo htmlspecialchars($user_data['department']); ?>">
                <label for="department" class="font-rubik">Department</label>
            </div>
            <div class="profile-details-button-wrapper">
                <button type="button" class="btn profile-details-buttons profile-details-edit-profile-btn font-rubik"
                    id="edit-profile-btn">Edit Profile</button>
                <button type="button"
                    class="btn profile-details-buttons profile-details-cancel-edit-profile-btn font-rubik"
                    id="cancel-edit-profile-btn">Cancel</button>
                <button type="button"
                    class="btn profile-details-buttons profile-details-confirm-edit-profile-btn font-rubik"
                    id="confirm-edit-profile-btn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('edit-profile-btn').addEventListener('click', function () {
            // Enable the input fields for editing
            document.getElementById('last-name').readOnly = false;
            document.getElementById('first-name').readOnly = false;
            document.getElementById('middle-name').readOnly = false;
            document.getElementById('department').readOnly = false;
        });

        document.getElementById('confirm-edit-profile-btn').addEventListener('click', function () {
            // Get values from input fields
            var lastName = document.getElementById('last-name').value;
            var firstName = document.getElementById('first-name').value;
            var middleName = document.getElementById('middle-name').value;
            var department = document.getElementById('department').value;

            // Create an AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "save_profile.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        alert(response.message); // Show success message
                        window.location.href = "admindashboard.php"; // Refresh the page
                    } else {
                        alert("Error: " + response.message); // Show error message if needed
                    }
                }
            };
            xhr.send("last_name=" + encodeURIComponent(lastName) + "&first_name=" + encodeURIComponent(firstName) + "&middle_name=" + encodeURIComponent(middleName) + "&department=" + encodeURIComponent(department));
        });

        document.getElementById('cancel-edit-profile-btn').addEventListener('click', function () {
            // Reset the fields to readonly
            document.getElementById('last-name').readOnly = true;
            document.getElementById('first-name').readOnly = true;
            document.getElementById('middle-name').readOnly = true;
            document.getElementById('department').readOnly = true;
        });

        //upload picture
        document.getElementById('upload-image').addEventListener('change', function (event) {
            var file = event.target.files[0];
            if (file) {
                var formData = new FormData();
                formData.append('profile_image', file);

                // Create an AJAX request to upload the image
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "upload_image.php", true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            // Update the displayed image
                            document.getElementById('user-image').src = 'data:image/jpeg;base64,' + response.image_data;
                            alert(response.message);
                            window.location.reload(); // Refresh the page
                        } else {
                            alert("Error: " + response.message);
                        }
                    }
                };
                xhr.send(formData);
            }
        });
    </script>

</body>

</html>