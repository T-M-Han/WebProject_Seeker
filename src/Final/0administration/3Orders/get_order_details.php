<?php
if (isset($_GET['orderid'])) {
    $orderId = $_GET['orderid'];
    $mysqli = new mysqli("localhost", "root", "", "seekerdb");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
        exit();
    }

    $orderDetailsQuery = "SELECT od.`sneakerid`, od.`sizeid`, s.`name`, s.`nickname`, s.`image`, p.`price`, si.`sizename`
                          FROM `orderdetail` od
                          INNER JOIN `sneakers` s ON od.`sneakerid` = s.`sneakerid`
                          INNER JOIN `sizes` si ON od.`sizeid` = si.`sizeid`
                          INNER JOIN `prices` p ON od.`sneakerid` = p.`sneakerid` AND od.`sizeid` = p.`sizeid`
                          WHERE od.`orderid` = $orderId";

    $orderDetailsResult = $mysqli->query($orderDetailsQuery);

    if ($orderDetailsResult->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo '<tbody>';
        while ($detailRow = $orderDetailsResult->fetch_assoc()) {
            echo '<tr>';
            echo '<td style="text-align: center;"><img src="data:image/jpeg;base64,' . base64_encode($detailRow['image']) . '" alt="' . $detailRow['name'] . '" style="max-width: 150px;"></td>';
            echo '<td style="text-align: center;">' . $detailRow['name'] . '</td>';
            echo '<td style="text-align: center;">' . $detailRow['nickname'] . '</td>';
            echo '<td style="text-align: center;">EU ' . $detailRow['sizename'] . '</td>';
            echo '<td style="text-align: center;">$' . $detailRow['price'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo 'No order details found for this order.';
    }

    $mysqli->close();
} else {
    echo 'Invalid request.';
}
?>
