<?php
    session_start();
    include("connect.php");

    $selectedState = isset($_GET['selState']) ? $_GET['selState'] : '';
    $selectedArt = isset($_GET['selArt']) ? $_GET['selArt'] : '';

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
    WHERE at.TYPE_NAME = 'PERFORMING ARTS' AND oa.IS_AVAILABLE = 1";

    $conditions = [];
    $params = [];
    $types = "";

    // Add state filter if selected
    if (!empty($selectedState)) {
        $conditions[] = "oa.OFFER_STATE = ?";
        $params[] = $selectedState;
        $types .= "s";
    }

    // Add art filter if selected
    if (!empty($selectedArt)) {
        $conditions[] = "ac.CATEGORY_NAME = ?";
        $params[] = $selectedArt;
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
        <title>Performing Arts</title>
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
            width: 100%; /* Full width */
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
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); /* Match card gradient */
            width: 1000px; /* Keep your specified width */
            height: 50px; /* Keep your specified height */
            font-size: 18px;
            border-radius: 12px; /* Match card border-radius */
            padding: 10px;
            border: 1px solid #bbdefb; /* Match card colors */
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); /* Match card shadow */
            color: #004085; /* Match card text color */
            transition: all 0.3s ease-in-out; /* Smooth transitions */
            cursor: pointer;
        }
        select.form-control:hover {
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); /* Enhance on hover */
            transform: translateY(-2px); /* Slight hover effect */
        }
        select.form-control:focus {
            outline: none;
            border-color: #448aff; /* Match card active state */
            box-shadow: 0px 8px 25px rgba(0, 123, 255, 0.5); /* Stronger shadow on focus */
        }
        select.form-control option {
            background: #e3f2fd; /* Match dropdown list background */
            color: #004085; /* Match dropdown list text color */
            padding: 10px;
        }
        select.form-control option:checked {
            background: #82b1ff; /* Match active card background */
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
            background: #fff; /* White background by default */
            color: #333; /* Dark text for readability */
            display: flex;
            flex-direction: column; /* Stack the content vertically */
            transition: all 0.3s ease-in-out; /* Smooth transition for hover effect */
        }
        /* Hover effect for the card */
        .card:hover {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); /* Lighter blue gradient on hover */
            transform: translateY(-10px); /* Slight lift on hover */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); /* Subtle shadow on hover */
        }
        /* Image placeholder styling */
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
            opacity: 0.8; /* Slight transparency effect on image when hovered */
        }
        /* Title styling inside the card */
        .cardTitle b {
            margin: 4px auto;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase; /* Adding uppercase for a cleaner look */
        }
        /* Details section inside the card */
        .cardDetails {
            flex-grow: 1;
            text-align: left;
        }
        /* Paragraph styling inside the card */
        .cardDetails p {
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
            margin: 4px 0;
            opacity: 0.8; /* Slight opacity for subtle effect */
        }
        /* "No Activity" message styling */
        .no-activity {
            margin: 50px;
            text-align: center;
            font-size: 18px;
            color: #666;
        }
        /* Responsive styling for mobile */
        @media (max-width: 768px) {
            .cardCont {
                margin-left: 20px;
                margin-right: 20px;
            }
            .card {
                width: 100%;
                height: auto; /* Let the height adjust on smaller screens */
                margin: 10px 0;
            }
            .card .image-placeholder {
                width: 100px;
                height: 100px;
            }
        }
        .btnFilter {
            width: 1020px;
            background: linear-gradient(135deg, #448aff, #005ecb); /* Match active card background */
            color: white; /* Contrast text */
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 12px; /* Match the dropdown and card border-radius */
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            cursor: pointer;
            transition: all 0.3s ease-in-out; /* Smooth transition */
        }
        .btnFilter:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); /* Darker shade on hover */
            transform: translateY(-3px); /* Slight hover lift */
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); /* Enhanced shadow */
        }
        .btnFilter:active {
            background: linear-gradient(135deg, #005ecb, #003d9a); /* Darker blue on click */
            transform: translateY(1px); /* Button press effect */
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2); /* Reduced shadow on press */
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
                        <select class="form-control" id="selArt" name="selArt" style="max-width:500px">
                            <option value="">-- Select Performing Art --</option>
                            <option value="MUSICAL THEATRE" <?php if ($selectedArt  === 'MUSICAL THEATRE') echo 'selected'; ?>>MUSICAL THEATRE</option>
                            <option value="DANCE" <?php if ($selectedArt  === 'DANCE') echo 'selected'; ?>>DANCE</option>
                            <option value="VOCAL & SINGING" <?php if ($selectedArt === 'VOCAL & SINGING') echo 'selected'; ?>>VOCAL & SINGING</option>
                        </select>
                        
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="submit" class="btnFilter">Filter</button>
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
