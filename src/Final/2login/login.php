<?php
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "seekerdb";

        $input_email = $_POST['email'];
        $input_password = $_POST['password'];

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM customer WHERE BINARY email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $input_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();

                if ($input_password == $row['password']) {
                    session_start();
                    $_SESSION['customerid'] = $row['customerid'];
                    $_SESSION['loggedIn'] = true;

                    header("Location: ../3main/2jordan.php");
                    exit();
                } else {
                    echo "<script>alert('Invalid password. Please try again.');</script>";
                }

            } else {
                echo "<script>alert('Email not found. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('SQL statement preparation error.');</script>";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "<script>alert('Both email and password are required..');</script>";
    }
}
?>
