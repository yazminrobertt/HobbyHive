<?php

session_start();
include('connect.php');

$username = $_SESSION['username'];


    $data = json_decode(file_get_contents('php://input'), true);
    $fromDate = $data['fromDate'];
    $toDate = $data['toDate'];

    // Prepare and execute the SQL query
    $sql = "SELECT oa.OFFER_NAME, SUM(b.BOOKING_TOTALPRICE) AS total_price 
            FROM offered_activity oa 
            JOIN booking b ON oa.OFFER_ID = b.OFFER_ID AND b.IS_CANCELED = 0 
            WHERE oa.COACH_USERNAME = ? 
            AND b.BOOKING_PLACEMENTDATE BETWEEN ? AND ? 
            GROUP BY oa.OFFER_NAME";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $username, $fromDate,$toDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the result array
    $salesData = [];
    while ($row = $result->fetch_assoc()) {
        $salesData[] = [
            'OFFER_NAME' => $row['OFFER_NAME'],
            'total_price' => (float)$row['total_price']
        ];
    }

    // Close connection
    $stmt->close();
    $conn->close();

    // Return JSON response
    echo json_encode($salesData);
    
?>
