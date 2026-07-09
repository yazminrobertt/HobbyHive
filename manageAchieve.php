<?php
    session_start();
    include('connect.php'); 

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    // Check the current number of achievements for the coach
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM coach_achievement WHERE COACH_USERNAME = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $achievement_count = $row['count'];
    $stmt->close();

    // Upload new achievement
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
        $achievement_desc = $_POST['achievement'] ?? '';

        if (empty($achievement_desc)) {
            echo "<script>alert('Achievement description cannot be empty.');</script>";
        } else {
            // Check if the coach has less than 5 achievements
            if ($achievement_count >= 6) {
                echo "<script>alert('You can only have a maximum of 5 achievements.');</script>";
            } else {
                $stmt = $conn->prepare("INSERT INTO coach_achievement (ACHIEVE_DESC, COACH_USERNAME) VALUES (?, ?)");
                $stmt->bind_param("ss", $achievement_desc, $username);

                if ($stmt->execute()) {
                    echo "<script>alert('Achievement uploaded successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to upload achievement. Please try again.');</script>";
                }

                $stmt->close();
            }
        }
    }

    // Delete achievement
    if (isset($_GET['delete'])) {
        $achieve_id = intval($_GET['delete']);

        $stmt = $conn->prepare("DELETE FROM coach_achievement WHERE ACHIEVE_ID = ? AND COACH_USERNAME = ?");
        $stmt->bind_param("is", $achieve_id, $username);

        if ($stmt->execute()) {
            echo "<script>alert('Achievement deleted successfully.'); window.location.href = 'manageAchieve.php';</script>";
        } else {
            echo "<script>alert('Failed to delete achievement. Please try again.');</script>";
        }

        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
        $achieve_id = intval($_POST['edit_id']);
        $achievement_desc = $_POST['achievement'] ?? '';

        if (isset($achievement_desc) && !empty($achievement_desc)) {
            echo "<script>console.log('Posted Achievement Description: " . htmlspecialchars($achievement_desc) . "');</script>";
            echo "<script>console.log('Edit ID: $achieve_id');</script>";

            $stmt = $conn->prepare("UPDATE coach_achievement SET ACHIEVE_DESC = ? WHERE ACHIEVE_ID = ? AND COACH_USERNAME = ?");
            $stmt->bind_param("sis", $achievement_desc, $achieve_id, $username);

            if ($stmt->execute()) {
                echo "<script>alert('Achievement updated successfully.'); window.location.href = 'manageAchieve.php';</script>";
            } else {
                echo "<script>console.log('SQL Error: " . $stmt->error . "');</script>";
                echo "<script>alert('Failed to update achievement. Please try again.');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Achievement description cannot be empty.');</script>";
        }
    }
?>


<!DOCTYPE html>
<html>
    <head>
        <?php include("header2.php"); ?>
        <title>Manage Achievements</title>
    </head>

    <style>
        body {
            padding-top: 70px; 
            font-family: Arial, sans-serif;
        }
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
        .form-section {
            width:900px;
            margin: 20px auto;
            padding: 20px;
            background-color:rgb(179, 205, 225);
            border-radius: 8px;
        }
        .form-section input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-section button {
            background: linear-gradient(135deg, #448aff, #005ecb);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .achievement {
            width:900px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px auto;
            padding: 15px;
            background-color:rgb(179, 205, 225);
            border-radius: 8px;
        }
        .achievement-text {
            width:700px;
            margin: 0;
            font-size: 14px;
            word-wrap: break-word; 
            word-break: break-word; 
        }
        .achievement-buttons button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-left: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .achievement-buttons button.delete {
            background-color: #dc3545;
        }
        .note {
            font-size: 12px;
            color: #666;
        }
        body .achieveSection{
            margin-top: 20px;
            padding: 0 20px;
            max-width: 1130px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .achieveHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .achieveHeader h2 {
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
            margin-bottom: 5px;;
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
                <div class="achieveSection">
                    <div class="achieveHeader">
                        <h2>Achievements</h2>
                        <button class="add-button" onclick="addAchievement()">+</button>
                    </div>
                </div>

                <div class="form-section" id="achievement-form" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="edit_id" id="edit-id" value="">
                        <input type="text" name="achievement" id="achievement-input" placeholder="Enter achievement">
                        <p class="note">*Maximum of only 6 achievements allowed</p>
                        <button type="submit" name="upload" id="upload-button">Upload</button>
                        <button type="submit" id="edit-button" name="edit" style="display: none;">Save Changes</button>
                    </form>
                </div>


                <div id="achievement-list">
                    <?php
                    $stmt = $conn->prepare("SELECT ACHIEVE_ID, ACHIEVE_DESC FROM coach_achievement WHERE COACH_USERNAME = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="achievement" data-id="' . $row['ACHIEVE_ID'] . '">';
                        echo '<p class="achievement-text">' . htmlspecialchars($row['ACHIEVE_DESC']) . '</p>';
                        echo '<div class="achievement-buttons">';
                        echo '<button onclick="editAchievement(this)">Edit</button>';
                        echo '<button class="delete" onclick="deleteAchievement(this)">Delete</button>';
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
        function addAchievement() {
            const form = document.getElementById('achievement-form');
            document.getElementById('achievement-input').value = '';
            document.getElementById('edit-id').value = '';
            document.getElementById('edit-button').style.display = 'none';
            document.getElementById('upload-button').style.display = 'inline-block';
            form.style.display = 'block';
        }

        function editAchievement(button) {
            const form = document.getElementById('achievement-form');
            const text = button.parentElement.parentElement.querySelector('.achievement-text').innerText;
            const achievementId = button.parentElement.parentElement.getAttribute('data-id');
            document.getElementById('achievement-input').value = text;
            document.getElementById('edit-id').value = achievementId;
            document.getElementById('edit-button').style.display = 'inline-block';
            document.getElementById('upload-button').style.display = 'none'; // Hide the upload button
            form.style.display = 'block';
        }

        function deleteAchievement(button) {
            const achievementId = button.parentElement.parentElement.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this achievement?')) {
                window.location.href = `?delete=${achievementId}`;
            }
        }
    </script>
</html>
