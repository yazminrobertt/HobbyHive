<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- add for all nanti -->
        <title>Support FAQ</title> 
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
            padding-top: 60px; /* Adjust based on the height of your navbar */
        }
        /* Main content area */
        main {
            flex: 1; /* Ensures the main content area takes available space */
        }
        .classfooter {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            margin-top: auto;
        }
        .classfooter .footer-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f9f9f9;
            border-top: 1px solid #ddd;
        }
        .classfooter .footerLeft {
            display: flex;
            gap: 15px;
        }
        .classfooter .footerBtn {
            background: none;
            border: none;
            font-size: 12px;
            color: #333;
            cursor: pointer;
            transition: color 0.3s;
        }
        .classfooter .footerBtn:hover {
            color: #bbb;
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

        .faqContainer {
            width: 70%;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .faqHeader {
            text-align: center;
            font-size: 50px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .faqDesc {
            text-align: center;
            font-size: 16px;
            margin-bottom: 40px;
        }

        .faqSect {
            margin-bottom: 40px;
            padding: 20px;
            /* background-color: #fff; */
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        .faqSectTitle {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .faqSectTitle:after {
            content: "";
            display: block;
            height: 2px;
            background-color: #000000;
            margin-top: 5px;
        }

        .faqItem {
            padding: 15px;
            background-color: #ECF0F1;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 16px;
            position: relative;
        }

        .faqItem p {
            margin: 0;
        }

        .faqItemBtn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }

        .faqItemBtn:hover {
            transform: scale(1.2);
        }

        .faqItemCont {
            display: none;
            background-color: #D5DBDB;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .faqItem.expanded .faqItemCont {
            display: block;
        }

        .faqItem.expanded .faqItemBtn {
            transform: rotate(180deg);
        }
    </style>

<body>
    <?php 
        if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
            if ($_SESSION['accountType'] == 'parent') {
                include('header.php');  // Include parent header
            } elseif ($_SESSION['accountType'] == 'coach') {
                include('header2.php'); // Include coach header
            }
        } else {
            include('header.php');  // Default header when not logged in
        } 
    ?>

    <div class="faqContainer">
        <div class="faqHeader">
            SUPPORT FAQ
        </div>
        <div class="faqDesc">
            Below are some frequently asked questions. We hope this helps!
        </div>

        <!-- Parents Section -->
        <div class="faqSect">
            <div class="faqSectTitle">Parents</div>
            <div class="faqItem">
                <div class="faqQuestion">
                    What is a Junior Tennis Program?
                    <button class="faqItemBtn">&#9660;</button>
                </div>
                <div class="faqItemCont">
                    <p>Our Junior Tennis Program is designed to introduce children to the sport of tennis in a fun and engaging way. It focuses on developing basic skills, hand-eye coordination, and overall fitness.</p>
                </div>
            </div>

            <div class="faqItem">
                <div class="faqQuestion">
                    How do I book a class for my child?
                    <button class="faqItemBtn">&#9660;</button>
                </div>
                <div class="faqItemCont">
                    <p>Booking a class is simple! Just visit our booking page, select the program, and choose your preferred date and time. You'll receive a confirmation email after your booking is successful.</p>
                </div>
            </div>

            <div class="faqItem">
                <div class="faqQuestion">
                    Can I cancel or reschedule my class?
                    <button class="faqItemBtn">&#9660;</button>
                </div>
                <div class="faqItemCont">
                    <p>Yes, we offer flexibility for cancellations and rescheduling. You can do this through your account dashboard, but please note that certain conditions apply. Contact our support team for more details.</p>
                </div>
            </div>
        </div>

        <!-- Coaches Section -->
        <div class="faqSect">
            <div class="faqSectTitle">Coaches</div>
            <div class="faqItem">
                <div class="faqQuestion">
                    How do I become a coach in the Junior Tennis Program?
                    <button class="faqItemBtn">&#9660;</button>
                </div>
                <div class="faqItemCont">
                    <p>To become a coach, you must have experience in tennis and work well with children. You’ll also need to complete a background check and coaching certification. Visit our recruitment page for more details.</p>
                </div>
            </div>

            <div class="faqItem">
                <div class="faqQuestion">
                    What resources are available for coaches?
                    <button class="faqItemBtn">&#9660;</button>
                </div>
                <div class="faqItemCont">
                    <p>We offer a range of resources including training manuals, video tutorials, and coaching seminars. Coaches also get access to ongoing professional development opportunities throughout the year.</p>
                </div>
            </div>

            <div class="faqItem">
                <div class="faqQuestion">
                    How can I communicate with parents about the children’s progress?
                    <button class="faqItemBtn">&#9660;</button>
                </div>
                <div class="faqItemCont">
                    <p>Coaches are encouraged to communicate regularly with parents through email or the parent portal. Additionally, progress reports and one-on-one meetings can be arranged to discuss the child’s development.</p>
                </div>
            </div>
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
        document.querySelectorAll('.faqItemBtn').forEach(button => {
            button.addEventListener('click', function () {
                const faqItem = this.closest('.faqItem');
                faqItem.classList.toggle('expanded');
            });
        });
    </script>
</body>
</html>
