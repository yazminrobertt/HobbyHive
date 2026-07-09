<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- add for all nanti -->
        <title>For Coaches</title> 
    </head>

    <style>
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 60px;
        }
        main {
            flex: 1;
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
            width: 90%; 
            max-width: 1200px;
            background-color: #FFECA1 ;
            text-align: center;
            padding: 100px 20px; 
            margin: 0 auto;
            border-radius: 15px; 
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
            margin: 10px auto; 
            line-height: 1.5;
            max-width: 700px;
            text-align: center ; 
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
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px; 
        }
        .row {
            display: flex;
            justify-content: center;
            gap: 40px; 
            margin-bottom: 60px;
            flex-wrap: wrap; 
        }
        .card {
            width: 350px; 
            background: linear-gradient(135deg, #E3F2FD, #BBDEFB); 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-size: 1.2rem;
            color: #2C3E50;
            border-radius: 20px;
            padding: 30px; 
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); 
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-10px); 
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25); 
            background: linear-gradient(135deg, #D6ECFA, #A9D4F8); 
        }
        .cardTitle {
            font-size: 2rem; 
            font-weight: bold;
            margin-bottom: 15px;
            color: #004085; 
        }

        .cardDesc {
            font-size: 14px;
            line-height: 1.8; 
            color: #34495E; 
        }
    </style>

    <body>
        <?php 
            include("header.php");
        ?>

        <div class="main">
            <div class="whatHobby">
                <div class="whatBanner">
                <h1 class="whatTitle">Why Join Us?</h1>
                <h4 class="whatIntro">Expand your reach, fill your sessions, simplify bookings, and connect with parents and students through our trusted platform!</h4>
                </div>
            </div>

            <div class="missionVision">
                <h2 class="sectionTitle">BENEFITS</h2>
                <div class="row">
                    <div class="card">
                        <div class="cardTitle">INCREASED VISIBILITY</div>
                        <div class="cardDesc">Connect with more parents and students effortlessly.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">SIMPLIFIED BOOKINGS</div>
                        <div class="cardDesc">Manage your schedule with ease using our platform.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">MORE ENROLLMENTS</div>
                        <div class="cardDesc">Fill your sessions and grow your coaching activities.</div>
                    </div>
                </div>

                <h2 class="sectionTitle">HOW TO JOIN</h2>
                <div class="row">
                    <div class="card">
                        <div class="cardTitle">1. SIGN UP</div>
                        <div class="cardDesc">Create your coach profile easily and start reaching more students today.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">2. ADD ACTIVITIES</div>
                        <div class="cardDesc">Simply add your programs with dates, times, and details to get noticed.</div>
                    </div>
                    <div class="card">
                        <div class="cardTitle">3. START BOOKING</div>
                        <div class="cardDesc">Sit back, let us handle the bookings, and watch your sessions fill!</div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>

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
