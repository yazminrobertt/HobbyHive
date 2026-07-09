<?php
    session_start();
    include('connect.php'); 

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    $sql = "SELECT * FROM parent WHERE PARENT_USERNAME = '$username'" ;
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $parent_username = $row['PARENT_USERNAME'] ;
        $parent_name = $row['PARENT_NAME'] ;
        $parent_phone = $row['PARENT_PHONE'] ;
        $parent_email = $row['PARENT_EMAIL'] ;
    } else {
        echo "<script>alert('No data found.');</script>";
        exit();
    }

    // Check if form is submitted to update data
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_phone = $_POST['phone'];
        $new_email = $_POST['email'];

        if (empty($new_phone) || empty($new_email)) {
            echo "<script>alert('Phone number and email cannot be empty.');</script>";
        } else {
            // Only update if the values have actually changed

            $update_sql = "UPDATE parent SET PARENT_PHONE = '$new_phone', PARENT_EMAIL = '$new_email' WHERE PARENT_USERNAME = '$username'";

            if ($conn->query($update_sql) === TRUE) {
                echo "<script>alert('Profile updated successfully.'); window.location.href = 'parentProfile.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error updating profile: " . $conn->error . "');</script>";
            }
        }
    }

?>

<!DOCTYPE html>

    <head>
        <?php include('header.php'); ?>
        <title>Edit Profile</title>
    </head>

    <style>
        html, body {
            height: 100%;
            margin-top: 0px;
            padding: 0px;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 150px;
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
        .roundedBox {
            margin-top: 10px;
            border-radius: 15px; 
            padding: 20px; 
            max-width: 600px; 
            margin: auto; 
            background-color: #ffffff; /* Clean white background */
            box-shadow: 0 4px 8px rgba(72, 161, 255, 0.3); /* Subtle blue shadow */
            color: #004085; /* Dark blue text color */
            border: 1px solid #ddd; /* Light border for definition */
        }
        .roundedBox .form-group {
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 15px; 
            align-items: center; 
        }
        .roundedBox .form-group label {
            font-size: 14px; /* Slightly larger font for readability */
            font-weight: bold; 
            color: #004085; 
            width: 150px; /* Fixed width for alignment */
            text-align: left; 
        }
        .roundedBox .form-control {
            font-size: 14px; /* Slightly larger input text */
            font-weight: normal; 
            padding: 10px; 
            background-color: #f9f9f9; /* Light gray background for inputs */
            border: 1px solid #ddd; /* Light border for inputs */
            border-radius: 5px; 
            color: #004085; 
            width: calc(100% - 170px); /* Adjusted width for spacing */
            margin-left: 15px; 
            text-align: left; 
        }
        .roundedBox .form-control:focus {
            border-color: #004ba0; /* Blue border on focus */
            box-shadow: 0 0 5px rgba(0, 75, 160, 0.3); /* Subtle blue glow */
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
        .editParent {
            margin-left:480px;
            background-color: rgb(55, 135, 227); /* Blue background */
            color: white; 
            border: none; 
            padding: 12px 24px; 
            font-size: 14px; /* Adjusted for readability */
            cursor: pointer; 
            border-radius: 5px; 
            text-align: center; 
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }
        .editParent:hover {
            background-color: #004ba0; /* Darker blue on hover */
        }
        .roundedBox .form-group.text-center {
            text-align: center; 
        }
    </style>

    <body>
        <div class="main">
            <div class="roundedBox">
                <form id="parentEdit" method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input  class="form-control" id="username" name="username" value="<?php echo $parent_username; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="Name">Name</label>
                        <input  class="form-control" id="Name" name="Name" value="<?php echo $parent_name; ?>" readonly>
                    </div>
                    <!-- Phone Number -->
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $parent_phone; ?>" >
                    </div>
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $parent_email; ?>" >
                    </div>
                    <!-- Save Button -->
                    <div class="form-group text-center" style="margin-top: 20px;">
                        <button class="editParent" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                    <button class="footerBtn">For Coaches</button>
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
