<?php
include 'dbcon.php';

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

function changePassword($pdoConnect, $oldPassword, $newPassword, $confirmPassword, $userId) {
    // Fetch the current hashed password from the database
    $stmt = $pdoConnect->prepare("SELECT password FROM admin WHERE admin_id = :admin_id");
    $stmt->bindParam(':admin_id', $userId);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the old password matches the current password
    if ($admin && password_verify($oldPassword, $admin['password'])) {
        // Validate new password
        if (strlen($newPassword) < 8) {
            return ["success" => false, "message" => "New password must be at least 8 characters long."];
        }

        if ($newPassword !== $confirmPassword) {
            return ["success" => false, "message" => "New passwords do not match."];
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $updateStmt = $pdoConnect->prepare("UPDATE admin SET password = :new_password WHERE admin_id = :admin_id");
        $updateStmt->bindParam(':new_password', $hashedPassword);
        $updateStmt->bindParam(':admin_id', $userId);

        if ($updateStmt->execute()) {
            return ["success" => true, "message" => "Password changed successfully."];
        } else {
            return ["success" => false, "message" => "Error updating password."];
        }
    } else {
        return ["success" => false, "message" => "Old password is incorrect."];
    }
}

// Handle the change password request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old-password'] ?? '';
    $newPassword = $_POST['new-password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    $userId = $_SESSION['user_id'] ?? null; // Get the user ID from the session

    if ($userId === null) {
        echo json_encode(["success" => false, "message" => "User not logged in."]);
        exit;
    }

    $result = changePassword($pdoConnect, $oldPassword, $newPassword, $confirmPassword, $userId);
    echo json_encode($result); // Return JSON response
    exit; // Exit to prevent further output
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>

<body>
    <!-- SETTINGS -->
    <div class="modal fade" id="settings-modal" data-bs-keyboard="false" aria-labelledby="Remove Subject"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content settings-modal-content">
                <div class="modal-header settings-modal-header">
                    <span class="settings-header font-rubik-medium">Settings</span>
                </div>
                <div class="modal-body settings-modal-body">
                    <div class="settings-change-password-wrapper">
                        <span class="settings-label settings-change-password-label font-rubik">Reset your
                            Password</span>
                        <button type="button" class="btn settings-change-password-btn font-rubik" data-bs-toggle="modal"
                            data-bs-target="#settings-change-password-modal">
                            Change Password
                        </button>
                    </div>
                </div>
                <div class="modal-footer settings-modal-footer">
                    <div class="settings-buttons">
                        <button type="button" class="btn settings-close font-rubik"
                            data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SETTINGS | CHANGE PASSWORD MODAL -->
    <div class="modal fade" id="settings-change-password-modal" data-bs-keyboard="false"
        aria-labelledby="ChangePasswordModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content settings-change-password-modal-content">
                <div class="modal-header settings-modal-header">
                    <span class="settings-change-password-header font-rubik-medium">Change Password</span>
                </div>
                <form class="modal-body settings-change-password-modal-body" id="settings-change-password-form"
                    novalidate>
                    <div class="form-floating settings-old-password-wrapper">
                        <input type="password" class="form-control font-rubik" id="old-password"
                            placeholder="Enter your Old Password" required>
                        <label for="old-password" class="font-rubik">Enter Old Password</label>
                        <div class="invalid-feedback">Old password is incorrect!</div>
                        <div class="invalid-feedback">Old password must not be empty!</div>
                    </div>
                    <div class="form-floating settings-new-password-wrapper">
                        <input type="password" class="form-control font-rubik" id="new-password"
                            placeholder="Enter your New Password" required>
                        <label for="new-password" class="font-rubik">Enter New Password</label>
                        <div class="invalid-feedback">New password must be at least 8 characters long!</div>
                    </div>
                    <div class="form-floating settings-confirm-password-wrapper">
                        <input type="password" class="form-control font-rubik" id="confirm-password"
                            placeholder="Confirm your New Password" required>
                        <label for="confirm-password" class="font-rubik">Confirm New Password</label>
                        <div class="invalid-feedback">Passwords do not match!</div>
                    </div>
                </form>
                <div class="modal-footer settings-change-password-modal-footer">
                    <div class="settings-change-password-buttons">
                        <button type="button" class="btn settings-change-password-cancel font-rubik"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn settings-change-password-confirm font-rubik"
                            onclick="changePassword()">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function changePassword() {
        const oldPassword = document.getElementById('old-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Validate inputs
        if (!oldPassword || !newPassword || !confirmPassword) {
            alert("Please fill in all fields.");
            return;
        }

        // AJAX request to PHP
        fetch('change_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `old-password=${encodeURIComponent(oldPassword)}&new-password=${encodeURIComponent(newPassword)}&confirm-password=${encodeURIComponent(confirmPassword)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Password changed successfully!");
                document.getElementById('settings-change-password-form').reset();
                $('#settings-change-password-modal').modal('hide'); // Hide the modal on success
            } else {
                alert(data.message || "Error changing password.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while changing the password.");
        });
    }
</script>

</body>

</html>