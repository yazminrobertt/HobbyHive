<?php   
    session_start();
    include('connect.php'); 

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];
    $sql = "SELECT COACHWALLET_ID, COACH_AMOUNT FROM coach_wallet WHERE COACH_USERNAME = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $coach_amount = $row['COACH_AMOUNT']; 
        $coachwallet_id = $row['COACHWALLET_ID']; 
    } else {
        echo "<script>alert('No data found. ');</script>";
        exit();
    }

    // $sqlTrans = "SELECT PAYMENT_TYPE, PAYMENT_AMOUNT FROM booking_payment WHERE COACHWALLET_ID = '$coachwallet_id'";
    // $resultTrans = $conn->query($sqlTrans);
    $sqlTrans = "
    SELECT 
        bp.PAYMENT_TYPE, 
        bp.PAYMENT_AMOUNT, 
        bp.BOOKING_ID,
        oa.OFFER_NAME,
        b.PARENT_USERNAME,
        b.BOOKING_PLACEMENTDATE
    FROM 
        booking_payment bp
    JOIN 
        booking b ON bp.BOOKING_ID = b.BOOKING_ID
    JOIN 
        offered_activity oa ON b.OFFER_ID = oa.OFFER_ID
    WHERE 
        bp.COACHWALLET_ID = '$coachwallet_id'";
    $resultTrans = $conn->query($sqlTrans);
?>

<!DOCTYPE html>

    <head>
        <?php include('header2.php'); ?>
    </head>

    <style>
        body {
            padding-top: 70px; 
        }
        .container{
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            /* background-color: #64565617; */
            border: 2px solid #448aff; 
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .walletHeader, .tranHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .walletHeader h2, .tranHeader h2 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        .walletBox {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 15px; 
            padding: 20px; 
            width: 100%; 
            max-width: 950px; 
            margin: auto; 
            background-color: #64565617; 
            display: flex;
            flex-direction: row; 
            justify-content: space-between; 
            align-items: center; 
            box-sizing: border-box;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); 
        }
        .walletBox .walletAmount {
            font-size: 20px; 
            font-weight: bold; 
            color: #004085; 
        }
        .transBox {
            font-family: 'Arial', sans-serif;
        }
        .transactionCard {
            width: 90%;
            max-width: 900px;
            border: 1px solid #0056b3;
            border-radius: 10px;
            padding: 15px;
            background-color: #e3f2fd; /* Light blue background */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px auto;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .transactionCard:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .transactionDetails {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .transactionLabel {
            font-size: 12px;
            font-weight: bold;
            color: #0056b3; /* Blue color for the label */
            margin-bottom: 5px;
        }
        .offerName {
            font-size: 14px;
            color: #333;
            font-weight: bold;
        }
        .offerDet {
            font-size: 12px;
            color: #333;
        }
        .transactionAmount {
            font-size: 14px;
            font-weight: bold;
            color: #007bff; /* Blue color for positive amounts */
            text-align: right;
        }
        .transactionAmount.negative {
            color: #d32f2f; /* Red color for negative amounts */
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
            <div class="container">
                <div class="walletSect">
                    <div class="walletHeader">
                        <h2>My Wallet</h2>
                    </div>
                </div>

                <div class="walletBox" style="margin-top: 30px;">
                    <div class="walletAmount"><b>RM<?php echo $coach_amount; ?></b></div>
                </div>

                <div class="tranSect">
                    <div class="tranHeader">
                        <h2>Transactions</h2>
                    </div>
                </div>

                <div class="transBox" style="margin-top: 30px;">
                    <?php if ($resultTrans->num_rows > 0): ?>
                        <?php while ($rowTrans = $resultTrans->fetch_assoc()): ?>
                            <div class="transactionCard">
                                <div class="transactionDetails">
                                    <div class="transactionLabel">
                                        <?php 
                                            echo ucfirst($rowTrans['PAYMENT_TYPE']) . " for:"; 
                                        ?>
                                    </div>
                                    <div class="offerName"><?php echo htmlspecialchars($rowTrans['OFFER_NAME']); ?></div>
                                    <div class="offerDet">Booking made on:  <?php echo htmlspecialchars($rowTrans['BOOKING_PLACEMENTDATE']); ?></div>
                                    <div class="offerDet">Booking made by:  <?php echo htmlspecialchars($rowTrans['PARENT_USERNAME']); ?></div>
                                </div>
                                <div class="transactionAmount">
                                    <?php 
                                        $sign = $rowTrans['PAYMENT_TYPE'] === 'PAYMENT' ? '+' : '-'; 
                                        echo $sign . " RM" . number_format($rowTrans['PAYMENT_AMOUNT'], 2); 
                                    ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="transactionCard">
                            <p>No transactions found.</p>
                        </div>
                    <?php endif; ?>
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
