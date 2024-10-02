document.addEventListener("DOMContentLoaded", function () {
    registerEventListeners();
    activateMenu();
});

function registerEventListeners() {
    var thumbnailImages = document.getElementsByClassName("img-thumbnail");

    // Loop through each thumbnail image and add an event listener
    for (var i = 0; i < thumbnailImages.length; i++) {
        thumbnailImages[i].addEventListener("click", function () {
            openPopup(this.src);
        });
    }
}

function openPopup(imageSrc) {
    // Remove any existing popup
    closePopup();

    // Create a new popup image element
    var popupImgElement = document.createElement("img");
    popupImgElement.setAttribute("class", "popup-content img-popup");
    popupImgElement.setAttribute("id", "popupImg");
    popupImgElement.src = imageSrc;

    // Append the new popup image element to the popup container
    document.getElementById("popupContainer").appendChild(popupImgElement);

    // Show the popup
    document.getElementById("popupContainer").style.display = "flex";
}

function closePopup() {
    // Remove any existing popup image element
    var existingPopup = document.getElementById("popupImg");
    if (existingPopup) {
        existingPopup.remove();
    }

    // Hide the popup container
    document.getElementById("popupContainer").style.display = "none";
}

function activateMenu() {
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        if (link.href === location.href) {
            link.classList.add('active');
        }
    })
}

function editProduct() {
    document.getElementById('display-content').style.display = 'none'; // Hide display content
    document.getElementById('display-content2').style.display = 'none'; // Hide display content
    document.getElementById('display-content3').style.display = 'none'; // Show display content
    document.getElementById('edit-content').style.display = 'block'; // Show edit form
}

function cancelEdit2() {
    document.getElementById('display-content').style.display = 'block'; // Show display content
    document.getElementById('display-content2').style.display = 'block'; // Show display content
    document.getElementById('display-content3').style.display = 'block'; // Show display content
    document.getElementById('edit-content').style.display = 'none'; // Hide edit form
}

// Function to edit a row
function editRow(memberId) {
    // Identifying the cells
    const fnameCell = document.getElementById('fname_' + memberId);
    const lnameCell = document.getElementById('lname_' + memberId);
    const emailCell = document.getElementById('email_' + memberId);
    const isAdminCell = document.getElementById('is_admin_' + memberId); // Admin status cell
    const actionsCell = document.getElementById('actions_' + memberId);

    // Saving current values
    const currentFname = fnameCell.innerHTML;
    const currentLname = lnameCell.innerHTML;
    const currentEmail = emailCell.innerHTML;
    let currentIsAdmin = false; // Default to false
    if (isAdminCell) {
        currentIsAdmin = isAdminCell.innerHTML.includes('Yes');
    }

    // Replacing contents with form inputs
    fnameCell.innerHTML = `<input type='text' name='fname' form='editForm_${memberId}' value='${currentFname}' />`;
    lnameCell.innerHTML = `<input type='text' name='lname' form='editForm_${memberId}' value='${currentLname}' />`;
    emailCell.innerHTML = `<input type='email' name='email' form='editForm_${memberId}' value='${currentEmail}' />`;

    if (isAdminCell) {
        isAdminCell.innerHTML = `<div class="form-check">
                                <input type='checkbox' class='form-check-input' name='isAdmin' form='editForm_${memberId}' ${currentIsAdmin ? 'checked' : ''} />
                                <label class="form-check-label">Is Admin?</label>
                             </div>`;
    }

    // Modifying actions to Apply (with Bootstrap button classes) and Cancel
    actionsCell.innerHTML = `<form id='editForm_${memberId}' action='updateUser.php' method='post' class='d-inline'>
                            <input type='hidden' name='member_id' value='${memberId}' />
                            <button type='submit' class='btn btn-success'>Apply</button>
                        </form>
                        <button type='button' class='btn btn-secondary' onclick='cancelEdit(${memberId}, "${currentFname.replace(/"/g, '&quot;')}", "${currentLname.replace(/"/g, '&quot;')}", "${currentEmail.replace(/"/g, '&quot;')}", ${currentIsAdmin})'>Cancel</button>`;
}


// Function to cancel the edit and revert back to text
function cancelEdit(memberId, fname, lname, email, isAdmin) {
    // Restoring original contents
    document.getElementById('fname_' + memberId).innerHTML = fname;
    document.getElementById('lname_' + memberId).innerHTML = lname;
    document.getElementById('email_' + memberId).innerHTML = email;
    if (document.getElementById('isAdmin_' + memberId)) {
        document.getElementById('isAdmin_' + memberId).innerHTML = isAdmin ? 'Yes' : 'No';
    }

    // Resetting the actions column
    document.getElementById('actions_' + memberId).innerHTML = `<button type="button" class="btn btn-primary" onclick="editRow(${memberId})">Edit</button>
                                                                 <form action="deleteuser.php" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline-block;">
                                                                     <input type="hidden" name="member_id" value="${memberId}">
                                                                     <button type="submit" class="btn btn-danger">Delete</button>
                                                                 </form>`;
}





//Cart and checkout JS
// Disable form submissions if there are invalid fields
(function checkoutFormValidation() {
    'use strict'

    window.addEventListener('load', function () {
        // Fetch all the forms that needs validatoin
        var forms = document.getElementsByClassName('needs-validation')

        // Loop over them and prevent submission
        Array.prototype.filter.call(forms, function (form) {
            form.addEventListener('submit', function (event) {
                if (form.checkValidity() === false) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    }, false)
})()


