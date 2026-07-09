<?php
    session_start();
    include('connect.php');
    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include('header.php'); ?>
        <title>Reload Wallet</title>
    </head>

    <style>
        /* body {
            padding-top: 70px; 
        } */
        .container{
            max-width: 1000px;
            margin: auto;
            padding: 10px;
            /* background-color: #64565617; */
            border: 2px solid #448aff; 
            border-radius: 15px;
        }
        .reloadHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .reloadHeader h2{
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
        .paymentOption {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .paymentOption .optionCard {
            width: 40%; /* Slightly smaller width */
            padding: 12px; /* Slightly smaller padding */
            text-align: center;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 12px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            font-size: 12px; /* Set font size to 12px */
            font-weight: bold;
            color: #004085;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        .paymentOption .optionCard:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
        }

        .paymentOption .optionCard.active {
            background: linear-gradient(135deg, #82b1ff, #448aff);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0px 8px 25px rgba(0, 123, 255, 0.5);
        }

        .formSection {
            display: none;
            margin-bottom: 30px;
            padding: 16px; /* Smaller padding */
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .formGroup {
            margin-bottom: 15px;
        }

        .formGroup label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
            font-size: 12px; 
        }

        .formGroup input {
            width: 100%;
            padding: 10px; 
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 12px; 
            transition: border-color 0.2s;
        }

        .formGroup input:focus {
            border-color: #007bff;
            outline: none;
        }

        .cardInputs {
            display: flex;
            gap: 15px;
            justify-content: space-between;
        }

        .cardInputs .formGroup {
            flex: 1;
        }

        .amountSection {
            margin-top: 20px;
            padding: 16px; 
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .amountSection label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #555;
            font-size: 12px; 
        }

        .amountSection input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 12px; 
        }

        .submitButton {
            display: block;
            margin: 20px auto;
            padding: 8px 20px; 
            font-size: 12px;
            background: linear-gradient(135deg, #82b1ff, #448aff);
            border-color: #448aff;
            color: white;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0px 8px 25px rgba(0, 123, 255, 0.5);
        }

        .submitButton:hover {
            background-color: #007bff;
            border-color: #007bff;
            transform: scale(1.05);
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

    <script>
        function toggleForm(option) {
            document.getElementById('bankForm').style.display = option === 'bank' ? 'block' : 'none';
            document.getElementById('cardForm').style.display = option === 'card' ? 'block' : 'none';

            // Highlight the selected option
            document.querySelectorAll('.optionCard').forEach(card => card.classList.remove('active'));
            document.getElementById(option + 'Card').classList.add('active');
        }

        function validateForm() {
            const amountInput = document.getElementById('amount');
            const expiryDateInput = document.getElementById('expiryDate');
            const cardForm = document.getElementById('cardForm'); // The section for card form

            // Validate amount
            if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                alert('Please enter a valid amount to reload.');
                return false;
            }

            // Validate expiry date if the card form is visible
            if (cardForm.style.display !== 'none' && !validateExpiryDate(expiryDateInput.value)) {
                alert('Please enter a valid expiry date in the format MM/YY.');
                return false;
            }

            return true;
        }

        function validateExpiryDate(expiryDate) {
            const regex = /^(0[1-9]|1[0-2])\/\d{2}$/; // Match MM/YY format
            if (!regex.test(expiryDate)) {
                return false;
            }

            const [month, year] = expiryDate.split('/').map(Number);
            const currentDate = new Date();
            const currentMonth = currentDate.getMonth() + 1; // Months are 0-based
            const currentYear = currentDate.getFullYear() % 100; // Get last two digits of the year

            // Check if the expiry date is in the future
            if (year < currentYear || (year === currentYear && month < currentMonth)) {
                return false;
            }

            return true;
        }

        function formatCardNumber(input) {
            input.value = input.value
                .replace(/\D/g, '') // Remove all non-numeric characters
                .replace(/(\d{4})(?=\d)/g, '$1-') // Add a dash after every 4 digits
                .substring(0, 19); // Limit to 19 characters
        }
    </script>

<body>
    <div class="main">
        <div class="container">
            <div class="reloadSect">
                <div class="reloadHeader" style="margin-bottom:20px;">
                    <h2>Reload Wallet</h2>
                </div>
            </div>

            <div class="paymentOption">
                <div id="bankCard" class="optionCard" onclick="toggleForm('bank')">
                    Online Banking
                </div>
                <div id="cardCard" class="optionCard" onclick="toggleForm('card')">
                    Credit/Debit Card
                </div>
            </div>

            <div id="bankForm" class="formSection">
                <div class="formGroup">
                    <label for="bankName">Bank Name</label>
                    <input type="text" id="bankName" name="bankName" placeholder="Enter your bank name">
                </div>
                <div class="formGroup">
                    <label for="accountNumber">Account Number</label>
                    <input type="text" id="accountNumber" name="accountNumber" placeholder="Enter your account number">
                </div>
            </div>

            <div id="cardForm" class="formSection">
                <div class="formGroup">
                    <label for="cardNumber">Card Number</label>
                    <input type="text" id="cardNumber" name="cardNumber" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19" oninput="formatCardNumber(this)">
                </div>
                <div class="cardInputs">
                    <div class="formGroup">
                        <label for="expiryDate">Expiry Date</label>
                        <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="formGroup">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3">
                    </div>
                </div>
                <div class="formGroup">
                    <label for="cardHolderName">Cardholder Name</label>
                    <input type="text" id="cardHolderName" name="cardHolderName" placeholder="Enter cardholder name">
                </div>
            </div>

            <form method="POST" action="finalReload.php" onsubmit="return validateForm()">
                <div class="amountSection">
                    <label for="amount">Amount to Reload</label>
                    <input type="number" id="amount" name="amount" placeholder="Enter the amount" min="1">
                </div>

                <button type="submit" class="submitButton" >Reload</button>
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
