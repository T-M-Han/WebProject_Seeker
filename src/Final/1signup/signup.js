document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');

    menuToggle.addEventListener('click', function() {
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

    var firstname = document.forms["signupForm"]["firstname"].value.trim();
    var lastname = document.forms["signupForm"]["lastname"].value.trim();
    var email = document.forms["signupForm"]["email"].value.trim();
    var address = document.forms["signupForm"]["address"].value.trim();
    var city = document.forms["signupForm"]["city"].value.trim();
    var country = document.forms["signupForm"]["country"].value.trim();
    var phone = document.forms["signupForm"]["phone"].value.trim();
    var password = document.forms["signupForm"]["password"].value;
    var confirmPassword = document.forms["signupForm"]["confirm_password"].value;
    var question = document.forms["signupForm"]["question"].value;
    var answer = document.forms["signupForm"]["answer"].value.trim();

    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email) || !email.includes('@gmail.com')) {
        document.getElementById("email-error").innerHTML = "Please enter a valid Gmail address.";
        isValid = false;
    }

    var phoneRegex = /^\d{11}$/;
    if (!phone.match(phoneRegex)) {
        document.getElementById("phone-error").innerHTML = "Please enter a valid phone number with 11 digits and no letters.";
        isValid = false;
    }

    var passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!password.match(passwordRegex)) {
        document.getElementById("password-error").innerHTML = "Password must be at least 8 characters long and contain at least one letter, one number, and one special character.";
        isValid = false;
    }

    if (password !== confirmPassword) {
        document.getElementById("confirm-password-error").innerHTML = "Passwords do not match.";
        isValid = false;
    }

    if (firstname === "") {
        document.getElementById("firstname-error").innerHTML = "Please enter your first name.";
        isValid = false;
    }
    if (lastname === "") {
        document.getElementById("lastname-error").innerHTML = "Please enter your last name.";
        isValid = false;
    }
    if (address === "") {
        document.getElementById("address-error").innerHTML = "Please enter your address.";
        isValid = false;
    }
    if (city === "") {
        document.getElementById("city-error").innerHTML = "Please enter your city.";
        isValid = false;
    }
    if (country === "") {
        document.getElementById("country-error").innerHTML = "Please enter your country.";
        isValid = false;
    }
    if (question === "") {
        document.getElementById("question-error").innerHTML = "Please select a security question.";
        isValid = false;
    }
    if (answer === "") {
        document.getElementById("answer-error").innerHTML = "Please provide an answer to the security question.";
        isValid = false;
    }

    return isValid;
}

function togglePasswordVisibility() {
    var confirmPasswordField = document.getElementById("confirm_password");

    if (confirmPasswordField.type === "password") {
        confirmPasswordField.type = "text";
        document.querySelector(".show-password").textContent = "Hide Password";
    } else {
        confirmPasswordField.type = "password";
        document.querySelector(".show-password").textContent = "Show Password";
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
