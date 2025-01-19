<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "seekerdb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $sql = "SELECT `adminid`, `username`, `role`, `password` FROM `admin` WHERE BINARY `username`=? AND BINARY `password`=? AND `role`=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['loggedIn'] = true;
        switch ($role) {
            case "Product Management Center":
                header("Location: ../1Products/1jmcenter.php");
                exit();
                break;
            case "Customer Management Center":
                header("Location: ../2Customers/2cscenter.php");
                exit();
                break;
            case "Orders Management Center":
                header("Location: ../3Orders/3fmcenter.php");
                exit();
                break;
            default:
                header("Location: default_page.php");
                exit();
        }
    } else {
        $error_message = "Invalid username or password";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../../images/SK.logo.png">
    <title>SEEKER-Management Centers</title>
    <link rel="stylesheet" href="adminmain.css">
</head>
<body>
    <div class="login-container">
        <center><h1>SEEKER</h1></center>
        <h2>SNEAKER COMPANY LIMITED</h2>
        <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="" disabled selected>Select Management Center</option>
                <option value="Product Management Center">Product Management Center</option>
                <option value="Customer Management Center">Customer Management Center</option>
                <option value="Orders Management Center">Orders Management Center</option>
            </select>
            <button type="submit">Login</button>
            <?php
            if (!empty($error_message)) {
                echo '<p class="error-message">' . $error_message . '</p>';
            }
            ?>
        </form>
    </div>
</body>
</html>