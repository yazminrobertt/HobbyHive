<?php 
    session_start();
    include('connect.php');

    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    // Get the logged-in parent username from the session
    $parent_username = $_SESSION['username'];

    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize input data to prevent XSS
        $child_name = mysqli_real_escape_string($conn, $_POST['childName']);
        $child_gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $child_age = $_POST['age']; // Get the age from the form
    
        // Check if fields are empty
        if (empty($child_name) || empty($child_gender) || empty($child_age)) {
            echo "<script>alert('All fields are required.');</script>";
        } else {
            // Prepare and execute the insert statement
            $sql = "INSERT INTO child (CHILD_NAME, CHILD_GENDER, CHILD_AGE, PARENT_USERNAME) 
                    VALUES (?, ?, ?, ?)";
    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssis", $child_name, $child_gender, $child_age, $parent_username);
            $stmt->execute();
    
            if ($stmt->affected_rows > 0) {
                // Redirect back to the parent's profile page after successful insertion
                echo "<script>alert('Child added successfully.'); window.location.href = 'parentProfile.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error adding child.');</script>";
            }
        }
    }
    
?>

<!DOCTYPE html>
    <head>
        <?php include('header.php'); ?>
    </head>

    <style>
        body {
            padding-top: 200px;
        }
        .roundedBox {
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
        .childAdd {
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
        .chilAdd:hover {
            background-color: #004ba0; /* Darker blue on hover */
        }
        .roundedBox .form-group.text-center {
            text-align: center; 
        }
        .gender-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .gender-button {
            align-items: left;
            width:100px;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 10px;
            background-color: #f9f9f9; 
            color: #004085; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s ease; 
        }
        .gender-button:hover {
            background-color: #004ba0; 
            color: white; 
        }
        .gender-button:active {
            background-color: #004ba0;
            color: white;
        }
        .gender-button.selected {
            background-color: #004ba0; 
            color: white; 
        }
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
    </style>

    <body>
        <div class="main">
            <div class="roundedBox">
                <form id="addChild" method="POST" action="">
                    <div class="form-group">
                        <label for="text">Name</label>
                        <input type="text" class="form-control" id="childName" name="childName">
                    </div>
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" class="form-control" id="age" name="age" value="3" min="3" max="18">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <div class="gender-buttons">
                            <div class="gender-button" data-gender="F">F</div>
                            <div class="gender-button" data-gender="M">M</div>
                        </div>
                        <!-- Hidden input to store the selected gender -->
                        <input type="hidden" id="gender" name="gender" value="">
                    </div>
                    <div style="display: flex; justify-content: center; margin-top: 20px;">
                        <button class="childAdd" type="submit">Add</button>
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


        <script>
            document.querySelectorAll('.gender-button').forEach(button => {
                button.addEventListener('click', function() {
                    // Remove 'selected' class from all gender buttons
                    document.querySelectorAll('.gender-button').forEach(b => b.classList.remove('selected'));

                    // Add 'selected' class to the clicked button
                    this.classList.add('selected');

                    // Update the hidden input with the selected gender value
                    document.getElementById('gender').value = this.getAttribute('data-gender');
                });
            });
        </script>
    </body>

</html>
