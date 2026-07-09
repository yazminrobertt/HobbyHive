<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- add for all nanti -->
        <title>About Us</title> 
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
            padding-top: 60px; 
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
        .whatBanner {
            width: 90%; /* Ensures it doesn't touch the sides of the screen */
            max-width: 1200px; /* Limits the maximum width of the banner */
            background-color: #FFECA1 ;
            text-align: center;
            padding: 100px 20px; /* Adjusted padding for better spacing */
            margin: 0 auto; /* Centers the banner */
            border-radius: 15px; /* Increased border radius for rounded corners */
            margin-bottom: 20px;
        }
        .whatTitle {
            font-size: 60px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        .whatIntro {
            font-size: 13px;
            color: #666;
            padding-top: 20px;
            margin: 10px auto; /* 'auto' on left and right for centering */
            line-height: 1.5;
            max-width: 700px;
            text-align: center ; /* Optional: Aligns the text in the center */
        }
        .missionVision {
            text-align: center;
            margin-top: 20px;
            padding: 50px auto;
        }
        .sectionTitle {
            font-size:30px; /* Increased size for emphasis */
            font-weight: bold;
            margin-top: 50px;
            margin-bottom: 20px;
            color: #1E2A38; /* Slightly darker for contrast */
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px; /* Subtle spacing for a polished look */
        }
        .row {
            display: flex;
            justify-content: center;
            gap: 40px; /* Increased spacing for larger cards */
            margin-bottom: 60px;
            flex-wrap: wrap; /* Wraps cards on smaller screens */
        }
        .card {
            width: 350px; /* Larger width for a bolder presence */
            background: linear-gradient(135deg, #E3F2FD, #BBDEFB); /* Brighter gradient colors */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-size: 1.2rem; /* Larger font size for better readability */
            color: #2C3E50;
            border-radius: 20px; /* Larger border radius for a modern look */
            padding: 30px; /* Increased padding for spacious content */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* Stronger shadow for depth */
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-10px); /* More pronounced lift effect */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25); /* Enhanced shadow for hover */
            background: linear-gradient(135deg, #D6ECFA, #A9D4F8); /* Brighter hover gradient */
        }
        .cardTitle {
            font-size: 2rem; /* Larger font size for title */
            font-weight: bold;
            margin-bottom: 15px;
            color: #004085; /* Stronger, brighter color for titles */
        }

        .cardDesc {
            font-size: 14px; /* Increased size for readability */
            line-height: 1.8; /* Comfortable spacing for larger text */
            color: #34495E; /* Slightly darker shade for contrast */
        }
    </style>

    <body>
        <?php 
            include("header.php");
        ?>

        <div class="main">
            <div class="whatHobby">
                <div class="whatBanner">
                    <h1 class="whatTitle">What is HobbyHive</h1>
                    <h4 class="whatIntro">Welcome to Hobby Hive, where passion meets purpose! At Hobby Hive, we believe in creating a vibrant community where individuals can explore their interests, discover new talents, and connect with like-minded people. Our platform offers a wide range of activities designed to inspire creativity, foster friendships, and encourage personal growth.</h4>
                </div>
            </div>

            <div class="missionVision">
                <h2 class="sectionTitle">Mission</h2>
                <div class="row">
                    <div class="card">
                        <div class="cardTitle">PARENTS</div>
                        <div class="cardDesc">Empowering parents with easy access to activities that foster their child's growth.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">COACHES</div>
                        <div class="cardDesc">To support coaches in sharing their expertise by connecting them with eager learners.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">CHILDREN</div>
                        <div class="cardDesc">To inspire children to explore, learn, and thrive through enriching extracurricular experiences.</div>
                    </div>
                </div>

                <h2 class="sectionTitle">Vision</h2>
                <div class="row">
                    <div class="card">
                        <div class="cardTitle">PARENTS</div>
                        <div class="cardDesc">To create a trusted platform where parents can discover the best activities for their child's unique interests.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">COACHES</div>
                        <div class="cardDesc">To be the go-to network for coaches, enabling them to grow their impact and passion for teaching.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">CHILDREN</div>
                        <div class="cardDesc">To nurture a generation of confident, creative, and curious young minds through diverse opportunities.
                        </div>
                    </div>
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
