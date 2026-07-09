<?php
    session_start();
    include('connect.php');

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    // Check the current number of accreditations for the coach
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM coach_accreditation WHERE COACH_USERNAME = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $credit_count = $row['count'];
    $stmt->close();

    // Upload new accreditation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
        $credit_desc = $_POST['credit_desc'] ?? '';

        // Handle file upload
        if (!empty($_FILES['credit_pic']['tmp_name'])) {
            $credit_pic = file_get_contents($_FILES['credit_pic']['tmp_name']);
            $credit_pic = base64_encode($credit_pic); // Base64 encode the image for database storage
        } else {
            $credit_pic = null;
        }

        if (empty($credit_desc)) {
            echo "<script>alert('Accreditation description cannot be empty.');</script>";
        } elseif ($credit_pic === null) {
            echo "<script>alert('Please upload a certificate image.');</script>";
        } else {
            // Check if the coach has less than 6 accreditations
            if ($credit_count >= 6) {
                echo "<script>alert('You can only have a maximum of 6 accreditations.');</script>";
            } else {
                $stmt = $conn->prepare("INSERT INTO coach_accreditation (CREDIT_DESC, CREDIT_PIC, COACH_USERNAME) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $credit_desc, $credit_pic, $username);

                if ($stmt->execute()) {
                    echo "<script>alert('Accreditation uploaded successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to upload accreditation. Please try again.');</script>";
                }

                $stmt->close();
            }
        }
    }

    // Delete accreditation
    if (isset($_GET['delete'])) {
        $credit_id = intval($_GET['delete']);

        $stmt = $conn->prepare("DELETE FROM coach_accreditation WHERE CREDIT_ID = ? AND COACH_USERNAME = ?");
        $stmt->bind_param("is", $credit_id, $username);

        if ($stmt->execute()) {
            echo "<script>alert('Accreditation deleted successfully.'); window.location.href = 'manageCredit.php';</script>";
        } else {
            echo "<script>alert('Failed to delete accreditation. Please try again.');</script>";
        }

        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
        $edit_id = $_POST['edit_id'];
        $credit_desc = $_POST['credit_desc'] ?? '';
    
        // Handle file upload (optional)
        if (!empty($_FILES['credit_pic']['tmp_name'])) {
            $credit_pic = file_get_contents($_FILES['credit_pic']['tmp_name']);
            $credit_pic = base64_encode($credit_pic); // Base64 encode the image for database storage
        } else {
            $credit_pic = null; // No new file uploaded
        }
    
        if (empty($credit_desc)) {
            echo "<script>alert('Accreditation description cannot be empty.');</script>";
        } else {
            if ($credit_pic !== null) {
                // Update both description and picture
                $stmt = $conn->prepare("UPDATE coach_accreditation SET CREDIT_DESC = ?, CREDIT_PIC = ? WHERE CREDIT_ID = ? AND COACH_USERNAME = ?");
                $stmt->bind_param("ssis", $credit_desc, $credit_pic, $edit_id, $username);
            } else {
                // Update only description
                $stmt = $conn->prepare("UPDATE coach_accreditation SET CREDIT_DESC = ? WHERE CREDIT_ID = ? AND COACH_USERNAME = ?");
                $stmt->bind_param("sis", $credit_desc, $edit_id, $username);
            }
    
            if ($stmt->execute()) {
                echo "<script>alert('Accreditation updated successfully.'); window.location.href = 'manageCredit.php';</script>";
            } else {
                echo "<script>alert('Failed to update accreditation. Please try again.');</script>";
            }
    
            $stmt->close();
        }
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("header2.php"); ?>
        <title>Manage Accreditations</title>
    </head>

    <style>
        /* body {
            padding-top: 70px; 
            font-family: Arial, sans-serif;
        } */
        .container {
            /* width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px; */
            /* background: #ffffff; */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            background-color:   #e3f2fd;
            border-radius: 15px;
        }
        .creditSection {
            margin-bottom: 20px;
        }
        .creditHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 2px solid black;
            padding-bottom: 5px;
        }
        .creditHeader h2 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }
        .add-button {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
        }
        .form-section {
            margin: 10px 0;
            padding: 10px;
            background-color:rgb(179, 205, 225);
            border-radius: 8px;
        }
        .form-section input[type="text"], 
        .form-section input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-section button {
            background: linear-gradient(135deg, #448aff, #005ecb);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .note {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        #credit-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .credit {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color:rgb(179, 205, 225);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .credit-image {
            width: 100px;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }
        .credit-text {
            flex-grow: 1;
            font-size: 14px;
            margin: 0;
            margin-right: 15px; /* Add spacing between text and buttons */
            word-wrap: break-word;
            text-align: left; /* Align the text more naturally */
        }
        .credit-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .credit-buttons button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
        .credit-buttons button.delete {
            background-color: #dc3545;
        }
        #preview-container {
            text-align: center;
            margin-top: 10px;
        }
        #previewImage {
            border: 1px solid #ccc;
            border-radius: 5px;
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
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
                <div class="creditSection">
                    <div class="creditHeader">
                        <h2>Accreditations</h2>
                        <button class="add-button" onclick="addAccreditation()">+</button>
                    </div>
                </div>

                <div class="form-section" id="credit-form" style="display: none;">
                    <form method="POST" enctype="multipart/form-data" action="">
                        <input type="hidden" name="edit_id" id="edit-id" value="">
                        <input type="text" name="credit_desc" id="credit-input" placeholder="Enter accreditation">
                        <label for="credit-pic">Upload Picture:</label>
                        <input type="file" name="credit_pic" id="credit-pic" accept="image/*">
                        <div id="preview-container">
                            <img id="previewImage" src="" alt="Image Preview" style="display: none; max-width: 100%; height: auto; margin-top: 10px; border-radius: 5px;">
                        </div>
                        <p class="note">*Maximum of only 6 accreditations allowed</p>
                        <button type="submit" name="upload" id="upload-button">Upload</button>
                        <button type="submit" id="edit-button" name="edit" style="display: none;">Save Changes</button>
                    </form>
                </div>

                <div id="credit-list">
                    <?php
                    $stmt = $conn->prepare("SELECT CREDIT_ID, CREDIT_DESC, CREDIT_PIC FROM coach_accreditation WHERE COACH_USERNAME = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="credit" data-id="' . $row['CREDIT_ID'] . '">';
                        echo '<img src="data:image/jpeg;base64,' . $row['CREDIT_PIC'] . '" alt="Certificate Image" class="credit-image">';
                        echo '<p class="credit-text">' . htmlspecialchars($row['CREDIT_DESC']) . '</p>';
                        echo '<div class="credit-buttons">';
                        echo '<button onclick="editAccreditation(this)">Edit</button>';
                        echo '<button class="delete" onclick="deleteAccreditation(this)">Delete</button>';
                        echo '</div>';
                        echo '</div>';
                    }

                    $stmt->close();
                    ?>
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

    <script>
        function addAccreditation() {
            const form = document.getElementById('credit-form');
            document.getElementById('credit-input').value = '';
            document.getElementById('edit-id').value = '';
            document.getElementById('edit-button').style.display = 'none';
            document.getElementById('upload-button').style.display = 'inline-block';
            form.style.display = 'block';
        }

        function editAccreditation(button) {
            const form = document.getElementById('credit-form');
            const text = button.parentElement.parentElement.querySelector('.credit-text').innerText;
            const creditId = button.parentElement.parentElement.getAttribute('data-id');
            document.getElementById('credit-input').value = text;
            document.getElementById('edit-id').value = creditId;

            const imageSrc = button.parentElement.parentElement.querySelector('.credit-image').src;
            const previewImage = document.getElementById('previewImage');
            previewImage.src = imageSrc;
            previewImage.style.display = 'block'; // Display the image in the preview

            document.getElementById('edit-button').style.display = 'inline-block';
            document.getElementById('upload-button').style.display = 'none'; // Hide the upload button
            form.style.display = 'block';
        }

        function deleteAccreditation(button) {
            const creditId = button.parentElement.parentElement.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this accreditation?')) {
                window.location.href = `?delete=${creditId}`;
            }
        }

        document.getElementById('credit-pic').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const previewImage = document.getElementById('previewImage');

            if (file) {
                const reader = new FileReader();
                // Load the image file
                reader.onload = function(e) {
                    previewImage.src = e.target.result; 
                    previewImage.style.display = 'block'; 
                };
                reader.readAsDataURL(file); 
            } else {
                previewImage.style.display = 'none'; 
            }
        });
    </script>
</html>
