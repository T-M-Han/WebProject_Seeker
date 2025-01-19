<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["firstname"])) {
        $firstnameErr = "First Name is required";
    } else {
        $firstname = test_input($_POST["firstname"]);
        if (!preg_match("/^[a-zA-Z ]*$/",$firstname)) {
            $firstnameErr = "Only letters and white space allowed";
        }
    }

    if (empty($_POST["lastname"])) {
        $lastnameErr = "Last Name is required";
    } else {
        $lastname = test_input($_POST["lastname"]);
        if (!preg_match("/^[a-zA-Z ]*$/",$lastname)) {
            $lastnameErr = "Only letters and white space allowed";
        }
    }

    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        } else if (!strpos($email, '@gmail.com')) {
            $emailErr = "Email must be a Gmail address";
        }
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
        if (!preg_match("/^(?=.*\d)(?=.*[A-Za-z])(?=.*[@#$%^&!])[0-9A-Za-z@#$%^&!]{8,}$/",$password)) {
            $passwordErr = "Password must be at least 8 characters long and contain at least one number, one letter, and one special character (@#$%^&!)";
        }
    }

    if (empty($_POST["confirm_password"])) {
        $confirm_passwordErr = "Please confirm password";
    } else {
        $confirm_password = test_input($_POST["confirm_password"]);
        if ($_POST["password"] != $_POST["confirm_password"]) {
            $confirm_passwordErr = "Passwords do not match";
        }
    }

    if (empty($_POST["question"]) || empty($_POST["answer"])) {
        $securityErr = "Please select a security question and provide an answer";
    } else {
        $question = test_input($_POST["question"]);
        $answer = test_input($_POST["answer"]);
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "seekerdb";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $existing_email_query = "SELECT * FROM customer WHERE email='$email'";
    $existing_phone_query = "SELECT * FROM customer WHERE phone='$phone'";
    $existing_password_query = "SELECT * FROM customer WHERE password='$password'";

    $email_result = $conn->query($existing_email_query);
    $phone_result = $conn->query($existing_phone_query);
    $password_result = $conn->query($existing_password_query);

    if ($email_result->num_rows > 0) {
        $emailErr = "Email already exists";
    }

    if ($phone_result->num_rows > 0) {
        $phoneErr = "Phone number already exists";
    }

    if ($password_result->num_rows > 0) {
        $passwordErr = "Password already exists";
    }

    if (!empty($firstnameErr) || !empty($lastnameErr) || !empty($emailErr) || !empty($addressErr) || !empty($cityErr) || !empty($countryErr) || !empty($phoneErr) || !empty($passwordErr) || !empty($confirm_passwordErr) || !empty($securityErr) || $email_result->num_rows > 0 || $phone_result->num_rows > 0 || $password_result->num_rows > 0) {
        $conn->close();
    } else {
        $sql = "INSERT INTO customer (firstname, lastname, email, address, city, country, phone, password, question, answer)
        VALUES ('$firstname', '$lastname', '$email', '$address', '$city', '$country', '$phone', '$password', '$question', '$answer')";

        if ($conn->query($sql) === TRUE) {
            $customerid = $conn->insert_id;

            $conn->close();

            header("Location: ../5profile/get_customer_data.php?customerid=$customerid");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
