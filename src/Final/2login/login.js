document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');

    menuToggle.addEventListener('click', function () {
        this.classList.toggle('open');
        nav.classList.toggle('open');
    });
});

function validateForm() {
    var isValid = true;

    var errorElements = document.getElementsByClassName("error-message");
    for (var i = 0; i < errorElements.length; i++) {
        errorElements[i].innerHTML = "";
    }

    var email = document.forms["loginForm"]["email"].value.trim();
    var password = document.forms["loginForm"]["password"].value.trim();

    if (email === "") {
        document.getElementById("email-error").innerHTML = "Please enter email.";
        isValid = false;
    }
    if (password === "") {
        document.getElementById("password-error").innerHTML = "Please enter password.";
        isValid = false;
    }
    return isValid;
}

function togglePasswordVisibility() {
    var passwordInput = document.getElementById("password");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        document.querySelector(".show-password").textContent = "Hide Password";
    } else {
        passwordInput.type = "password";
        document.querySelector(".show-password").textContent = "Show Password";
    }
}

function togglePasswordVisibility2() {
    var confirmPasswordInput = document.getElementById("confirm_password");
    if (confirmPasswordInput.type === "password") {
        confirmPasswordInput.type = "text";
        document.querySelector(".show-password").textContent = "Hide Password";
    } else {
        confirmPasswordInput.type = "password";
        document.querySelector(".show-password").textContent = "Show Password";
    }
}

function validatePassword() {
    var passwordInput = document.getElementById("new_password");
    var password = passwordInput.value;
    var passwordError = document.getElementById("password-error");
    var passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!password.match(passwordRegex)) {
        passwordError.innerHTML = "Password must be at least 8 characters long and contain at least one letter, one number, and one special character.";
        return false;
    } else {
        passwordError.innerHTML = "";
        return true;
    }
}

function handleSearch() {
    var searchBox = document.getElementById('searchBox');
    if (searchBox.style.display === 'none' || searchBox.style.display === '') {
        searchBox.style.display = 'block';
    } else {
        searchBox.style.display = 'none';
    }
}

document.getElementById('searchIcon').addEventListener('click', function(event) {
    event.preventDefault();
    handleSearch();
});

document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var query = document.querySelector('#searchBox input[name="query"]').value.trim();
    if (query !== '') {
        window.location.href = '../3main/9search.php?query=' + encodeURIComponent(query);
    }
});
