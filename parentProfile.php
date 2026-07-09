<?php 
    session_start();
    include('connect.php'); 

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    // Fetch parent data
    $sql = "SELECT * FROM parent WHERE PARENT_USERNAME = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $parent_username = $row['PARENT_USERNAME'];
        $parent_name = $row['PARENT_NAME'];
        $parent_phone = $row['PARENT_PHONE'];
        $parent_email = $row['PARENT_EMAIL'];
    } else {
        echo "<script>alert('No data found.');</script>";
        exit();
    }
    
    $sql = "SELECT PARENT_AMOUNT FROM parent_wallet WHERE PARENT_USERNAME = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $parent_amount = $row['PARENT_AMOUNT'];  
    } else {
        echo "<script>alert('No data found. ');</script>";
        exit();
    }

    // Fetch children data
    $childrenSql = "SELECT * FROM child WHERE PARENT_USERNAME = '$username'";
    $childrenResult = $conn->query($childrenSql);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_child'])) {
        $childId = $_POST['child_id'];
        
        // Check HAS_ACTIVITY value
        $activityCheckSql = "SELECT HAS_ACTIVITY FROM child WHERE CHILD_ID = ?";
        $stmt = $conn->prepare($activityCheckSql);
        $stmt->bind_param('i', $childId);
        $stmt->execute();
        $result = $stmt->get_result();
        $child = $result->fetch_assoc();

        if ($child && $child['HAS_ACTIVITY'] == 1) {
            echo "<script>alert('Sorry, you cannot remove this child because they are involved in an activity.');</script>";
        } else {
            echo "<script>
                if (confirm('Are you sure you want to remove this child?')) {
                    window.location.href = 'childRemove.php?child_id=$childId';
                }
            </script>";
        }
    }
?>

<!DOCTYPE html>

    <head>
        <?php include('header.php'); ?>
        <title>Profile</title>
    </head>

    <script>
        $(document).ready(function(){
            // Initially show bookingPg section, hide others
            $("#bookingPg").show();
            $("#historyPg").hide(); 

            // Apply initial border-bottom to the btnBooking button
            $(".btnBooking").css("border-bottom", "2px solid rgb(0, 0, 0)");

            // Click event for the Booking button
            $(".btnBooking").click(function(){
                $("#bookingPg").show();
                $("#historyPg").hide();
                $(".btnBooking").css("border-bottom", "2px solid rgb(0, 0, 0)");
                $(".btnHistory").css("border-bottom", "none");
            });

            // Click event for the History button
            $(".btnHistory").click(function(){
                $("#historyPg").show();
                $("#bookingPg").hide();
                $(".btnHistory").css("border-bottom", "2px solid rgb(0, 0, 0)");
                $(".btnBooking").css("border-bottom", "none");
            });
        });
    </script>


    <style>
        body {
            padding-top: 70px; 
            font-family: Arial, sans-serif;
        }
        body .childrenSection{
            margin-top: 10px;
            padding: 0 20px;
            max-width: 1130px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container{
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            /* border: 1px solid rgb(9, 78, 189); */
        }
        .roundedBox {
            border-radius: 15px;
            padding: 20px;
            max-width: 600px;
            margin: auto;
            background-color: white; 
            box-shadow: 0 4px 8px rgba(72, 161, 255, 0.3); 
            color: #004085;  
            border: 1px solid #ddd;  
        }
        .roundedBox .form-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: center;
        }
        .roundedBox .form-group label {
            font-size: 12px; 
            font-weight: bold;
            color: #004085;
            width: 150px;  
            text-align: left; 
        }
        .roundedBox .form-control {
            font-size: 12px;  
            font-weight: normal;
            padding: 10px;
            background-color: transparent;  
            border: 1px solid #ddd;  
            border-radius: 5px;
            color: #004085;
            width: calc(100% - 170px);  
            margin-left: 15px;
            text-align: left;
            pointer-events: none;  
        }
        .editParent {
            margin-left:450px;
            background-color:rgb(55, 135, 227);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 12px;  /* Smaller font size */
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
            width: auto;
            transition: background-color 0.3s ease;
        }
        .editParent:hover {
            background-color: #004ba0;
        }
        .roundedBox .form-group.text-center {
            text-align: center;
        }
        .childrenHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .childrenHeader h2 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        .addChild {
            text-align: right;
            border: none;
            background-color: inherit;
            padding: 0.05px 2px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
        }
        .addChild:hover{color: rgb(31, 109, 210);}
        .walletBox {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); 
            border-radius: 15px; 
            padding: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 950px; 
            margin: auto; 
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); 
        }
        .walletBox .walletAmount {
            font-size: 20px; 
            font-weight: bold; 
            color: #004085; 
        }
        .walletBox .reloadButton {
            background-color: #448aff; 
            color: white; 
            border: none;
            padding: 10px 20px; 
            font-size: 14px; 
            font-weight: bold; 
            border-radius: 8px; 
            cursor: pointer; 
            transition: background-color 0.3s ease, transform 0.2s ease; 
        }
        .walletBox .reloadButton:hover {
            background-color: #005ecb; 
            transform: scale(1.05); 
        }
        .bookingSection button:hover{
            border-bottom: 2px solid rgb(0, 0, 0);
            color: #000000;
        }
        #childInfo {
            margin-top: 20px;
        }
        #childInfo .child-info {
            background-color:#e3f2fd ;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(66, 154, 249, 0.2); 
            margin-bottom: 20px;
            color: #004085; 
            border: 1px solid #ddd;
            width: calc(33% - 20px); 
            margin-right: 10px;
            margin-left: 10px;
            box-sizing: border-box; 
            display: inline-block;
            vertical-align: top;
        }
        #childInfo .child-info:nth-child(3n+1) {
            clear: both;
        }
        #childInfo .childDetails {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        #childInfo .childDetails label {
            font-size: 12px;
            font-weight: bold;
            color: #004085;
            width: 150px; /* Fixed width for labels */
            text-align: left;
        }
        #childInfo .childDetails .form-control {
            font-size: 12px;
            padding: 8px;
            background-color: transparent; 
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #004085;
            width: 300px; 
            text-align: left;
            pointer-events: none;  
        }
        #childInfo .childActions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
        }
        #childInfo .childActions .editChild {
            background-color: #82b1ff;
            color: #004085;
            padding: 10px 15px; 
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: background-color 0.3s ease;
            width: 120px; 
            text-align: center;
            box-sizing: border-box;
        }
        #childInfo .childActions .editChild:hover {
            background-color: #448aff; /* Darker blue on hover */
        }
        #childInfo .childActions button {
            text-align: center;
            background-color: #ff4e4e; /
            color: white;
            padding: 10px 15px; 
            border-radius: 5px;
            border: none;
            font-size: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 120px; 
        }
        #childInfo .childActions button:hover {
            background-color: #ff2a2a; 
        }
        #childInfo h4 {
            margin-top: 20px;
            color: #004085;
            font-size: 14px;
        }
        .bookingSection {
            display: flex;
            justify-content: space-around; 
            margin-bottom: 20px;
            padding: 10px 0; 
        }
        .bookingSection button {
            border: none;
            background-color: transparent;
            cursor: pointer;
            padding: 16px 32px;
            font-size: 16px;
            color: black; 
            font-weight: bold; 
            transition: all 0.3s ease; 
        }
        .bookingSection button:hover {
            border-bottom: 3px solid #448aff; 
            color: #005ecb; 
            transform: scale(1.05); 
        }
        .bookingSection button:active {
            border-bottom: 3px solid #448aff; 
            color: #448aff;
            transform: scale(0.98); 
        }
        #bookingPg {
            border: 2px solid #82b1ff;
            border-radius: 8px;
            padding-top: 5px; 
            padding-left: 10px; 
            padding-right: 10px; 
            padding-bottom: 10px; 
            border-radius: 10px;
            max-width: 1000px; 
            width: 100%; 
            margin: 0 auto; 
        }
        #historyPg{
            border: 2px solid  #448aff; 
            border-radius: 8px;
            padding-top: 5px; 
            padding-left: 10px; 
            padding-right: 10px; 
            padding-bottom: 10px; 
            border-radius: 10px; 
            max-width: 1000px; 
            width: 100%; /
            margin: 0 auto; 
        }
        .allBookings {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .bookingCard {
            margin-top: 20px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            text-align: left;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .bookingCard:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }
        .activityImage {
            width: 250px;
            height: 150px;
            margin-right: 15px; /* Space between image and text */
            overflow: hidden; /* Prevents image from overflowing the container */
            border-radius: 8px; /* Optional: adds rounded corners to the image container */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e0e0e0; /* Optional: background color for when there is no image */
        }
        .activityImage img {
            width: 100%; /* Ensures the image fills the container */
            height: 100%; /* Ensures the image fills the container */
            object-fit: cover; /* Ensures the image is resized proportionally */
            border-radius: 8px; /* Optional: adds rounded corners to the image */
        }
        .bookingDetails {
            padding: 15px;
        }
        .bookingDetails p {
            margin: 5px 0;
            font-size: 14px;
        }
        .noActivity {
            margin-top: 20px;
            text-align: center;
            font-size: 16px;
            color: #888;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 90px;
            /* padding-top: 60px; Adjust based on the height of your navbar */
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
        <!-- display parent info -->
        <div class="container">
            <div class="roundedBox">
                <form id="parentInfo" method="POST" action="parentEdit.php">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <span id="username" class="form-control" name="username" value="<?php echo $parent_username; ?>"><?php echo $parent_username; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <span id="name" class="form-control" name="name" value="<?php echo $parent_name; ?>"><?php echo $parent_name; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <span id="phone" class="form-control" name="phone" value="<?php echo $parent_phoen ?>"><?php echo $parent_phone; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <span id="email" class="form-control" name="email" value="<?php echo $parent_email; ?>"><?php echo $parent_email; ?></span>
                    </div>
                    <div class="form-group text-center">
                        <button type="button"class="editParent" onclick="window.location.href='parentEdit.php';">Edit Profile</button>
                    </div>
                </form>
            </div>

            <div class="childrenSection">
                <div class="childrenHeader">
                    <h2>My Wallet</h2>
                </div>
            </div>

            <!-- display parent wallet -->
            <div class="walletBox" style="margin-top: 20px;">
                <div class="walletAmount"><b>RM<?php echo $parent_amount; ?></b></div>
                <button class="reloadButton" onclick="window.location.href='parentWallet.php';">View Transactions</button>
            </div>

            <!-- display child section -->
            <div class="childrenSection">
                <div class="childrenHeader">
                    <h2>Children</h2>
                    <button class="addChild" onclick="window.location.href='childAdd.php';">Add Child</button>
                </div>
            </div>

            <!-- display child info -->
            <div id="childInfo">
                <?php if ($childrenResult->num_rows > 0): ?>
                    <?php while ($child = $childrenResult->fetch_assoc()): ?>
                        <div class="child-info">
                            <div class="childDetails">
                                <label for="childName">Name</label>
                                <input type="text" class="form-control" id="childName" value="<?= htmlspecialchars($child['CHILD_NAME']); ?>" readonly>
                            </div>

                            <div class="childDetails">
                                <label for="childAge">Age</label>
                                <input type="text" class="form-control" id="childAge" value="<?= htmlspecialchars($child['CHILD_AGE']); ?>" readonly>
                            </div>

                            <div class="childDetails">
                                <label for="childGender">Gender</label>
                                <input type="text" class="form-control" id="childGender" value="<?= htmlspecialchars($child['CHILD_GENDER']); ?>" readonly>
                            </div>

                            <div class="childActions">
                                <form method="POST" action="">
                                    <input type="hidden" name="child_id" value="<?= $child['CHILD_ID']; ?>">
                                    <a class="editChild" href="childEdit.php?child_id=<?php echo $child['CHILD_ID']; ?>">Edit Child</a>
                                    <button type="submit" name="remove_child" >Remove Child</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <h4 style="margin-top: 20px;"><center>No child/children added yet</center></h4>
                <?php endif; ?>
            </div>

            <!-- display bookings section -->
            <div class="bookingSection" style="margin-top: 20px; margin-bottom:20px;">
                <button class="btnBooking">Bookings</button>
                <button class="btnHistory">Booking Histories</button>
            </div>

            <div id="bookingPg">
                <div class="allBookings">
                    <?php
                    // Fetch all bookings made by the parent
                    $sqlBookings = "SELECT booking.BOOKING_ID, offered_activity.OFFER_NAME, offered_activity.OFFER_ID
                                    FROM booking 
                                    JOIN offered_activity ON booking.OFFER_ID = offered_activity.OFFER_ID
                                    WHERE booking.PARENT_USERNAME = '$username' AND IS_DONE=0 AND IS_CANCELED=0";
                    $resultBookings = $conn->query($sqlBookings);

                    if ($resultBookings->num_rows > 0):
                        while ($booking = $resultBookings->fetch_assoc()):
                            $offerId = $booking['OFFER_ID'];

                            // Fetch the base64 image from the offered_pic table using OFFER_ID
                            $sqlImage = "SELECT OP_PIC FROM offered_pic WHERE OFFER_ID = '$offerId' LIMIT 1";
                            $resultImage = $conn->query($sqlImage);
                            $image = null;
                            if ($resultImage->num_rows > 0) {
                                $imageData = $resultImage->fetch_assoc();
                                $image = $imageData['OP_PIC']; // Base64 image string
                            }
                            ?>
                            <div class="bookingCard" onclick="window.location.href='bookedActivity.php?bookingId=<?php echo urlencode($booking['BOOKING_ID']); ?>'">
                                <div class="activityImage">
                                    <?php if ($image): ?>
                                        <img src="data:image/jpeg;base64,<?php echo $image; ?>" alt="Activity Image" />
                                    <?php else: ?>
                                        <p>No image available</p>
                                    <?php endif; ?>
                                </div>
                                <div class="bookingDetails">
                                    <p><b><?php echo htmlspecialchars($booking['OFFER_NAME']); ?></b></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="noBooking">No current booking.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="historyPg">
                <div class="allBookings">
                    <?php
                    // Fetch all bookings made by the parent
                    $sqlBookings = "SELECT booking.BOOKING_ID, offered_activity.OFFER_NAME, offered_activity.OFFER_ID
                                    FROM booking 
                                    JOIN offered_activity ON booking.OFFER_ID = offered_activity.OFFER_ID
                                    WHERE booking.PARENT_USERNAME = '$username' AND IS_DONE=1 AND IS_CANCELED=0";
                    $resultBookings = $conn->query($sqlBookings);

                    if ($resultBookings->num_rows > 0):
                        while ($booking = $resultBookings->fetch_assoc()):
                            $offerId = $booking['OFFER_ID'];

                            // Fetch the base64 image from the offered_pic table using OFFER_ID
                            $sqlImage = "SELECT OP_PIC FROM offered_pic WHERE OFFER_ID = '$offerId' LIMIT 1";
                            $resultImage = $conn->query($sqlImage);
                            $image = null;
                            if ($resultImage->num_rows > 0) {
                                $imageData = $resultImage->fetch_assoc();
                                $image = $imageData['OP_PIC']; // Base64 image string
                            }
                            ?>
                            <div class="bookingCard" onclick="window.location.href='doneActivity.php?bookingId=<?php echo urlencode($booking['BOOKING_ID']); ?>'">
                                <div class="activityImage">
                                    <?php if ($image): ?>
                                        <img src="data:image/jpeg;base64,<?php echo $image; ?>" alt="Activity Image" />
                                    <?php else: ?>
                                        <p>No image available</p>
                                    <?php endif; ?>
                                </div>
                                <div class="bookingDetails">
                                    <p><b><?php echo htmlspecialchars($booking['OFFER_NAME']); ?></b></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="noBooking">No current booking.</div>
                    <?php endif; ?>
                </div>
            </div>
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
