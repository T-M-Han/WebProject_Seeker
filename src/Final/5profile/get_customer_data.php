<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seekerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];

    $customerid = $_SESSION['customerid'];
    $sql = "UPDATE customer SET firstname=?, lastname=?, email=?, address=?, city=?, country=?, phone=? WHERE customerid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $firstname, $lastname, $email, $address, $city, $country, $phone, $customerid);

    if ($stmt->execute()) {
        header("Location: 1profile.php");
        exit;
    } else {
        $errorMessages['database'] = "Error updating customer information: " . $conn->error;
    }
}

if(isset($_SESSION['customerid'])) {
    $customerid = $_SESSION['customerid'];
    $sql = "SELECT firstname, lastname, email, address, city, country, phone FROM customer WHERE customerid = $customerid";
    $result = $conn->query($sql);

    if($result) {
        $row = $result->fetch_assoc();
        echo "<div class='account-info' style='text-align:center;padding:0px;'>";
        echo "<label style='font-size:24px;font-weight:bold;margin:0px;'>ACCOUNT INFORMATION</label>
                <button class='remove-item' onclick='openEditForm()'>EDIT</button>";
        echo "<div class='table-container' style='text-align:left;'>";
        echo "<table class='info-table'>";
        echo "<tr><td>First Name:</td><td>" . htmlspecialchars($row['firstname']) . "</td></tr>";
        echo "<tr><td>Last Name:</td><td>" . htmlspecialchars($row['lastname']) . "</td></tr>";
        echo "<tr><td>Email:</td><td>" . htmlspecialchars($row['email']) . "</td></tr>";
        echo "<tr><td>Address:</td><td>" . htmlspecialchars($row['address']) . "</td></tr>";
        echo "<tr><td>City:</td><td>" . htmlspecialchars($row['city']) . "</td></tr>";
        echo "<tr><td>Country:</td><td>" . htmlspecialchars($row['country']) . "</td></tr>";
        echo "<tr><td>Phone:</td><td>" . htmlspecialchars($row['phone']) . "</td></tr>";
        echo "<tr><td>Password:</td><td>**********
            <button class='remove-item' onclick='redirectToForgotPassword()'>FORGOT PASSWORD</button>
            </td></tr>";
        echo "</table>";
        echo "</div>";
        echo "</div>";

        echo "<div id='editFormPopup' class='popup' style='" . (isset($errorMessages['validation']) ? "display: block;" : "display: none;") . "'>";
        echo "<h2>Edit ACCOUNT INFORMATION</h2>";
        echo "<form id='editForm' method='post'>";
        
        echo "<input type='text' name='firstname' id='firstname' placeholder='First Name' value='" . $row['firstname'] . "'required><br>";
        
        echo "<input type='text' name='lastname' id='lastname' placeholder='Last Name' value='" . $row['lastname'] . "'required><br>";
        
        echo "<input type='email' name='email' id='email' placeholder='Email' value='" . $row['email'] . "' pattern='[^\s@]+@[^\s@]+\.[^\s@]+' title='Please enter a valid email address' required><br>";
        
        echo "<input type='text' name='address' id='address' placeholder='Address' value='" . $row['address'] . "'required><br>";
        
        echo "<input type='text' name='city' id='city' placeholder='City' value='" . $row['city'] . "'required><br>";
        
        echo "<input type='text' name='country' id='country' placeholder='Country' value='" . $row['country'] . "' required><br>";
        
        echo "<input type='text' name='phone' id='phone' placeholder='Phone' value='" . $row['phone'] . "' pattern='\\d{9,11}' title='Please enter between 9 and 11 digits' required><br>";

        echo "<button type='submit' class='buy-button' >UPDATE</button>";
        echo "</form>";
        echo "<button onclick='closeEditForm()' style='background-color:white; color:black;'>Cancel</button>";
        echo "</div>";

    } else {
        echo "Error retrieving customer data: " . $conn->error;
    }
} else {
    header("Location: ../2login/1mainlogin.php");
    exit(); // Make sure to stop script execution after redirection
}


$conn->close();


?>

<script>
function openEditForm() {
    document.getElementById('editFormPopup').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeEditForm() {
    document.getElementById('editFormPopup').style.display = 'none';
    document.body.classList.remove('modal-open');
}

function redirectToForgotPassword() {
    window.location.href = '../2login/forgot_password.php';
}

function openEditForm() {
    document.getElementById('editFormPopup').style.display = 'block';
    document.body.classList.add('modal-open');
}

function redirectToForgotPassword() {
    window.location.href = '../2login/forgot_password.php';
}
</script>

<style>
.account-info {
    padding: 20px;
    max-width: 100%;
    overflow-x: auto;
}

.table-container {
    overflow-x: auto;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
}

.info-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.info-table td:first-child {
    font-weight: bold;
    min-width: 120px;
}

@media (max-width: 768px) {
    .info-table td:first-child {
        min-width: 100px;
    }
}

@media (max-width: 480px) {
    .info-table td:first-child {
        min-width: 80px;
    }
}

.popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    z-index: 9999;
    max-width: 400px;
}

.popup h2 {
    margin-top: 0;
    color: #333;
}

.popup input[type="text"],
.popup input[type="email"],
.popup input[type="phone"],
.popup button[type="submit"] {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

.popup button[type="submit"],
.popup button {
    display: block;
    width: 100%;
    padding: 12px;
    font-size: 16px;
    color: #fff;
    background-color: #000000;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.popup button[type="submit"]:hover,
.popup button:hover {
    background-color: #555555;
}

body.modal-open {
    overflow: hidden;
}

body.modal-open::after {
    content: "";
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9998;
}
</style>
