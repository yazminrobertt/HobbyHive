<?php
    include('connect.php');

    if (isset($_GET['activityId'])) {
        $activityId = $_GET['activityId'];
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

        // Modify the SQL query based on whether the date range is provided
        if ($startDate && $endDate) {
            $sql = "SELECT * FROM review WHERE OFFER_ID = '$activityId' AND REVIEW_DATE BETWEEN '$startDate' AND '$endDate'";
        } else {
            $sql = "SELECT * FROM review WHERE OFFER_ID = '$activityId'"; // Get all reviews if no date range
        }

        $reviewsResult = $conn->query($sql);

        // Initialize rating counts (1-5)
        $ratingCounts = [0, 0, 0, 0, 0]; // Index 0-4 corresponds to ratings 1-5

        while ($review = $reviewsResult->fetch_assoc()) {
            $rating = $review['REVIEW_RATE'];
            $ratingCounts[$rating - 1]++;
        }

        // If no reviews found
        if (array_sum($ratingCounts) === 0) {
            echo json_encode(['error' => 'No ratings found']);
        } else {
            echo json_encode(['ratings' => $ratingCounts]);
        }
    }
?>
