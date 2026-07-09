<?php

session_start();
include('connect.php');

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture the date range from the form
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];

    // Query to get the booking count for each offer in the specified date range
    $sql = "SELECT oa.OFFER_NAME, COUNT(b.BOOKING_ID) AS booking_count
            FROM offered_activity oa
            LEFT JOIN booking b ON oa.OFFER_ID = b.OFFER_ID AND IS_CANCELED=0
            WHERE oa.COACH_USERNAME = ? AND b.BOOKING_PLACEMENTDATE BETWEEN ? AND ?
            GROUP BY oa.OFFER_NAME";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $username, $fromDate, $toDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $offers = [];
        while ($row = $result->fetch_assoc()) {
            $offers[] = $row;
        }

        // Convert the result to a JSON format
        echo json_encode(['offers' => $offers]);
    }
}
?>
