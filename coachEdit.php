<?php 
    session_start();
    include('connect.php'); 

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    // Fetch coach data
    $sql = "SELECT * FROM coach WHERE COACH_USERNAME = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $coach_username = $row['COACH_USERNAME'];
        $coach_name = $row['COACH_NAME'];
        $coach_phone = $row['COACH_PHONE'];
        $coach_email = $row['COACH_EMAIL'];
        $coach_age = $row['COACH_AGE'];
        $coach_gender = $row['COACH_GENDER'];
        $coach_about = $row['COACH_ABOUT'];
        $coach_pic = !empty($row['COACH_PROPIC']) ? "data:image/jpeg;base64," . base64_encode($row['COACH_PROPIC']) : 'add.png';
    } else {
        echo "<script>alert('No data found.');</script>";
        exit();
    }
    
    $conn->close();
?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let profilePic = document.getElementById("profile-pic");
        let inputFile = document.getElementById("upload-pic");

        inputFile.addEventListener("change", function () {
            if (inputFile.files && inputFile.files[0]) {
                profilePic.src = URL.createObjectURL(inputFile.files[0]);
            }
        });
    });
</script>

<!DOCTYPE html>
<html>
    <head>
        <?php include("header2.php"); ?>
        <title>Edit Profile</title>
    </head>

    <style>
        /* body {
            padding-top: 105px;
        } */
        .roundedBox {
            border-radius: 15px;
            padding: 15px; 
            max-width: 1000px; 
            width: 100%;
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 4px 8px rgba(72, 161, 255, 0.3);
            color: #004085; 
            display: flex;
            align-items: flex-start;
            justify-content: left;
        }
        .forPic {
            margin-top: 100px;
            width: 300px;
            height: 300px;
            display: flex;
            flex-direction: column;
            align-items: flex-start; 
            justify-content: center;
            position: relative;
        }
        .image-container {
            position: relative;
            width: 250px; 
            height: 250px; 
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            overflow: hidden;
            background-color: #ddd;
            align-self: center; 
            margin-right: 10px;
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); 
        } 
       
        .form-container {
            margin-left: 20px;
            width:900px;
            font-size: 12px; /* Adjusted for compactness */
            display: flex;
            flex-direction: column;
            gap: 10px; /* Space between form groups */
        }

        .form-group {
            display: flex;
            justify-content: space-between; /* Aligns label and input horizontally */
            margin-bottom: 10px;
            align-items: center;
        }

        .form-group label {
            font-size: 12px;
            font-weight: bold;
            color: #004085;
            width: 150px;
            text-align: left;
        }

        .form-group input,
        .form-group textarea {
            font-size: 12px;
            font-weight: normal;
            padding: 10px;
            background-color: transparent; /* Keeps background clean */
            border: 1px solid #ddd; /* Subtle border for clarity */
            border-radius: 5px;
            color: #004085;
            width: calc(100% - 170px); /* Adjust width based on label */
            margin-left: 15px;
            text-align: left;
        }

        textarea {
            resize: none; /* Prevent resizing */
        }

        table.forAgeGender {
            width: 100%;
            border-collapse: collapse; /* Removes gaps between borders */
            margin-top: 5px; /* Reduced margin */
        }

        table.forAgeGender th, 
        table.forAgeGender td {
            padding: 5px 10px; /* Compact padding */
            text-align: left;
        }

        table.forAgeGender input.form-control {
            width: 100%;
        }

        .editCoach {
            margin-left: auto; /* Aligns button to the right */
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

        .editCoach:hover {
            background-color: #004ba0;
        }

        @media (max-width: 768px) {
            .roundedBox {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
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
        .edit-icon {
            display: inline-block;
            padding: 10px 15px; 
            background-color:  #003366; ;
            color: #fff;
            border-radius: 5px; 
            cursor: pointer; 
            text-align: center; 
            font-size: 14px;
            font-weight: bold; 
            transition: background-color 0.3s ease; 
        }

        .edit-icon:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }

    </style>

    <body>
    <div class="main">   
        <form method="POST" action="coachManEdit.php" enctype="multipart/form-data">
            <div class="roundedBox">
                <div class="forPic">
                    <div class="image-container">
                        <img id="profile-pic" src="<?php echo $coach_pic; ?>" alt="Coach Image">
                    </div>
                    <label for="upload-pic" class="edit-icon">
                        <label>Edit Pic</label>
                        <!-- <img src="edit-icon.png" alt="Edit Icon" width="16"> -->
                    </label>
                    <input type="file" id="upload-pic" name="profile_pic" style="display: none;" accept="image/*">
                </div>
                <div class="form-container">
                    <div class="form-group">
                        <label for="coUsername">Username</label>
                        <input type="text" class="form-control" id="coUsername" name="coUsername" value="<?php echo $coach_username; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="coName">Name</label>
                        <input type="text" class="form-control" id="coName" name="coName" value="<?php echo $coach_name; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="coEmail">Email</label>
                        <input type="email" class="form-control" id="coEmail" name="coEmail" value="<?php echo $coach_email; ?>">
                    </div>
                    <div class="form-group">
                        <label for="coPhone">Phone Number</label>
                        <input type="tel" class="form-control" id="coPhone" name="coPhone" value="<?php echo $coach_phone; ?>">
                    </div>
                    <div class="form-group">
                        <label for="coAbout">About Me</label>
                        <textarea class="form-control" id="coAbout" name="coAbout"><?php echo $coach_about; ?></textarea>
                    </div>
                    <!-- <table class="forAgeGender">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <label for="coAge">Age</label>
                                    <input type="number" class="form-control text-center" id="coAge" name="coAge" value="<?php echo $coach_age; ?>" min="20" max="85">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="coGender">Gender</label>
                                    <input type="text" class="form-control" id="coGender" name="coGender" value="<?php echo $coach_gender; ?>" readonly>
                                </div>
                            </td>
                        </tr>
                    </table> -->
                    <table class="forAgeGender">
                        <tr>
                            <th> <div class="form-group">
                                    <label for="coAge">Age</label>
                                    
                                </div></th>
                            <th> <div class="form-group">
                                    <label for="coGender">Gender</label>
                                </div></th>
                        </tr>
                        <tr>

                            <td>
                                <div class="form-group">
                                    <input type="number" class="form-control text-center" id="coAge" name="coAge" value="<?php echo $coach_age; ?>" min="20" max="85">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                <input type="text" class="form-control" id="coGender" name="coGender" value="<?php echo $coach_gender; ?>" readonly>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <button type="submit" class="editCoach">Update Profile</button>
                </div>
            </div>
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
</html>
