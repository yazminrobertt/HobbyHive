<?php
    session_start();
    include('connect.php');

    // $activityId = isset($_GET['activityId']) ? $_GET['activityId'] : '';
    $activityId = isset($_POST['activityId']) ? $_POST['activityId'] : (isset($_GET['activityId']) ? $_GET['activityId'] : '');
    if (!$activityId) {
        die('Activity ID is required.');
    }
    
    // Fetch current data from the database
    $query = "SELECT * FROM offered_activity WHERE OFFER_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $activityId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        die('Activity not found.');
    }
    $data = $result->fetch_assoc();

    $pricingQuery = "SELECT * FROM offered_pricing WHERE OFFER_ID = ?";
    $pricingStmt = $conn->prepare($pricingQuery);
    $pricingStmt->bind_param("i", $activityId);
    $pricingStmt->execute();
    $pricingResult = $pricingStmt->get_result();

    $trialPrice = '';
    $monthPrice = '';

    // Loop through pricing data to set the values
    while ($pricingData = $pricingResult->fetch_assoc()) {
        if ($pricingData['PRICING_TYPE'] == 'TRIAL CLASS') {
            $trialPrice = $pricingData['PRICE'];
        } elseif ($pricingData['PRICING_TYPE'] == 'ONE MONTH PACKAGE') {
            $monthPrice = $pricingData['PRICE'];
        }elseif($pricingData['PRICING_TYPE'] == 'ONE YEAR PACKAGE'){
            $yearPrice=$pricingData['PRICE'];
        }
    }

    $queryPics = "SELECT * FROM offered_pic WHERE OFFER_ID = ?";
    $stmtPics = $conn->prepare($queryPics);
    $stmtPics->bind_param("i", $activityId);
    $stmtPics->execute();
    $resultPics = $stmtPics->get_result();

    // Store the fetched images along with OP_ID
    $images = [];
    while ($row = $resultPics->fetch_assoc()) {
        $images[] = [
            'OP_ID' => $row['OP_ID'], // Add OP_ID to the array
            'OP_PIC' => $row['OP_PIC'] // Assuming this is already Base64
        ];
    }

    // $query = "SELECT ot.OT_ID, ot.OT_DAY, ot.OT_STARTTIME, ot.OT_ENDTIME, ot.OT_PAX, ot.OT_TYPE
    // FROM offered_time ot
    // LEFT JOIN booking b ON ot.OT_ID = b.OT_ID
    // WHERE (b.BOOKING_ID IS NULL OR b.IS_DONE = 1)
    // AND ot.OFFER_ID = ? AND ot.IS_REMOVED = 0";

    $query="SELECT ot.OT_ID, ot.OT_DAY, ot.OT_STARTTIME, ot.OT_ENDTIME, ot.OT_PAX, ot.OT_TYPE 
    FROM offered_time ot WHERE ot.OFFER_ID = ? 
    AND ot.IS_REMOVED = 0 
    AND NOT EXISTS 
    ( SELECT 1 FROM booking b 
    WHERE b.OT_ID = ot.OT_ID AND b.IS_DONE = 0 );";

    // Prepare and execute the query to prevent SQL injection
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $activityId); // "i" means the parameter is an integer
    $stmt->execute();

    // Fetch the results
    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Activity details
        $acName = $_POST['acName'];
        $acDesc = $_POST['acDesc'];
        $acLocation = $_POST['acLocation'];
        $acMinAge = $_POST['acMinAge'];
        $acMaxAge = $_POST['acMaxAge'];
    
        // Prepare the SQL query to update the activity details
        $queryUpdate = "UPDATE offered_activity SET 
                            OFFER_NAME = ?, 
                            OFFER_DESC = ?, 
                            OFFER_LOCATION = ?, 
                            OFFER_MINAGE = ?, 
                            OFFER_MAXAGE = ? 
                        WHERE OFFER_ID = ?";
        $stmtUpdate = $conn->prepare($queryUpdate);
    
        // Bind the parameters
        $stmtUpdate->bind_param("sssiis", $acName, $acDesc, $acLocation, $acMinAge, $acMaxAge, $activityId);
    
        // Execute the query to update activity details
        if ($stmtUpdate->execute()) {
            // Loop through each image input (acPic1, acPic2, acPic3)
            for ($i = 1; $i <= 3; $i++) {
                // Check if the file is uploaded
                if (isset($_FILES["acPic$i"]) && $_FILES["acPic$i"]['error'] === UPLOAD_ERR_OK) {
                    // Read the uploaded image
                    $imageContent = file_get_contents($_FILES["acPic$i"]['tmp_name']);
                    $imageData = base64_encode($imageContent);
    
                    // Get the OP_ID from the hidden input
                    $opId = $_POST["opId$i"];
    
                    // Prepare SQL to update the image in the offered_pic table
                    $queryImageUpdate = "UPDATE offered_pic SET OP_PIC = ? WHERE OFFER_ID = ? AND OP_ID = ?";
                    $stmtImageUpdate = $conn->prepare($queryImageUpdate);
    
                    // Bind the parameters (image data, OFFER_ID, and OP_ID)
                    $stmtImageUpdate->bind_param("sii", $imageData, $activityId, $opId);
    
                    // Execute the image update query
                    if ($stmtImageUpdate->execute()) {
                        echo "Image acPic$i updated successfully.<br>";
                    } else {
                        echo "Error updating the image for acPic$i: " . $stmtImageUpdate->error . "<br>";
                    }
                    $stmtImageUpdate->close();
                }
            }
    
            // Pricing details
            $acTryPrice = $_POST['acTryPrice'];
            $acMonthPrice = $_POST['acMonthPrice'];
            $acYearPrice = $_POST['acYearPrice'];
    
            // Validate the input to ensure they are numeric values
            if (is_numeric($acTryPrice) && is_numeric($acMonthPrice)&& is_numeric($acYearPrice)) {
                // Update the 'TRIAL CLASS' pricing
                $updateTrialQuery = "UPDATE offered_pricing SET PRICE = ? WHERE OFFER_ID = ? AND PRICING_TYPE = 'TRIAL CLASS'";
                $stmtTrial = $conn->prepare($updateTrialQuery);
                $stmtTrial->bind_param("di", $acTryPrice, $activityId); // "di" means double for price and integer for offer ID
                $stmtTrial->execute();
                
                // Update the 'ONE MONTH PACKAGE' pricing
                $updateMonthQuery = "UPDATE offered_pricing SET PRICE = ? WHERE OFFER_ID = ? AND PRICING_TYPE = 'ONE MONTH PACKAGE'";
                $stmtMonth = $conn->prepare($updateMonthQuery);
                $stmtMonth->bind_param("di", $acMonthPrice, $activityId); // "di" means double for price and integer for offer ID
                $stmtMonth->execute();

                // Update the 'ONE MONTH PACKAGE' pricing
                $updateMonthQuery = "UPDATE offered_pricing SET PRICE = ? WHERE OFFER_ID = ? AND PRICING_TYPE = 'ONE YEAR PACKAGE'";
                $stmtMonth = $conn->prepare($updateMonthQuery);
                $stmtMonth->bind_param("di", $acYearPrice, $activityId); // "di" means double for price and integer for offer ID
                $stmtMonth->execute();
    
                // Check if both updates were successful
                if ($stmtTrial->affected_rows > 0 && $stmtMonth->affected_rows > 0) {
                    echo "<script>alert('Pricing updated successfully'); window.location.href = 'coachProfile.php';</script>";
                } else {
                    // echo "<script>alert('Error updating pricing');</script>";
                }
    
                // Close the statements
                $stmtTrial->close();
                $stmtMonth->close();
            } else {
                // echo "<script>alert('Please enter valid numeric prices');</script>";
                echo "<script>alert('Please enter valid numeric prices');window.location.href = 'activityEdit.php?activityId=$activityId';</script>";
            }

            // echo '<pre>';
            // var_dump($_POST);
            // echo '</pre>';

            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update':
                        if (isset($_POST['sessions']['OT_ID'])) {
                            // Get the data for existing sessions
                            $otIds = $_POST['sessions']['OT_ID'];
                            $days = $_POST['sessions']['day'];
                            $startTimes = $_POST['sessions']['start_time'];
                            $endTimes = $_POST['sessions']['end_time'];
                            $pax = $_POST['sessions']['pax'];
                            $types = $_POST['sessions']['session_type'];
                    
                            // Update existing sessions
                            foreach ($otIds as $key => $otId) {
                                if (!empty($otId)) {  // Ensure OT_ID is not empty (this ensures only existing sessions are updated)
                                    $query = "UPDATE offered_time SET OT_DAY = ?, OT_STARTTIME = ?, OT_ENDTIME = ?, OT_PAX = ?, OT_SLOTSLEFT = ?, OT_TYPE = ? WHERE OT_ID = ?";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("ssssisi", $days[$key], $startTimes[$key], $endTimes[$key], $pax[$key], $pax[$key], $types[$key], $otId);
                                    $stmt->execute();
                                    $stmt->close();
                                }
                            }
                        }
                        break;
            
                    case 'add':
                        if (isset($_POST['sessions']['day'])) {
                            // Add new sessions (those that do not have OT_ID)
                            $days = $_POST['sessions']['day'];
                            $startTimes = $_POST['sessions']['start_time'];
                            $endTimes = $_POST['sessions']['end_time'];
                            $pax = $_POST['sessions']['pax'];
                            $types = $_POST['sessions']['session_type'];
            
                            // Insert new sessions into the database
                            foreach ($days as $key => $day) {
                                if (empty($_POST['sessions']['OT_ID'][$key])) {  // Only add if OT_ID is empty
                                    $query = "INSERT INTO offered_time (OFFER_ID,OT_DAY, OT_STARTTIME, OT_ENDTIME, OT_PAX, OT_SLOTSLEFT, OT_TYPE) VALUES (?,?, ?, ?, ?, ?, ?)";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("issssis",$activityId, $day, $startTimes[$key], $endTimes[$key], $pax[$key], $pax[$key], $types[$key]);
                                    $stmt->execute();
                                    $stmt->close();
                                }
                            }
                        }
                        break;

                    case 'delete':
                        if (isset($_POST['OT_ID'])) {
                            $otId = $_POST['OT_ID'];
            
                            // Perform the deletion logic here
                            $query = "UPDATE offered_time SET IS_REMOVED = 1 WHERE OT_ID = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $otId);
                            $stmt->execute();
                            $stmt->close();
            
                            // Return a success message
                            echo 'success';
                        } else {
                            echo 'error: OT_ID not provided';
                        }
                        break;
                }
            }
            // Redirect to the profile page after activity update
            echo "<script>alert('Activity successfully updated!');window.location.href = 'activityCoDisplay.php?activityId=$activityId';</script>";
        } else {
            // Error: Show an error message
            echo "Error updating activity details: " . $stmtUpdate->error;
        }
    
        // Close the prepared statement for updating activity
        $stmtUpdate->close();
    }  
?>


<!DOCTYPE html>
    <head>
        <?php include('header2.php'); ?>
        <title>Edit Activity</title>
    </head>

    <script>
        function previewImage(event, id) {
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('preview_' + id).src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

    <style>
        body {
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }
        .roundedBox {
            border-radius: 15px;
            padding: 30px;
            max-width: 1000px;
            width: 100%;
            margin: 20px auto;
            background-color:   #e3f2fd;
            text-align: center;
        }
        .image-upload-section {
            display: flex;
            justify-content: center;
            gap: 20px; /* Spacing between items */
            margin-bottom: 20px;
        }
        .image-upload {
            width: 30%; /* Each upload section takes 30% of the width */
            text-align: center;
        }
        .image-upload label {
            display: block;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            width: 100%;
            height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #888;
            font-size: 14px;
        }
        .image-upload label:hover {
            background-color: #f0f0f0;
            border-color: #888;
        }
        .image-upload img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .required-text {
            font-size: 12px;
            color: #555;
            margin-top: 3px;
            text-align: center;
        }
        .straightBox {
            border-radius: 0px;
            padding: 20px;
            max-width: 1000px;
            width: 100%;
            margin: 20px auto;
            background-color:   #e3f2fd;
        }
        .form-group{
            padding-left:70px;
            padding-right:70px;
        }
        table.forTypeCat {
            width: 100%;                
            border-collapse: separate;  
            border-spacing: 5px;      
        }
        table.forTypeCat td {
            padding: 0;                
            width: 50%;                 
        }
        table.forTypeCat .form-group {
            margin-bottom: 10px;       
        }
        table.forTypeCat input.form-control {
            width: 100%;                
        }
        table.forAge {
            width: 100%;                
            border-collapse: separate;  
            border-spacing: 5px;       
        }
        table.forAge td {
            padding: 0;                 
            width: 50%;                
        }
        table.forAge .form-group {
            margin-bottom: 10px;        
        }
        table.forAge input.form-control {
            width: 100%;               
        }
        body .priceSect ,.genSect ,.seshSection{
            /* margin-top: 50px; */
            padding: 0 20px;
            max-width: 1130px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .priceHeader ,.genHeader ,.seshHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .priceHeader ,.genHeaders ,.seshHeader h2 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        h2{
            text-align: center;
            color: #333;
            font-size: 24px;
        }
        .acMinAge .acMaxAge {
            max-width: 100%;
            width: 100%;
            font-size: 1.5rem;
            padding: 10px;
            /* height: 50px; */
        }
        table.forPrice {
            width: 900px;
            border-collapse: separate;
            border-spacing: 5px;
            table-layout: fixed; /* Ensures equal column widths */
        }
        table.forPrice td {
            padding: 0;
            width: auto; /* Let the table handle equal sizing */
        }
        table.forPrice .form-group {
            margin-bottom: 10px;
        }
        table.forPrice input.form-control {
            width: 100%;
        }
        .addSesh{
            text-align: right;
            border: none;
            background-color: inherit;
            padding: 0.05px 2px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
        }
        .addSesh:hover{color: rgb(31, 109, 210);}
        .forSession {
            margin-top: 30px;
            width: 100%;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed; /* Ensures equal column width */
        }
        .forSession th, .forSession td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            font-size: 14px;
            width: 25%; 
        }
        .forSession th:nth-child(4), .forSession td:nth-child(4) {
            width: 15%; 
        }
        .forSession th {
            background-color: #f6f6f6;
            font-weight: bold;
        }
        .forSession select,.forSession input[type="time"], .forSession input[type="number"] {
            width: 90%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }
        .deleteSesh {
            display: block; /* Makes the button behave like a block element */
            margin: 10px auto 0; /* Centers the button horizontally and adds spacing */
            padding: 5px 10px;
            font-size: 14px;
            background-color:transparent; /* Red */
            color: #f44336;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        .deleteSesh:hover {
            background-color: #d32f2f; /* Darker red */
            color:white;
        }
        .addActivity {
            margin: 20px auto; 
            display: block;
            width: 200px;  
            background: linear-gradient(135deg, #448aff, #005ecb);
            color: white;  
            border: none;
            padding: 10px 15px; 
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px; 
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15); 
            transition: all 0.3s ease-in-out; 
            text-align: center; 
        }
        .addActivity:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); 
            transform: translateY(-3px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); 
        } 
        .sessionBlock.deleted {
            display: none;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 50px;
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
            margin-top: 20px; /* Pushes the footer to the bottom */
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
            <form action="" method="post" enctype="multipart/form-data">
                <div class="roundedBox">
                    <div class="image-upload-section">
                        <!-- acPic1 -->
                        <div class="image-upload">
                            <label for="acPic1">
                                <img id="preview_acPic1" 
                                    src="<?php echo isset($images[0]) && !empty($images[0]['OP_PIC']) ? 'data:image/jpeg;base64,' . $images[0]['OP_PIC'] : 'acPic1.png'; ?>" 
                                    alt="acPic1" style="width: 100%; height: 100%; object-fit: contain;">
                            </label>
                            <input type="file" id="acPic1" name="acPic1" accept="image/*" onchange="previewImage(event, 'acPic1')">
                            <input type="hidden" name="opId1" value="<?php echo isset($images[0]) ? $images[0]['OP_ID'] : ''; ?>"> <!-- Hidden input for OP_ID -->
                        </div>

                        <!-- acPic2 -->
                        <div class="image-upload">
                            <label for="acPic2">
                                <img id="preview_acPic2" 
                                    src="<?php echo isset($images[1]) && !empty($images[1]['OP_PIC']) ? 'data:image/jpeg;base64,' . $images[1]['OP_PIC'] : 'acPic2.png'; ?>" 
                                    alt="acPic2" style="width: 100%; height: 100%; object-fit: contain;">
                            </label>
                            <input type="file" id="acPic2" name="acPic2" accept="image/*" onchange="previewImage(event, 'acPic2')">
                            <input type="hidden" name="opId2" value="<?php echo isset($images[1]) ? $images[1]['OP_ID'] : ''; ?>"> <!-- Hidden input for OP_ID -->
                        </div>

                        <!-- acPic3 -->
                        <div class="image-upload">
                            <label for="acPic3">
                                <img id="preview_acPic3" 
                                    src="<?php echo isset($images[2]) && !empty($images[2]['OP_PIC']) ? 'data:image/jpeg;base64,' . $images[2]['OP_PIC'] : 'acPic3.png'; ?>" 
                                    alt="acPic3" style="width: 100%; height: 100%; object-fit: contain;">
                            </label>
                            <input type="file" id="acPic3" name="acPic3" accept="image/*" onchange="previewImage(event, 'acPic3')">
                            <input type="hidden" name="opId3" value="<?php echo isset($images[2]) ? $images[2]['OP_ID'] : ''; ?>"> <!-- Hidden input for OP_ID -->
                        </div>
                    </div>
                </div>

                <div class="straightBox">
                    <div class="genSect">
                        <div class="genHeader">
                            <h2> <b>General Details</b></h2>
                        </div>
                    </div>

                    <table class="forTypeCat" style="margin-top:30px;">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <label for="activityType">Activity Type</label>
                                    <input type="text" class="form-control" id="activityType" name="activityType" value="<?php echo $data['CATEGORY_ID']; ?>" readonly>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="activityCategory">Category</label>
                                    <input type="text" class="form-control" id="activityCategory" name="activityCategory" value="<?php echo $data['CATEGORY_ID']; ?>" readonly>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="form-group">
                        <label for="acName">Activity Name</label>
                        <input type="text" class="form-control" id="acName" name="acName" value="<?php echo $data['OFFER_NAME']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="acDesc">Activity Description</label>
                        <textarea class="form-control" id="acDesc" name="acDesc" required><?php echo $data['OFFER_DESC']; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="acState">State</label>
                        <input type="text" class="form-control" id="acState" name="acState" value="<?php echo $data['OFFER_STATE']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="acLocation">Location</label>
                        <input type="text" class="form-control" id="acLocation" name="acLocation" value="<?php echo $data['OFFER_LOCATION']; ?>" required>
                    </div>

                    <table class="forAge">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <label for="acMinAge">Minimum Age</label>
                                    <input type="number" class="form-control" id="acMinAge" name="acMinAge" value="<?php echo $data['OFFER_MINAGE']; ?>" min="3" max="18">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="acMaxAge">Maximum Age</label>
                                    <input type="number" class="form-control" id="acMaxAge" name="acMaxAge" value="<?php echo $data['OFFER_MAXAGE']; ?>" min="3" max="18">
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="priceSect">
                        <div class="priceHeader">
                            <h2> <b>Price Details</b></h2>
                        </div>
                    </div>

                    <table class="forPrice" style="margin-top: 20px">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <label for="acTryPrice">Trial Class</label>
                                    <input type="number" class="form-control" id="acTryPrice" name="acTryPrice" value="<?php echo $trialPrice; ?>" step="0.01">
                                    <p class="required-text">*Price per one class</p>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="acMonthPrice">One Month Package</label>
                                    <input type="number" class="form-control" id="acMonthPrice" name="acMonthPrice" value="<?php echo $monthPrice; ?>" step="0.01">
                                    <p class="required-text">*Price per month/4 classes</p>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="acMonthPrice">One Year Package</label>
                                    <input type="number" class="form-control" id="acYearPrice" name="acYearPrice" value="<?php echo $yearPrice; ?>" step="0.01">
                                    <p class="required-text">*Price per year/48 classes</p>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="seshSection">
                        <div class="seshHeader">
                            <h2> Session Details</h2>
                            <button type="button" class="addSesh" onclick="setAction('add')">Add Session</button>
                        </div>
                    </div>

                    <input type="hidden" name="action" id="actionField" value="update"> <!-- Default action is 'update' -->
                    <div id="sessionContainer">
                        <p class="required-text">Only sessions with no bookings are displayed and can be edited or deleted.</p>
                        <?php
                        if ($sessions) {
                            foreach ($sessions as $session) {
                                ?>
                                <div class="sessionTemplate sessionBlock">
                                    <table class="forSession">
                                        <tr>
                                            <th>Day</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Pax</th>
                                            <th>Session For</th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <select name="sessions[day][]">
                                                    <option value="Monday" <?php echo $session['OT_DAY'] == 'MONDAY' ? 'selected' : ''; ?>>Monday</option>
                                                    <option value="Tuesday" <?php echo $session['OT_DAY'] == 'TUESDAY' ? 'selected' : ''; ?>>Tuesday</option>
                                                    <option value="Wednesday" <?php echo $session['OT_DAY'] == 'WEDNESDAY' ? 'selected' : ''; ?>>Wednesday</option>
                                                    <option value="Thursday" <?php echo $session['OT_DAY'] == 'THURSDAY' ? 'selected' : ''; ?>>Thursday</option>
                                                    <option value="Friday" <?php echo $session['OT_DAY'] == 'FRIDAY' ? 'selected' : ''; ?>>Friday</option>
                                                    <option value="Saturday" <?php echo $session['OT_DAY'] == 'SATURDAY' ? 'selected' : ''; ?>>Saturday</option>
                                                    <option value="Sunday" <?php echo $session['OT_DAY'] == 'SUNDAY' ? 'selected' : ''; ?>>Sunday</option>
                                                </select>
                                            </td>
                                            <td><input type="time" name="sessions[start_time][]" value="<?php echo $session['OT_STARTTIME']; ?>"></td>
                                            <td><input type="time" name="sessions[end_time][]" value="<?php echo $session['OT_ENDTIME']; ?>"></td>
                                            <td><input type="number" name="sessions[pax][]" min="1" value="<?php echo $session['OT_PAX']; ?>"></td>
                                            <td>
                                                <select name="sessions[session_type][]">
                                                    <option value="TRIAL CLASS" <?php echo $session['OT_TYPE'] == 'TRIAL CLASS' ? 'selected' : ''; ?>>Trial Class</option>
                                                    <option value="ONE MONTH PACKAGE" <?php echo $session['OT_TYPE'] == 'ONE MONTH PACKAGE' ? 'selected' : ''; ?>>One Month Package</option>
                                                    <option value="ONE YEAR PACKAGE" <?php echo $session['OT_TYPE'] == 'ONE YEAR PACKAGE' ? 'selected' : ''; ?>>One Year Package</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    <input type="hidden" id="activityId" name="activityId"  value="<?= $activityId ?>">
                                    <input type="hidden" name="sessions[OT_ID][]" value="<?php echo $session['OT_ID']; ?>">
                                    <button type="button" class="deleteSesh" onclick="setAction('delete', this)">Delete Session</button>

                                </div>
                                <?php
                            }
                        } else {
                            echo "<p>No available sessions.</p>";
                        }
                        ?>
                    </div>
                <input type="hidden" name="activityId" value="<?= $activityId ?>">
                <button class="addActivity"type="submit" name="update">Update</button>
            </form>
        </div>

        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                <button class="footerBtn" onclick="window.location.href='forCoach.php';">Support FAQ</button>
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

    <script>
    function setAction(actionType, button) {
        const actionField = document.getElementById("actionField");
        actionField.value = actionType; 
        switch (actionType) {
            case 'add':
                addSession();  
                break;
            case 'delete':
                deleteSession(button);  
                break;
            default:
                console.log("Unknown action:", actionType);
                break;
        }
    }

    function addSession() {
        const container = document.getElementById("sessionContainer");
        const template = document.querySelector(".sessionTemplate");
        const newSession = template.cloneNode(true);

        newSession.style.display = "block";
        newSession.classList.remove("sessionTemplate");

        // Reset input values for the newly added session
        const inputs = newSession.querySelectorAll("input, select");
        inputs.forEach(input => {
            input.value = ''; 
        });

        container.appendChild(newSession);
    }

    
    function deleteSession(button) {
        // console.log("Delete button clicked!");

        // // Find the parent session block (the closest div with class 'sessionBlock')
        // const sessionBlock = button.closest('.sessionBlock');
        // console.log("Session Block:", sessionBlock);

        // // Get the session ID (OT_ID) from the input field within the session block
        // const sessionId = sessionBlock.querySelector('input[name="sessions[OT_ID][]"]').value;
        // console.log("Session ID:", sessionId);

        // const activityId = document.getElementById('activityId').value;
        // console.log("ACTIVITY ID:", activityId);

        // // Prompt for confirmation before deleting
        // const confirmation = confirm("Are you sure you want to delete this session?");
        // if (!confirmation) {
        //     console.log("Deletion canceled.");
        //     return; // Exit if the user cancels
        // }

        console.log("Delete button clicked!");

        
        const sessionBlock = button.closest('.sessionBlock');
        console.log("Session Block:", sessionBlock);


        const sessionId = sessionBlock.querySelector('input[name="sessions[OT_ID][]"]').value;
        console.log("Session ID:", sessionId);

        const activityId = document.getElementById('activityId').value;
        console.log("ACTIVITY ID:", activityId);


        const confirmation = confirm("Are you sure you want to delete this session?");
        if (!confirmation) {
            console.log("Deletion canceled.");
            return; 
        }

        const formData = new FormData();
        formData.append('action', 'delete');  
        formData.append('OT_ID', sessionId);  
        formData.append('activityId', activityId);  

        fetch('activityEdit.php', {  
            method: 'POST',
            body: formData
        })
        sessionBlock.remove();
        console.log("Session block removed from DOM.");
    }

        function validatePictures(event) {
            // Get the file inputs
            const pic1 = document.getElementById('acPic1').files.length;
            const pic2 = document.getElementById('acPic2').files.length;
            const pic3 = document.getElementById('acPic3').files.length;

            // Check if all three images are uploaded
            if (pic1 === 0 || pic2 === 0 || pic3 === 0) {
                event.preventDefault();  // Prevent form submission
                alert('Please upload all 3 pictures.');
            }
        }
    </script>
</html>