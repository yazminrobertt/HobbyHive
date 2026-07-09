<?php
    session_start();
    include('connect.php');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $offerId = isset($_POST['offer_id']) ? $_POST['offer_id'] : '';
        $packageType = $_POST['package_type'];
        $priceKey = "package_price_" . str_replace(" ", "_", $packageType); // Replace spaces with underscores
        $price = isset($_POST[$priceKey]) ? floatval($_POST[$priceKey]) : 0;
        $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
        $sessionId = isset($_POST['session_id']) ? $_POST['session_id'] : '';
        $selectedChildren = isset($_POST['selected_children']) ? $_POST['selected_children'] : [];
        
        // // Store selected data in session to pass to final booking process
        $_SESSION['selected_package'] = [
            'type' => $packageType,
            'price' => $price,
            'start_date' => $startDate,
            'session_id' => $sessionId,
            'children' => $selectedChildren
        ];
    } else {
        echo "Invalid request.";
        exit;
    }

    $pax = count($_SESSION['selected_package']['children']);
    // Fetch parent details
    $parentUsername = $_SESSION['username']; // Assuming parent's username is stored in session
    $parentQuery = "SELECT PARENT_NAME, PARENT_PHONE, PARENT_EMAIL FROM parent WHERE PARENT_USERNAME = ?";
    $stmt = $conn->prepare($parentQuery);
    $stmt->bind_param("s", $parentUsername);
    $stmt->execute();
    $parentResult = $stmt->get_result();
    $parent = $parentResult->fetch_assoc();

    // Fetch activity details based on the selected offer
    $activityQuery = "SELECT OFFER_NAME, OFFER_LOCATION FROM offered_activity WHERE OFFER_ID = ?";
    $stmt = $conn->prepare($activityQuery);
    $stmt->bind_param("s", $offerId);
    $stmt->execute();
    $activityResult = $stmt->get_result();
    $activity = $activityResult->fetch_assoc();

    // Fetch the pricing details
    $pricingType = $_SESSION['selected_package']['type'];
    $pricingQuery = "SELECT PRICE FROM offered_pricing WHERE OFFER_ID = ? AND PRICING_TYPE = ?";
    $stmt = $conn->prepare($pricingQuery);
    $stmt->bind_param("ss", $offerId, $pricingType);
    $stmt->execute();
    $pricingResult = $stmt->get_result();
    $pricing = $pricingResult->fetch_assoc();
    $pricing2 = $pricing['PRICE'];
    $totalPrice = $pricing2 * $pax;

    $sessionQuery = "SELECT OT_DAY, OT_STARTTIME, OT_ENDTIME FROM offered_time WHERE OFFER_ID = ? AND OT_ID = ?";
    $stmt = $conn->prepare($sessionQuery);
    $stmt->bind_param("ss", $offerId, $sessionId);
    $stmt->execute();
    $sessionResult = $stmt->get_result();
    $session = $sessionResult->fetch_assoc();
    $sessionDay = $session['OT_DAY'];
    $sessionStartTime = $session['OT_STARTTIME'];
    $sessionEndTime = $session['OT_ENDTIME'];
        
    $startDate = trim($startDate);
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $startDate, $matches)) {
        $startDate = $matches[3] . '-' . $matches[2] . '-' . $matches[1]; // Convert to yyyy-mm-dd
    } else {
        echo "Error: Invalid date format. Please use the format d/m/y.<br>";
        echo "Input date was: " . $startDate . "<br>";
        exit; 
    }

    $endDate = $startDate;
    if ($packageType === 'TRIAL CLASS') {
        $endDate = $startDate; 
    } else if ($packageType === 'ONE MONTH PACKAGE') {
        $endDate = date('Y-m-d', strtotime($startDate . ' +21 days')); 
    }else if ($packageType === 'ONE YEAR PACKAGE') {
        $endDate = date('Y-m-d', strtotime($startDate . ' +329 days')); 
    }

    // Fetch selected children details
    $selectedChildrenData = [];
    foreach ($_SESSION['selected_package']['children'] as $childId) {
        $childQuery = "SELECT child_name, child_age, child_gender FROM child WHERE child_id = ?";
        $stmt = $conn->prepare($childQuery);
        $stmt->bind_param("i", $childId);
        $stmt->execute();
        $childResult = $stmt->get_result();
        $child = $childResult->fetch_assoc();
        $child['child_id'] = $childId;
        $selectedChildrenData[] = $child;
    }

    $parentQuery = "SELECT PARENT_AMOUNT FROM parent_wallet WHERE PARENT_USERNAME = ?";
    $stmt = $conn->prepare($parentQuery);
    $stmt->bind_param("s", $parentUsername);
    $stmt->execute();
    $parentResult = $stmt->get_result();
    $parentWallet = $parentResult->fetch_assoc();

?>

<!DOCTYPE html>
<html>
    <head>
        <?php include('header.php'); ?>
        <title>Confirmation Page</title>
    </head>

    <style>
        body {
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }
        .title {
            max-width: 1000px;
            margin:auto;
            font-weight: bold;
            padding-bottom: 10px;
            color: #004085;
        }
        .container {
            border-radius: 2px;
            border: 2px solid #82b1ff;
            border-radius: 8px;
            max-width: 1000px;
            margin:auto;
            margin-bottom:30px;
            padding: 20px;
        }
        body .parentSect,.childSect,.bookSect{
            margin-top: 10px;
            max-width: 1130px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .parentHeader,  .childHeader,.bookHeader{
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .parentHeader, .childHeader,.bookHeader h4 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        .forBookDet {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .forBookDet td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .forBookDet td b {
            color: #333;
        }
        .forBookDet td:first-child {
            width: 30%;
            font-weight: bold;
            background-color:rgb(177, 196, 245);
            border: 0.5px solid  #004085;
        }
        .forBookDet td:last-child {
            width: 70%;
        }
        .forChild,.forParent {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .forChild th, .forChild td,.forParent th, .forParent td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .forChild th, .forParent th {
            background-color:rgb(158, 191, 245);
            font-weight: bold;
            color: #333;
        }
        .forChild td, .forParent td {
            color: #333;
        }
        .forChild tr:hover, .forParent tr:hover {
            background-color: #f1f1f1;
        }
        .forChild td:first-child, .forChild td:last-child, .forParent td:first-child, .forParent td:last-child {
            width: 33%; 
        }
        .forChild th, .forChild td, .forParent th, .forParent td {
            text-align: center;
        }
        .confirm {
            margin: 20px auto; /* Center the button */
            display: block;
            width: 800px;  /* Match width proportions with the review cards */
            background: linear-gradient(135deg, #448aff, #005ecb);
            color: white;  /* Same text color as review headers */
            border: none;
            padding: 10px 15px; /* Slightly larger padding for better balance */
            font-size: 14px; /* Maintain consistent font size */
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px; /* Match border radius of review cards */
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15); /* Match shadow with review cards */
            transition: all 0.3s ease-in-out; /* Smooth hover effect */
            text-align: center; /* Center-align text */
        }
        .confirm:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); /* Darker shade on hover */
            transform: translateY(-3px); /* Slight hover lift */
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); /* Enhanced shadow */
        }
        .main {
            flex: 1; /* Ensures the main content area takes available space */
        }
        .classfooter {
            color: #003366; 
            background-color: #A1C3F6;
            border-color: #A1C3F6; 
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            margin-top: auto; /* Pushes the footer to the bottom */

        }
        .classfooter .footer-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #A1C3F6;
            border-color: #A1C3F6; 
            border-top: 1px solid #ddd;
            color: #003366; 
        }
        .classfooter .footerLeft {
            display: flex;
            gap: 15px;
        }
        .classfooter .footerBtn {
            background: none;
            border: none;
            font-size: 12px;
            color: #003366; 
            cursor: pointer;
            transition: color 0.3s;
        }
        .classfooter .footerBtn:hover {
            color:rgb(6, 42, 79); 
        }
        .classfooter .footerRight {
            display: flex;
            gap: 10px;
        }
        .classfooter .socialIcon img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
    </style>

    <body>
        <div class="main">
        <div class="title">
            <h1><strong>Confirm Your Selection</strong></h1>
        </div>

        <div class="container">

            <div class="parentSect">
                <div class="parentHeader">
                    <h4><b>Parent Information</b></h4>
                </div>
            </div>
            <div class="parentInfo">
                <table class="forParent">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                    <tr>
                        <td> <?php echo htmlspecialchars($parent['PARENT_NAME']); ?></td>
                        <td> <?php echo htmlspecialchars($parent['PARENT_EMAIL']); ?></td>
                        <td><?php echo htmlspecialchars($parent['PARENT_PHONE']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="bookSect">
                <div class="bookHeader">
                    <h4><b>Booking Details</b></h4>
                </div>
            </div>
            <div class="bookedActivitySection">
                <table class="forBookDet">
                    <tr>
                        <td><b>Activity:</b></td>
                        <td><?php echo htmlspecialchars($activity['OFFER_NAME']); ?></td>
                    </tr>
                    <tr>
                        <td><b>Location:</b></td>
                        <td><?php echo htmlspecialchars($activity['OFFER_LOCATION']); ?></td>
                    </tr>
                    <tr>
                        <td><b>Chosen Package:</b></td>
                        <td><?php echo htmlspecialchars($packageType); ?></td>
                    </tr>
                    <tr>
                        <td><b>Session Day:</b></td>
                        <td><?php echo htmlspecialchars($sessionDay); ?></td>
                    </tr>
                    <tr>
                        <td><b>Time:</b></td>
                        <td><?php echo htmlspecialchars($sessionStartTime) . ' - ' . htmlspecialchars($sessionEndTime); ?></td>
                    </tr>
                    <tr>
                        <td><b>Start Date:</b></td>
                        <td><?php echo htmlspecialchars($startDate); ?></td>
                    </tr>
                    <tr>
                        <td><b>End Date:</b></td>
                        <td><?php echo htmlspecialchars($endDate); ?></td>
                    </tr>
                    <tr>
                        <td><b>Pax:</b></td>
                        <td><?php echo $pax; ?></td>
                    </tr>
                    <tr>
                        <td><b>Total Price:</b></td>
                        <td>RM <?php echo htmlspecialchars($totalPrice); ?></td>
                    </tr>
                </table>
            </div>

            <div class="childSect">
                <div class="childHeader">
                    <h4><b>Partcipants Information</b></h4>
                </div>
            </div>
            <div class="childrenInfo">
                <table class="forChild">
                    <thead>
                        <tr>
                            <th>Child Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selectedChildrenData as $child) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($child['child_name']); ?></td>
                                <td><?php echo htmlspecialchars($child['child_age']); ?></td>
                                <td><?php echo htmlspecialchars($child['child_gender']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <form action="finalConfirm.php" method="POST">
                <input type="hidden" name="bookingPax" value="<?php echo $pax; ?>">
                <input type="hidden" name="bookStartDate" value="<?php echo $startDate; ?>">
                <input type="hidden" name="bookEndDate" value="<?php echo $endDate; ?>">
                <input type="hidden" name="bookChosenPricing" value="<?php echo $packageType; ?>">
                <input type="hidden" name="bookTotalPrice" value="<?php echo $totalPrice; ?>">
                <input type="hidden" name="bookOfferId" value="<?php echo $offerId; ?>">
                <input type="hidden" name="bookSesh" value="<?php echo $sessionId; ?>">
                <?php foreach ($selectedChildrenData as $child) { ?>
                    <input type="hidden" name="selectedChildrenIds[]" value="<?php echo htmlspecialchars($child['child_id']); ?>">
                <?php } ?>

                <button class="confirm"type="submit">Confirm and Proceed</button>
            </form>
        </div>
        </div>


        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                    <button class="footerBtn" onclick="window.location.href='forCoach.php';">For Coaches</button>
                    <button class="footerBtn" onclick="window.location.href='faq.php';">Support FAQ</button>
                </div>
                <div class="footerRight">
                    <div class="socialIcon">
                        <img src="Facebook.jpg" alt="Facebook">
                    </div>
                    <div class="socialIcon">
                        <img src="Instagram.jpg" alt="Instagram">
                    </div>
                    <div class="socialIcon">
                        <img src="Twitter.jpg" alt="Twitter">
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
