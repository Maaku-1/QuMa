document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.getElementById('edit-profile-btn');
    const cancelButton = document.getElementById('cancel-edit-profile-btn');
    const confirmButton = document.getElementById('confirm-edit-profile-btn');
    const firstNameInput = document.getElementById('first-name');
    const middleNameInput = document.getElementById('middle-name');
    const lastNameInput = document.getElementById('last-name');
    const campusInput = document.getElementById('campus');
    const departmentInput = document.getElementById('department');
    const sidebarName = document.querySelector('.sidebar-profile-name');
    const responsiveName = document.querySelector('.responsive-sidebar-profile-name');
    const profileCardName = document.querySelector('.profile-card-name');
    const tabButtons = document.querySelectorAll('.nav-link');

    let initialFirstName, initialMiddleName, initialLastName, initialCampus, initialDepartment;
    let lastConfirmedFirstName, lastConfirmedMiddleName, lastConfirmedLastName, lastConfirmedCampus, lastConfirmedDepartment;

    function storeInitialValues() {
        initialFirstName = firstNameInput.value;
        initialMiddleName = middleNameInput.value;
        initialLastName = lastNameInput.value;
        initialCampus = campusInput.value;
        initialDepartment = departmentInput.value;
    }

    function storeConfirmedValues() {
        lastConfirmedFirstName = firstNameInput.value;
        lastConfirmedMiddleName = middleNameInput.value;
        lastConfirmedLastName = lastNameInput.value;
        lastConfirmedCampus = campusInput.value;
        lastConfirmedDepartment = campusInput.value;
    }

    function revertToInitialValues() {
        firstNameInput.value = initialFirstName;
        middleNameInput.value = initialMiddleName;
        lastNameInput.value = initialLastName;
        campusInput.value = initialCampus;
        departmentInput.value = initialDepartment;
    }

    function revertToConfirmedValues() {
        firstNameInput.value = lastConfirmedFirstName;
        middleNameInput.value = lastConfirmedMiddleName;
        lastNameInput.value = lastConfirmedLastName;
        campusInput.value = lastConfirmedCampus;
        departmentInput.value = lastConfirmedDepartment;
    }

    editButton.addEventListener('click', function() {
        editButton.style.display = 'none';
        cancelButton.style.display = 'inline-block';
        confirmButton.style.display = 'inline-block';

        // Store initial values
        storeInitialValues();

        // Make input fields editable
        firstNameInput.readOnly = false;
        middleNameInput.readOnly = false;
        lastNameInput.readOnly = false;
        campusInput.readOnly = false;
        departmentInput.readOnly = false;
    });

    cancelButton.addEventListener('click', function() {
        editButton.style.display = 'inline-block';
        cancelButton.style.display = 'none';
        confirmButton.style.display = 'none';

        // Revert to initial values
        revertToInitialValues();

        // Make input fields read-only again
        firstNameInput.readOnly = true;
        middleNameInput.readOnly = true;
        lastNameInput.readOnly = true;
        campusInput.readOnly = true;
        departmentInput.readOnly = true;
    });

    confirmButton.addEventListener('click', function() {
        editButton.style.display = 'inline-block';
        cancelButton.style.display = 'none';
        confirmButton.style.display = 'none';

        // Make input fields read-only again
        firstNameInput.readOnly = true;
        middleNameInput.readOnly = true;
        lastNameInput.readOnly = true;
        campusInput.readOnly = true;
        departmentInput.readOnly = true;

        // Format the name
        const firstName = firstNameInput.value;
        const middleName = middleNameInput.value ? middleNameInput.value.charAt(0) + '.' : '';
        const lastName = lastNameInput.value;
        const formattedName = `${firstName} ${middleName} ${lastName}`;
        const firstWordOfFirstName = firstName.split(' ')[0];
        const sidebarFormattedName = `${firstWordOfFirstName} ${lastName}`;

        // Update sidebar profile details with only first and last name
        sidebarName.textContent = sidebarFormattedName;
        responsiveName.textContent = sidebarFormattedName;

        // Update profile card details with full name
        profileCardName.textContent = formattedName;

        // Store confirmed values
        storeConfirmedValues();
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (editButton.style.display === 'none') {
                // Revert to last confirmed values if there are unsaved changes
                revertToConfirmedValues();

                // Make input fields read-only again
                firstNameInput.readOnly = true;
                middleNameInput.readOnly = true;
                lastNameInput.readOnly = true;
                campusInput.readOnly = true;
                departmentInput.readOnly = true;

                // Reset buttons
                editButton.style.display = 'inline-block';
                cancelButton.style.display = 'none';
                confirmButton.style.display = 'none';
            }
        });
    });

    // Store initial confirmed values
    storeConfirmedValues();
});