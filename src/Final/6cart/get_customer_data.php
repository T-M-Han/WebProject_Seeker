<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seekerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMessages = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];

    $isValid = validateForm();

    if ($isValid) {
        $customerid = $_SESSION['customerid'];
        $sql = "UPDATE customer SET firstname=?, lastname=?, email=?, address=?, city=?, country=?, phone=? WHERE customerid=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $firstname, $lastname, $email, $address, $city, $country, $phone, $customerid);

        if ($stmt->execute()) {
            header("Location: 2cart.php");
            exit;
        } else {
            $errorMessages['database'] = "Error updating customer information: " . $conn->error;
        }
    } else {
        $errorMessages['validation'] = "Please check Email or Phone again!";
    }
}

if(isset($_SESSION['customerid'])) {
    $customerid = $_SESSION['customerid'];
    $sql = "SELECT firstname, lastname, email, address, city, country, phone FROM customer WHERE customerid = $customerid";
    $result = $conn->query($sql);

    if($result) {
        $row = $result->fetch_assoc();
        echo "<p style='font-size: 18px;font-weight: bold;text-align:center;margin: 20px;'>SHIPPING Information</p>";
        echo "<div >";
        echo "<table>";
        echo "<tr><td>First Name:</td><td>" . $row['firstname'] . "</td></tr>";
        echo "<tr><td>Last Name:</td><td>" . $row['lastname'] . "</td></tr>";
        echo "<tr><td>Email:</td><td>" . $row['email'] . "</td></tr>";
        echo "<tr><td>Address:</td><td>" . $row['address'] . "</td></tr>";
        echo "<tr><td>City:</td><td>" . $row['city'] . "</td></tr>";
        echo "<tr><td>Country:</td><td>" . $row['country'] . "</td></tr>";
        echo "<tr><td>Phone:</td><td>" . $row['phone'] . "</td></tr>";
        echo "</table>";
        echo "<button class='remove-item' onclick='openEditForm()' style='position: absolute; top: 310px; right: 0;'>EDIT</button><br>";
        echo "</div>";

        echo "<div id='editFormPopup' class='popup' style='" . (isset($errorMessages['validation']) ? "display: block;" : "display: none;") . "'>";
        echo "<h2>Edit SHIPPING Information</h2>";
        echo "<form id='editForm' method='post'>";
        if(isset($errorMessages['validation']))
            echo "<span class='error-message'>" . $errorMessages['validation'] . "</span><br>";
        echo "<input type='text' name='firstname' id='firstname' placeholder='First Name' value='" . $row['firstname'] . "'required><br>";
        if(isset($errorMessages['firstname']))
            echo "<span class='error-message'>" . $errorMessages['firstname'] . "</span><br>";
        echo "<input type='text' name='lastname' id='lastname' placeholder='Last Name' value='" . $row['lastname'] . "'required><br>";
        if(isset($errorMessages['lastname']))
            echo "<span class='error-message'>" . $errorMessages['lastname'] . "</span><br>";
        echo "<input type='email' name='email' id='email' placeholder='Email' value='" . $row['email'] . "'required><br>";
        if(isset($errorMessages['email']))
            echo "<span class='error-message'>" . $errorMessages['email'] . "</span><br>";
        echo "<input type='text' name='address' id='address' placeholder='Address' value='" . $row['address'] . "'required><br>";
        if(isset($errorMessages['address']))
            echo "<span class='error-message'>" . $errorMessages['address'] . "</span><br>";
        echo "<input type='text' name='city' id='city' placeholder='City' value='" . $row['city'] . "'required><br>";
        if(isset($errorMessages['city']))
            echo "<span class='error-message'>" . $errorMessages['city'] . "</span><br>";
        echo "<input type='text' name='country' id='country' placeholder='Country' value='" . $row['country'] . "'required><br>";
        if(isset($errorMessages['country']))
            echo "<span class='error-message'>" . $errorMessages['country'] . "</span><br>";
        echo "<input type='text' name='phone' id='phone' placeholder='Phone' value='" . $row['phone'] . "'required><br>";
        if(isset($errorMessages['phone']))
            echo "<span class='error-message'>" . $errorMessages['phone'] . "</span><br>";
        echo "<p style='font-size:12px;'>Remark: This information will be updated to your account information.</p>";
        echo "<button type='submit' class='buy-button' >UPDATE</button>";
        echo "</form>";
        echo "<button onclick='closeEditForm()' style='background-color:white; color:black;'>Cancel</button>";
        echo "</div>";

    } else {
        echo "Error retrieving customer data: " . $conn->error;
    }
} else {
    echo "Customer ID not set in session.";
}

$conn->close();

function validateForm() {
    $isValid = true;

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];

    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    if (!preg_match($emailRegex, $email) || !strpos($email, '@gmail.com')) {
        $isValid = false;
        $errorMessages['email'] = "Please enter a valid Gmail address.";
    }

    $phoneRegex = '/^\d{11}$/';
    if (!preg_match($phoneRegex, $phone)) {
        $isValid = false;
        $errorMessages['phone'] = "Please enter a valid phone number with 11 digits and no letters.";
    }

    if ($firstname === "") {
        $isValid = false;
        $errorMessages['firstname'] = "Please enter your first name.";
    }
    if ($lastname === "") {
        $isValid = false;
        $errorMessages['lastname'] = "Please enter your last name.";
    }
    if ($address === "") {
        $isValid = false;
        $errorMessages['address'] = "Please enter your address.";
    }
    if ($city === "") {
        $isValid = false;
        $errorMessages['city'] = "Please enter your city.";
    }
    if ($country === "") {
        $isValid = false;
        $errorMessages['country'] = "Please enter your country.";
    }

    return $isValid;
}
?>
</script>

<script>
function openEditForm() {
    document.getElementById('editFormPopup').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeEditForm() {
    document.getElementById('editFormPopup').style.display = 'none';
    document.body.classList.remove('modal-open');
}
</script>

<style>
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

.popup .error-message {
    color: red;
    font-size: 14px;
    margin-bottom: 5px;
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
