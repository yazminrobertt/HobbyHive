<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hobby Hive</title>
        <?php include('header.php'); ?>
    </head>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            /* padding-top: 60px; Adjust based on the height of your navbar */
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
        .hobbyHive {
            width: 100%;
            padding: 0;
        }
        .banner {
            width: 100%;
            /* background-color: #CCED6F; */
            background-image: url('canva-4home.png');
            background-size: cover;
            background-position: center;
            text-align: center;
            padding: 100px 20px;
            border-radius: 10px;
        }     
        .site-name {
            font-size: 90px;
            /* font-size: 4rem; */
            font-weight: bold;
            margin: 0;
            /* color: #333; */
            color:#003366 ;
        }
        .tagline {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 5px 30px;
            color: #003366;
        }
        .intro {
            font-size: 1.2rem;
            color: #003366;
            margin: 10px 0;
            line-height: 1.5;
        }
        .aboutBtn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 1.2rem;
            color: #003366; /* Dark blue text */
            background-color:#ffdc4c; /* Golden yellow */
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .aboutBtn:hover {
            background-color: #ffdc4c; /* Lighter yellow on hover */
        }
        .btnType {
            display: flex;
            justify-content: space-between; 
            gap: 20px;
            margin-top: 50px;
            margin-bottom: 50px;
            padding: 0 70px;
            box-sizing: border-box; 
        }
        .acBtn {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 45%; /* Equal width for both buttons */
            padding: 30px 0; /* Adjust padding for larger buttons */
            background-color: #FFECA1 ;
            color: black;
            font-size: 2rem; /* Bigger font size */
            font-weight: bold;
            text-decoration: none; /* Remove underline */
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
            height: 150px; /* Equal height for both buttons */
        }
        .acBtn:hover {
            background-color: #FFECA1;
            color: #333;
            text-decoration: none;
            transform: translateY(-5px); /* Lift the button slightly */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add subtle shadow for depth */
        }
        .coBanner {
            width: 90%; /* Ensures it doesn't touch the sides of the screen */
            max-width: 1200px; /* Limits the maximum width of the banner */
            background-color: #A1C3F6 ;
            text-align: center;
            padding: 50px 20px; /* Adjusted padding for better spacing */
            margin: 0 auto; /* Centers the banner */
            border-radius: 15px; /* Increased border radius for rounded corners */
            margin-bottom: 20px;
        }
        .coTitle {
            font-size: 60px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        .coTag {
            font-size: 1.8rem;
            font-weight: normal;
            margin: 5px 0;
            color: #555;
        }
        .coIntro {
            font-size: 1.2rem;
            color: #666;
            margin: 10px auto; /* 'auto' on left and right for centering */
            line-height: 1.5;
            max-width: 500px;
            text-align: center; /* Optional: Aligns the text in the center */
        }
        .coBtn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 1.2rem;
            color: white;
            background-color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .coBtn:hover {
            background-color: #555;
            text-decoration: none;
        }
    </style>
    <body>
        <div class="main">
            <div class="hobbyHive">
                <div class="banner">
                    <h1 class="site-name">HOBBYHIVE</h1>
                    <h3 class="tagline">Unleash Your Potential at Hobby Hive!</h3>
                    <h4 class="intro">Explore fun and enriching after-school activities that spark imagination, foster friendships, and develop new skills. Join us today!</h4>
                    <a href="aboutUs.php" class="aboutBtn">Learn More</a>
                </div>
            </div>

            <div class="btnType">
                <a href="sportsDisplay.php" class="acBtn sports">Sports</a>
                <a href="artsDisplay.php" class="acBtn  arts">Performing Arts</a>
            </div>

            <div class="coDir">
                <div class="coBanner">
                    <h1 class="coTitle">Calling All Coaches!</h1>
                    <h3 class="coTag">Join Us and Inspire the Next Generation</h3>
                    <h4 class="coIntro">If you're passionate about sports or performing arts and want to make a difference, we would love to have you join our team! Help young minds develop their skills and foster a love for their hobbies.</h4>
                    <a href="forCoach.php" class="coBtn">Become a Coach</a>
                </div>
            </div>

        </div>

        <!-- Footer Section -->
        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                    <button class="footerBtn"onclick="window.location.href='forCoach.php';">For Coaches</button>
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
