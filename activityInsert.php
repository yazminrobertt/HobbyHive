<?php
session_start();
include('connect.php');

$coachUsername = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $activityType = $_POST['activityType'];
    $activityCategory = $_POST['activityCategory'];
    $activityName = $_POST['acName'];
    $activityDesc = $_POST['acDesc'];
    $activityState = $_POST['acState'];
    $activityLocation = $_POST['acLocation'];
    $minAge = $_POST['acMinAge'];
    $maxAge = $_POST['acMaxAge'];

    $trialPrice = $_POST['acTryPrice']; 
    $monthPrice = $_POST['acMonthPrice'];
    $yearPrice = $_POST['acYearPrice']; 

    $images = ['acPic1', 'acPic2', 'acPic3'];
    $imageData = [];

    foreach ($images as $image) {
        if (isset($_FILES[$image]) && $_FILES[$image]['error'] === UPLOAD_ERR_OK) {
            // Read the image content as binary
            $imageContent = file_get_contents($_FILES[$image]['tmp_name']);
            
            // Encode the binary data to Base64
            $encodedImage = base64_encode($imageContent);
            
            $imageData[$image] = $encodedImage;  // Store Base64 encoded image
        } else {
            $imageData[$image] = null;  // No image uploaded
        }
    }

    if (empty($activityType) || empty($activityCategory) || empty($activityName) || empty($activityDesc) || 
        empty($activityState) || empty($activityLocation) || empty($trialPrice)|| empty( $yearPrice) || empty($monthPrice)) {
        echo "<script>alert('Please fill in all required fields, including prices.');</script>";
    } else {
        $typeQuery = "SELECT TYPE_ID FROM activity_type WHERE TYPE_NAME = ?";
        $typeStmt = $conn->prepare($typeQuery);
        $typeStmt->bind_param("s", $activityType);
        $typeStmt->execute();
        $typeResult = $typeStmt->get_result();

        if ($typeResult->num_rows > 0) {
            $typeRow = $typeResult->fetch_assoc();
            $typeId = $typeRow['TYPE_ID'];

            $categoryQuery = "SELECT CATEGORY_ID FROM activity_category WHERE CATEGORY_NAME = ? AND TYPE_ID = ?";
            $categoryStmt = $conn->prepare($categoryQuery);
            $categoryStmt->bind_param("si", $activityCategory, $typeId);
            $categoryStmt->execute();
            $categoryResult = $categoryStmt->get_result();

            if ($categoryResult->num_rows > 0) {
                $categoryRow = $categoryResult->fetch_assoc();
                $categoryId = $categoryRow['CATEGORY_ID'];

                $insertQuery = "INSERT INTO offered_activity (CATEGORY_ID, OFFER_NAME, OFFER_DESC, OFFER_MINAGE, OFFER_MAXAGE, OFFER_STATE, OFFER_LOCATION, COACH_USERNAME)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("isssisss", $categoryId, $activityName, $activityDesc, $minAge, $maxAge, $activityState, $activityLocation, $coachUsername);

                if ($insertStmt->execute()) {
                    $offerId = $insertStmt->insert_id;

                    $pricingQuery = "INSERT INTO offered_pricing (OFFER_ID, PRICING_TYPE, PRICE) VALUES (?, ?, ?)";
                    $pricingStmt = $conn->prepare($pricingQuery);

                    $trialType = 'TRIAL CLASS';
                    $pricingStmt->bind_param("iss", $offerId, $trialType, $trialPrice);
                    if (!$pricingStmt->execute()) {
                        echo "<script>alert('Failed to insert trial price.');</script>";
                        exit;
                    }

                    $monthType = 'ONE MONTH PACKAGE';
                    $pricingStmt->bind_param("iss", $offerId, $monthType, $monthPrice);
                    if (!$pricingStmt->execute()) {
                        echo "<script>alert('Failed to insert one-month price.');</script>";
                        exit;
                    }

                    $yearType = 'ONE YEAR PACKAGE';
                    $pricingStmt->bind_param("iss", $offerId, $yearType, $yearPrice);
                    if (!$pricingStmt->execute()) {
                        echo "<script>alert('Failed to insert one-year price.');</script>";
                        exit;
                    }

                    foreach ($imageData as $image => $encodedImage) {
                        if ($encodedImage !== null) {
                            $picQuery = "INSERT INTO offered_pic (OFFER_ID, OP_PIC) VALUES (?, ?)";
                            $picStmt = $conn->prepare($picQuery);
                            $picStmt->bind_param("is", $offerId, $encodedImage);  // 's' for string (Base64 encoded)
                            
                            if (!$picStmt->execute()) {
                                echo "<script>alert('Failed to insert image data.');</script>";
                                exit;
                            }
                        }
                    }

                    if (isset($_POST['sessions']['day']) && is_array($_POST['sessions']['day'])) {
                        $timeQuery = "INSERT INTO offered_time (OFFER_ID, OT_DAY, OT_STARTTIME, OT_ENDTIME, OT_PAX, OT_SLOTSLEFT, OT_TYPE, IS_REMOVED) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $timeStmt = $conn->prepare($timeQuery);

                        for ($i = 0; $i < count($_POST['sessions']['day']); $i++) {
                            $day = trim($_POST['sessions']['day'][$i]);
                            $startTime = trim($_POST['sessions']['start_time'][$i]);
                            $endTime = trim($_POST['sessions']['end_time'][$i]);
                            $pax = intval(trim($_POST['sessions']['pax'][$i]));
                            $sessionType = trim($_POST['sessions']['session_type'][$i]);
                            $slotsLeft = $pax;
                            $isRemoved = 0;

                            if (!empty($day) && !empty($startTime) && !empty($endTime) && $pax > 0  && !empty($sessionType)){
                                $timeStmt->bind_param("isssiisi", $offerId, $day, $startTime, $endTime, $pax, $slotsLeft, $sessionType, $isRemoved);

                                if (!$timeStmt->execute()) {
                                    echo "<script>alert('Failed to insert session data.');</script>";
                                    exit;
                                }
                            } else {
                                continue;
                            }
                        }
                    } else {
                        echo "<script>alert('No session data provided.');</script>";
                        exit;
                    }
                } else {
                    echo "<script>alert('Failed to insert activity data.');</script>";
                }
            } else {
                echo "<script>alert('Invalid activity category.');</script>";
            }
        } else {
            echo "<script>alert('Invalid activity type.');</script>";
        }
    }
    // header("Location: coachProfile.php");
    echo "<script>alert('Activity successfully added!');window.location.href = 'coachProfile.php';</script>";
}
$conn->close();
?>
