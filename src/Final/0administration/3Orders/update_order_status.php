<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['orderid']) && isset($_POST['newstatus'])) {
        $orderid = $_POST['orderid'];
        $newstatus = $_POST['newstatus'];

        $mysqli = new mysqli("localhost", "root", "", "seekerdb");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit();
        }

        $updateQuery = "UPDATE orders SET status = ? WHERE orderid = ?";
        $statement = $mysqli->prepare($updateQuery);
        $statement->bind_param("ss", $newstatus, $orderid);
        if ($statement->execute()) {
            header("Location: 3fmcenter.php");
            exit();
        } else {
            echo "Error updating status: " . $mysqli->error;
        }

        $mysqli->close();
    } else {
        echo "Missing parameters!";
    }
} else {
    echo "Invalid request method!";
}
?>
