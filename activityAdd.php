<?php
    session_start();
    include('connect.php');
?>


<!DOCTYPE html>
    <head>
        <?php include('header2.php'); ?>
        <title>Add Activity</title>
    </head>

    <style>
        body {
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }
        .roundedBox {
            border-radius: 15px;
            padding: 30px;
            max-width: 1000px;
            width: 100%;
            margin: 20px auto;
            background-color:   #e3f2fd;
            text-align: center;
        }
        .image-upload-section {
            display: flex;
            justify-content: center;
            gap: 20px; /* Spacing between items */
            margin-bottom: 20px;
        }
        .image-upload {
            width: 30%; /* Each upload section takes 30% of the width */
            text-align: center;
        }
        .image-upload label {
            display: block;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            width: 100%;
            height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #888;
            font-size: 14px;
        }
        .image-upload label:hover {
            background-color: #f0f0f0;
            border-color: #888;
        }
        .image-upload img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .required-text {
            font-size: 12px;
            color: #555;
        }
        .straightBox {
            border-radius: 0px;
            padding: 20px;
            max-width: 1000px;
            width: 100%;
            margin: 20px auto;
            background-color:   #e3f2fd;
        }
        .form-group{
            padding-left:70px;
            padding-right:70px;
        }
        table.forTypeCat {
            width: 100%;                
            border-collapse: separate;  
            border-spacing: 5px;       
        }
        table.forTypeCat td {
            padding: 0;                 /* Remove padding inside the cells */
            width: 50%;                 /* Ensure both columns take equal space */
        }
        table.forTypeCat .form-group {
            margin-bottom: 10px;        /* Add space between label and input */
        }
        table.forTypeCat input.form-control {
            width: 100%;                /* Ensure inputs fill the available width */
        }
        table.forAge {
            width: 100%;                /* Ensure the table takes full width */
            border-collapse: separate;  /* Ensure the cells are separated */
            border-spacing: 5px;       /* Add space between table cells */
        }
        table.forAge td {
            padding: 0;                 /* Remove padding inside the cells */
            width: 50%;                 /* Ensure both columns take equal space */
        }
        table.forAge .form-group {
            margin-bottom: 10px;        /* Add space between label and input */
        }
        table.forAge input.form-control {
            width: 100%;                /* Ensure inputs fill the available width */
        }
        body .priceSect ,.genSect ,.seshSection{
            /* margin-top: 50px; */
            padding: 0 20px;
            max-width: 1130px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .priceHeader ,.genHeader ,.seshHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .priceHeader ,.genHeaders ,.seshHeader h2 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        h2{
            text-align: center;
            color: #333;
            font-size: 24px;
        }
        .acMinAge .acMaxAge {
            max-width: 100%;
            width: 100%;
            font-size: 1.5rem;
            padding: 10px;
            /* height: 50px; */
        }
        table.forPrice {
            width: 900px;
            border-collapse: separate;
            border-spacing: 5px;
            table-layout: fixed; /* Ensures equal column widths */
        }
        table.forPrice td {
            padding: 0;
            width: auto; /* Let the table handle equal sizing */
        }
        table.forPrice .form-group {
            margin-bottom: 10px;
        }
        table.forPrice input.form-control {
            width: 100%;
        }
        .addSesh{
            text-align: right;
            border: none;
            background-color: inherit;
            padding: 0.05px 2px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
        }
        .addSesh:hover{color: rgb(31, 109, 210);}
        .forSession {
            margin-top: 30px;
            width: 100%;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed; /* Ensures equal column width */
        }
        .forSession th, .forSession td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            font-size: 14px;
            width: 25%; 
        }
        .forSession th:nth-child(4), .forSession td:nth-child(4) {
            width: 15%; 
        }
        .forSession th {
            background-color: #f6f6f6;
            font-weight: bold;
        }
        .forSession select,.forSession input[type="time"], .forSession input[type="number"] {
            width: 90%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }
        .deleteSesh {
            display: block; /* Makes the button behave like a block element */
            margin: 10px auto 0; /* Centers the button horizontally and adds spacing */
            padding: 5px 10px;
            font-size: 14px;
            background-color:transparent; /* Red */
            color: #f44336;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        .deleteSesh:hover {
            background-color: #d32f2f; /* Darker red */
            color:white;
        }
        .addActivity {
            margin: 20px auto; 
            display: block;
            width: 200px;  
            background: linear-gradient(135deg, #448aff, #005ecb);
            color: white;  
            border: none;
            padding: 10px 15px; 
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px; 
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15); 
            transition: all 0.3s ease-in-out; 
            text-align: center; 
        }
        .addActivity:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); 
            transform: translateY(-3px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); 
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
            padding-top: 50px;
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
        function previewImage(event, inputId) {
            var output = document.getElementById('preview_' + inputId);
            var file = event.target.files[0];
            
            if (file) {
                var reader = new FileReader();
                reader.onload = function() {
                    output.src = reader.result;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>


    <body>
        <div class="main">
            <form name="activityInfo" method="POST" action="activityInsert.php" enctype="multipart/form-data" >
                <!-- for pics -->
                <div class="roundedBox">
                    <div class="image-upload-section">
                        <div class="image-upload">
                            <label for="acPic1">
                                <img id="preview_acPic1" src="acPic1.png" alt="acPic1" style="width: 100%; height: 100%; object-fit: contain;">
                            </label>
                            <input type="file" id="acPic1" name="acPic1" accept="image/*" onchange="previewImage(event, 'acPic1')">
                        </div>

                        <div class="image-upload">
                            <label for="acPic2">
                                <img id="preview_acPic2" src="acPic2.png" alt="acPic2" style="width: 100%; height: 100%; object-fit: contain;">
                            </label>
                            <input type="file" id="acPic2" name="acPic2" accept="image/*" onchange="previewImage(event, 'acPic2')">
                        </div>

                        <div class="image-upload">
                            <label for="acPic3">
                                <img id="preview_acPic3" src="acPic3.png" alt="acPic3" style="width: 100%; height: 100%; object-fit: contain;">
                            </label>
                            <input type="file" id="acPic3" name="acPic3" accept="image/*" onchange="previewImage(event, 'acPic3')">
                        </div>
                    </div>
                    <p class="required-text">*required to upload 3 pictures</p>
                </div>

                <div class="straightBox">
                    <div class="genSect">
                        <div class="genHeader">
                            <h2> <b>General Details</b></h2>
                        </div>
                    </div>

                    <table class="forTypeCat" style="margin-top:30px;">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <!-- Activity Type Dropdown -->
                                    <label for="activityType">Activity Type</label>
                                    <select class="form-control" id="activityType" name="activityType" onchange="updateCategories()" required>
                                        <option value="">-- Select Activity Type --</option>
                                        <option value="SPORTS">SPORTS</option>
                                        <option value="PERFORMING ARTS">PERFORMING ARTS</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <!-- Activity Category Dropdown -->
                                    <label for="activityCategory">Category</label>
                                    <select class="form-control" id="activityCategory" name="activityCategory" required>
                                        <option value="">-- Select Category --</option>
                                        
                                    </select>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="form-group">
                        <label for="acName">Activity Name</label>
                        <input type="text" class="form-control" id="acName" name="acName" required>
                    </div>

                    <div class="form-group">
                        <label for="acDesc">Activity Description</label>
                        <textarea type="text" class="form-control" id="acDesc" name="acDesc" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="acState">State</label>
                        <select style="max-width:400px;" class="form-control" id="acState" name="acState" required>
                            <option value="">-- Select State --</option>
                            <option value="PERAK">PERAK</option>
                            <option value="KUALA LUMPUR">KUALA LUMPUR</option>
                            <option value="SELANGOR">SELANGOR</option>
                            <option value="MELAKA">MELAKA</option>
                            <option value="JOHOR">JOHOR</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="acLocation">Location</label>
                        <input type="text" class="form-control" id="acLocation" name="acLocation" required>
                    </div>

                    <table class="forAge">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <label for="acMinAge">Minimum Age</label>
                                    <input type="number" class="acMinAge form-control text-center" id="age-value" name="acMinAge" value="3" min="3" max="18" required>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="acMaxAge">Maximum Age</label>
                                    <input type="number" class="acMaxAge form-control text-center" id="age-value" name="acMaxAge" value="3" min="3" max="18" required>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- price section line -->
                    <div class="priceSect">
                        <div class="priceHeader">
                            <h2> <b>Price Details</b></h2>
                        </div>
                    </div>

                    <table class="forPrice" style="margin-top: 20px">
                        <tr>
                            <td>
                                <div class="form-group">
                                    <label for="acTryPrice">Trial Class</label>
                                    <input type="number" class="acTryPrice form-control text-center" id="acTryPrice" name="acTryPrice" step="0.01" required>
                                    <p class="required-text">*Price per one class</p>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="acMonthPrice">One Month Package</label>
                                    <input type="number" class="acMonthPrice form-control text-center" id="acMonthPrice" name="acMonthPrice" step="0.01" required>
                                    <p class="required-text">*Price per month/4 classes</p>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <label for="acMonthPrice">One Year Package</label>
                                    <input type="number" class="acMonthPrice form-control text-center" id="acYearPrice" name="acYearPrice" step="0.01" required>
                                    <p class="required-text">*Price per year/48 classes</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="seshSection">
                        <div class="seshHeader">
                            <h2> Session Details</h2>
                            <button type="button" class="addSesh" onclick="addSession()">Add Session</button>
                        </div>
                    </div>
                    
                    <div id="sessionContainer">
                        <!-- Template for sessions -->
                        <div class="sessionTemplate sessionBlock" style="display: none;">
                            <table class="forSession">
                                <tr>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Pax</th>
                                    <th>Session For</th>
                                </tr>
                                <tr>
                                    <td>
                                        <select name="sessions[day][]">
                                            <option value="">-- Select Day --</option>
                                            <option value="Monday">Monday</option>
                                            <option value="Tuesday">Tuesday</option>
                                            <option value="Wednesday">Wednesday</option>
                                            <option value="Thursday">Thursday</option>
                                            <option value="Friday">Friday</option>
                                            <option value="Saturday">Saturday</option>
                                            <option value="Sunday">Sunday</option>
                                        </select>
                                    </td>
                                    <td><input type="time" name="sessions[start_time][]"></td>
                                    <td><input type="time" name="sessions[end_time][]" ></td>
                                    <td><input type="number" name="sessions[pax][]" min="1" ></td>
                                    <td>
                                        <select name="sessions[session_type][]">
                                            <option value="">-- Select Session Type --</option>
                                            <option value="Trial Class">Trial Class</option>
                                            <option value="One Month Package">One Month Package</option>
                                            <option value="One Year Package">One Year Package</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <button type="button" class="deleteSesh" onclick="deleteSession(this)">Delete Session</button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <!-- <button type="submit" class="addActivity">Add Activity</button> -->
                    <button type="submit" class="addActivity" onclick="validatePictures(event)">Add Activity</button>
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

    <script>

        function updateCategories() {
            var activityType = document.getElementById("activityType").value;
            var categoryDropdown = document.getElementById("activityCategory");
            categoryDropdown.innerHTML = '<option value="">-- Select Category --</option>';
            if (activityType === "SPORTS") {
                var categories = ["TENNIS","HOCKEY", "SWIMMING",];
            } else if (activityType === "PERFORMING ARTS") {
                var categories = ["MUSICAL THEATRE","DANCE","VOCAL & SINGING"];
            } else {
                var categories = [];
            }
            categories.forEach(function(category) {
                var option = document.createElement("option");
                option.value = category;
                option.text = category;
                categoryDropdown.appendChild(option);
            });
        }

        function addSession() {
            const container = document.getElementById("sessionContainer");
            const template = document.querySelector(".sessionTemplate");
            const newSession = template.cloneNode(true);
            newSession.style.display = "block";
            newSession.classList.remove("sessionTemplate");
            container.appendChild(newSession);
        }
        
        function deleteSession(button) {
            button.closest('.sessionBlock').remove();
        }

        function validatePictures(event) {
            // Get the file inputs
            const pic1 = document.getElementById('acPic1').files.length;
            const pic2 = document.getElementById('acPic2').files.length;
            const pic3 = document.getElementById('acPic3').files.length;

            // Check if all three images are uploaded
            if (pic1 === 0 || pic2 === 0 || pic3 === 0) {
                event.preventDefault();  // Prevent form submission
                alert('Please upload all 3 pictures.');
            }
        }
    </script>

</html>