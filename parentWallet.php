<?php   
    session_start();
    include('connect.php'); 

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];
    $sql = "SELECT PARENTWALLET_ID, PARENT_AMOUNT FROM parent_wallet WHERE PARENT_USERNAME = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $parent_amount = $row['PARENT_AMOUNT']; 
        $parentwallet_id = $row['PARENTWALLET_ID']; 
    } else {
        echo "<script>alert('No data found. ');</script>";
        exit();
    }

    $sqlParentTrans = "SELECT TRANS_ID, TRANS_TYPE, TRANS_AMOUNT FROM parent_trans WHERE PARENTWALLET_ID = ?";
    $stmt = $conn->prepare($sqlParentTrans);
    $stmt->bind_param("s", $parentwallet_id);
    $stmt->execute();
    $resultParentTrans = $stmt->get_result();
    
    $transactions = [];
    
    if ($resultParentTrans->num_rows > 0) {
        while ($rowParentTrans = $resultParentTrans->fetch_assoc()) {
            if ($rowParentTrans['TRANS_TYPE'] === 'RELOAD') {
                // For RELOAD, use the old query
                $transactions[] = [
                    'TRANS_ID' => $rowParentTrans['TRANS_ID'],
                    'TRANS_TYPE' => $rowParentTrans['TRANS_TYPE'],
                    'TRANS_AMOUNT' => $rowParentTrans['TRANS_AMOUNT'],
                    'OFFER_NAME' => null, // No offer name for RELOAD
                ];
            } else {
                // For PAYMENT or REFUND, use the new query
                $sqlTransDetails = "
                    SELECT 
                        pr.BOOKING_ID,
                        b.OFFER_ID,
                        b.BOOKING_PLACEMENTDATE,
                        oa.OFFER_NAME
                    FROM 
                        payment_record pr
                    JOIN 
                        booking b ON pr.BOOKING_ID = b.BOOKING_ID
                    JOIN 
                        offered_activity oa ON b.OFFER_ID = oa.OFFER_ID
                    WHERE 
                        pr.TRANS_ID = ?";
                $stmtDetails = $conn->prepare($sqlTransDetails);
                $stmtDetails->bind_param("i", $rowParentTrans['TRANS_ID']);
                $stmtDetails->execute();
                $resultDetails = $stmtDetails->get_result();
    
                if ($resultDetails->num_rows > 0) {
                    $rowDetails = $resultDetails->fetch_assoc();
                    $transactions[] = [
                        'TRANS_ID' => $rowParentTrans['TRANS_ID'],
                        'TRANS_TYPE' => $rowParentTrans['TRANS_TYPE'],
                        'TRANS_AMOUNT' => $rowParentTrans['TRANS_AMOUNT'],
                        'OFFER_NAME' => $rowDetails['OFFER_NAME'],
                        'BOOKING_PLACEMENTDATE' => $rowDetails['BOOKING_PLACEMENTDATE'],
                    ];
                } else {
                    // Add the transaction without offer details
                    $transactions[] = [
                        'TRANS_ID' => $rowParentTrans['TRANS_ID'],
                        'TRANS_TYPE' => $rowParentTrans['TRANS_TYPE'],
                        'TRANS_AMOUNT' => $rowParentTrans['TRANS_AMOUNT'],
                        'OFFER_NAME' => null,
                    ];
                }
            }
        }
    }
    
?>

<!DOCTYPE html>

    <head>
        <?php include('header.php'); ?>
        <title>My Wallet</title>
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
        h2 {
            text-align: center;
            color: #333;
            font-size: 24px;
        }
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
            padding-top: 100px; 
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
                    <div class="walletAmount"><b>RM<?php echo $parent_amount; ?></b></div>
                    <button class="reloadButton" onclick="window.location.href='reloadWallet.php';">Reload</button>
                </div>

                <div class="tranSect">
                    <div class="tranHeader">
                        <h2>Transactions</h2>
                    </div>
                </div>

                <div class="transBox" style="margin-top: 30px;">
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="transactionCard">
                                <div class="transactionDetails">
                                    <?php if ($transaction['TRANS_TYPE'] === 'RELOAD'): ?>
                                        <div class="transactionLabel" style="font-size:14px;">
                                            <?php echo ucfirst($transaction['TRANS_TYPE']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="transactionLabel">
                                            <?php echo ucfirst($transaction['TRANS_TYPE']) . " for:"; ?>
                                        </div>
                                        <div class="offerName">
                                            <?php echo htmlspecialchars($transaction['OFFER_NAME']); ?>
                                        </div>
                                        <div class="offerDet">Booking made on:  <?php echo htmlspecialchars($transaction['BOOKING_PLACEMENTDATE']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="transactionAmount">
                                    <?php 
                                        $sign = $transaction['TRANS_TYPE'] === 'RELOAD' || $transaction['TRANS_TYPE'] === 'REFUND' ? '+' : '-'; 
                                        echo $sign . " RM" . number_format($transaction['TRANS_AMOUNT'], 2); 
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
