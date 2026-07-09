<?php
    session_start();
    include("connect.php");

    $selectedState = isset($_GET['selState']) ? $_GET['selState'] : '';
    $selectedSport = isset($_GET['selSport']) ? $_GET['selSport'] : '';

    // SQL query to get the activities
    $query = "SELECT oa.OFFER_ID, oa.OFFER_NAME, oa.OFFER_STATE, oa.OFFER_LOCATION, oa.OFFER_MINAGE, oa.OFFER_MAXAGE, ac.CATEGORY_NAME, op.OP_PIC
    FROM offered_activity oa
    JOIN activity_category ac ON oa.CATEGORY_ID = ac.CATEGORY_ID
    JOIN activity_type at ON ac.TYPE_ID = at.TYPE_ID
    LEFT JOIN (
        SELECT OP_PIC, OFFER_ID
        FROM offered_pic
        GROUP BY OFFER_ID
        LIMIT 1
    ) op ON oa.OFFER_ID = op.OFFER_ID
    WHERE at.TYPE_NAME = 'SPORTS' AND oa.IS_AVAILABLE = 1";

    $conditions = [];
    $params = [];
    $types = "";

    // Add state filter if selected
    if (!empty($selectedState)) {
        $conditions[] = "oa.OFFER_STATE = ?";
        $params[] = $selectedState;
        $types .= "s";
    }

    // Add sport filter if selected
    if (!empty($selectedSport)) {
        $conditions[] = "ac.CATEGORY_NAME = ?";
        $params[] = $selectedSport;
        $types .= "s";
    }

    // Append conditions to the query
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

?>

<!DOCTYPE html>
    <head>
        <?php include("header.php");?>
        <title>Sports</title>
    </head>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 60px; /* Adjust based on the height of your navbar */
        }
        main {
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
        .roundedBox {
            border-radius: 15px;
            padding-top: 20px;
            padding-bottom: 20px;
            padding-left: 40px;
            padding-right: 40px;
            max-width: 600px;
            margin: auto;
            background-color: #ffffff; 
            box-shadow: 0 4px 8px rgba(72, 161, 255, 0.3); 
            color: #004085;
            border: 1px solid #ddd; 
        }
        .roundedBox .form-group {
            display: block; /* Change flex to block for stacking */
            margin-bottom: 15px;
        }
        .roundedBox .form-group label {
            font-size: 14px;
            font-weight: bold;
            color: #004085;
            text-align: left;
            margin-bottom: 5px; /* Adds space between label and input */
        }
        .roundedBox .form-control {
            font-size: 14px; 
            font-weight: normal;
            padding: 10px;
            background-color: #f9f9f9; 
            border: 1px solid #ddd; 
            border-radius: 5px;
            color: #004085;
            width: 100%; /* Ensure input takes full width */
            margin-left: 0; /* Reset the margin-left */
            text-align: left;
        }
        .roundedBox .form-control:focus {
            border-color: #004ba0;
            box-shadow: 0 0 5px rgba(0, 75, 160, 0.3); 
            outline: none;
        }
        .roundedBox .form-control:read-only {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            color: #777;
            cursor: not-allowed;
        }
        .roundedBox .form-control:read-only:focus {
            border-color: #ddd;
            box-shadow: none;
            outline: none;
        }
        .submit {
            width: 100%; 
            background-color: rgb(55, 135, 227); 
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px; 
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s ease; 
            display: block; 
            margin: 0 auto; 
        }
        .submit:hover {
            background-color: #004ba0; 
        }
        .roundedBox .form-group.text-center {
            text-align: center;
        }
        .forFilter {
            margin: 0 auto;
        }
        select.form-control {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); 
            width: 1000px;
            height: 50px; 
            font-size: 18px;
            border-radius: 12px; 
            padding: 10px;
            border: 1px solid #bbdefb; 
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); 
            color: #004085;
            transition: all 0.3s ease-in-out; 
            cursor: pointer;
        }
        select.form-control:hover {
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px); 
        }
        select.form-control:focus {
            outline: none;
            border-color: #448aff; 
            box-shadow: 0px 8px 25px rgba(0, 123, 255, 0.5); 
        }
        select.form-control option {
            background: #e3f2fd;
            color: #004085; 
            padding: 10px;
        }
        select.form-control option:checked {
            background: #82b1ff;
            color: white;
        }
        .form-group {
            padding-left: 70px;
            padding-right: 70px;
        }
        td {
            padding: 10px;
        }
        .cardCont {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            max-width: 100%;
            margin-left: 50px;
            margin-right: 50px;
            padding: 20px;
            /* background: #f0f4f8; Light background for the container */
            border-radius: 10px;
        }
        .card {
            width: 300px; /* Fixed width for the card */
            height: 350px; /* Fixed height for the card */
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 5px;
            background: #fff; 
            color: #333; 
            display: flex;
            flex-direction: column; 
            transition: all 0.3s ease-in-out; 
        }
        .card:hover {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            transform: translateY(-10px); 
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); 
        }
        .card .image-placeholder {
            width: 250px;
            height: 150px;
            background-color: #f0f0f0;
            border-radius: 8px;
            margin: 16px auto;
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s ease-in-out;
        }
        .card:hover .image-placeholder {
            opacity: 0.8; 
        }
        .cardTitle b {
            margin: 4px auto;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .cardDetails {
            flex-grow: 1;
            text-align: left;
        }
        .cardDetails p {
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
            margin: 4px 0;
            opacity: 0.8; 
        }
        .no-activity {
            margin: 50px;
            text-align: center;
            font-size: 18px;
            color: #666;
        }
        @media (max-width: 768px) {
            .cardCont {
                margin-left: 20px;
                margin-right: 20px;
            }
            .card {
                width: 100%;
                height: auto; 
                margin: 10px 0;
            }
            .card .image-placeholder {
                width: 100px;
                height: 100px;
            }
        }
        .btnFilter {
            width: 1020px;
            background: linear-gradient(135deg, #448aff, #005ecb); 
            color: white; /* Contrast text */
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 12px; 
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); 
            cursor: pointer;
            transition: all 0.3s ease-in-out; 
        }
        .btnFilter:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); 
            transform: translateY(-3px); 
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); 
        }
        .btnFilter:active {
            background: linear-gradient(135deg, #005ecb, #003d9a); 
            transform: translateY(1px); 
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2); 
        }
    </style>

    <body>
        <form method="get" action="">
            <table class="forFilter">
                <tr>
                    <td>
                        <select class="form-control" id="selState" name="selState" style="max-width:500px">
                            <option value="">-- Select State --</option>
                            <option value="PERAK" <?php if ($selectedState === 'PERAK') echo 'selected'; ?>>PERAK</option>
                            <option value="KUALA LUMPUR" <?php if ($selectedState === 'KUALA LUMPUR') echo 'selected'; ?>>KUALA LUMPUR</option>
                            <option value="SELANGOR" <?php if ($selectedState === 'SELANGOR') echo 'selected'; ?>>SELANGOR</option>
                            <option value="MELAKA" <?php if ($selectedState === 'MELAKA') echo 'selected'; ?>>MELAKA</option>
                            <option value="JOHOR" <?php if ($selectedState === 'JOHOR') echo 'selected'; ?>>JOHOR</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control" id="selSport" name="selSport" style="max-width:500px">
                            <option value="">-- Select Sport --</option>
                            <option value="TENNIS" <?php if ($selectedSport === 'TENNIS') echo 'selected'; ?>>TENNIS</option>
                            <option value="SWIMMING" <?php if ($selectedSport === 'SWIMMING') echo 'selected'; ?>>SWIMMING</option>
                            <option value="HOCKEY" <?php if ($selectedSport === 'HOCKEY') echo 'selected'; ?>>HOCKEY</option>
                        </select>
                        
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button class="btnFilter" type="submit" >Filter</button>
                    </td>
                </tr>
            </table>
        </form>
        

        <div class="cardCont">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card" onclick="window.location.href='activityDisplay.php?activityId=<?php echo urlencode($row['OFFER_ID']); ?>'">
                    <div class="image-placeholder"  
                        style="background-image: url('data:image/jpeg;base64,<?php echo $row['OP_PIC']; ?>');">
                    </div>
                        <div class="cardDetails">
                            <div class="cardTitle">
                                <p><b><?php echo htmlspecialchars($row['OFFER_NAME']); ?></b></p>
                            </div>
                            <p><b>State:</b> <?php echo htmlspecialchars($row['OFFER_STATE']); ?></p>
                            <p><b>Location:</b> <?php echo htmlspecialchars($row['OFFER_LOCATION']); ?></p>
                            <p><b>Age Range:</b> <?php echo htmlspecialchars($row['OFFER_MINAGE']); ?> - <?php echo htmlspecialchars($row['OFFER_MAXAGE']); ?> years</p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-activity">Sorry, no activity available.</div>
            <?php endif; ?>
        </div>  
        
        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                    <button class="footerBtn"onclick="window.location.href='forCoach.php';">For Coaches</button>
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
