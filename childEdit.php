<?php 
    session_start();
    include('connect.php');

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    // Fetch child data from the database based on the child's ID passed in the URL
    if (isset($_GET['child_id'])) {
        $child_id = $_GET['child_id'];

        $sql = "SELECT * FROM child WHERE CHILD_ID = ? AND PARENT_USERNAME = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $child_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $child = $result->fetch_assoc();
            $child_name = $child['CHILD_NAME'];
            $child_gender = $child['CHILD_GENDER'];
            $child_age = $child['CHILD_AGE'];
        } else {
            echo "<script>alert('Child not found.'); window.location.href = 'parentProfile.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid request.'); window.location.href = 'parentProfile.php';</script>";
        exit();
    }

    // If the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize the age input
        $child_age = $_POST['age']; // Get the new age from the form
    
        // Prepare and execute the update statement
        $sql = "UPDATE child SET CHILD_AGE = ? WHERE CHILD_ID = ? AND PARENT_USERNAME = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $child_age, $child_id, $username);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            // Redirect back to the parent's profile page after successful update
            echo "<script>alert('Child age updated successfully.'); window.location.href = 'parentProfile.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error updating child age.');</script>";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include('header.php'); ?>
        <title>Edit Child</title>
    </head>
    <style>
        body {
            padding-top: 150px; 
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
        table.forAgeGender {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
        }
        table.forAgeGender td {
            padding: 0;
            width: 50%;
        }
        .roundedBox .form-group {
            margin-bottom: 15px;
        }
        .roundedBox .form-group label {
            font-size: 12px;
            font-weight: bold;
            color: #004085;
            width: 120px;
            text-align: left;
            margin-right: 10px;
        }
        .roundedBox .form-control {
            font-size: 12px;
            font-weight: normal;
            padding: 10px;
            background-color: transparent;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #004085;
            width: 100%;
            margin-left: 0;
            text-align: left;
        }
        table.forAgeGender tr:first-child td {
            width: 100%;
        }
        .childAdd {
            background-color: rgb(55, 135, 227);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
            width: auto;
            transition: background-color 0.3s ease;
        }
        .childAdd:hover {
            background-color: #004ba0;
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
        <form id="editChild" method="POST" action="">
            <table class="forAgeGender">
                <!-- Name field takes up full row -->
                <tr>
                    <td colspan="2">
                        <div class="form-group">
                            <label for="childName">Name</label>
                            <input type="text" class="form-control" id="childName" name="childName" value="<?php echo $child_name; ?>" readonly>
                        </div>
                    </td>
                </tr>
                <!-- Age and Gender fields in the same row -->
                <tr>
                    <td>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" class="form-control" id="age" name="age" value="<?php echo $child_age; ?>" min="3" max="18">
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <input type="text" class="form-control" id="gender" name="gender" value="<?php echo $child_gender; ?>" readonly>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="display: flex; justify-content: center; margin-top: 20px;">
                <button class="childAdd" type="submit">Update</button>
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
