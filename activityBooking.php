<?php  
    session_start();
    include("connect.php");

    // Get the activity ID from the URL
    $offerId = isset($_GET['offer_id']) ? $_GET['offer_id'] : '';

    // Fetch package details
    $packageQuery = "SELECT PRICING_TYPE, PRICE FROM offered_pricing WHERE OFFER_ID = ?";
    $stmt = $conn->prepare($packageQuery);
    $stmt->bind_param("s", $offerId);
    $stmt->execute();
    $packageResult = $stmt->get_result();

    $packages = [];
    while ($row = $packageResult->fetch_assoc()) {
        $packages[] = $row;
    }

    // Fetch OT_DAY and OT_TYPE for the selected offer_id
    $daysQuery = "SELECT OT_DAY, OT_TYPE, OT_ID, OT_STARTTIME, OT_ENDTIME, OT_PAX, OT_SLOTSLEFT FROM offered_time WHERE OFFER_ID = ? AND IS_REMOVED=0";
    $stmt = $conn->prepare($daysQuery);
    $stmt->bind_param("s", $offerId);
    $stmt->execute();
    $daysResult = $stmt->get_result();

    $sessions = [];
    while ($row = $daysResult->fetch_assoc()) {
        $sessions[] = $row;
    }

    $availableDays = [];
    $otType = ''; // To store the OT_TYPE for the selected package type

    // Store available days and the OT_TYPE
    foreach ($sessions as $row) {
        $availableDays[$row['OT_TYPE']][] = $row['OT_DAY'];
        if (!$otType) {
            $otType = $row['OT_TYPE']; // Get the OT_TYPE from the first row (or adjust logic as needed)
        }
    }

    // Fetch OT_TYPE from the selected package
    $selectedPackageType = isset($_POST['package_type']) ? $_POST['package_type'] : '';

    // Fetch logged-in parent's username
    $parentUsername = $_SESSION['username']; // Assuming parent's username is stored in session

    // Get the children's data for the logged-in parent
    $childQuery = "SELECT child_id, child_name, child_age FROM child WHERE parent_username = ?";
    $stmt = $conn->prepare($childQuery);
    $stmt->bind_param("s", $parentUsername);
    $stmt->execute();
    $childResult = $stmt->get_result();

    $children = [];
    while ($row = $childResult->fetch_assoc()) {
        $children[] = $row;
    }

    // Fetch the min and max age for the selected activity
    $activityQuery = "SELECT OFFER_MINAGE, OFFER_MAXAGE FROM offered_activity WHERE offer_id = ?";
    $stmt = $conn->prepare($activityQuery);
    $stmt->bind_param("s", $offerId);
    $stmt->execute();
    $activityResult = $stmt->get_result();
    $activity = $activityResult->fetch_assoc();
    $minAge = $activity['OFFER_MINAGE'];
    $maxAge = $activity['OFFER_MAXAGE'];
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include('header.php'); ?>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <title>Booking Page</title>
    </head>

    <style>
        body {
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }
        .title {
            max-width: 1000px;
            margin:auto;
            font-weight: bold;
            padding-bottom: 10px;
            color: #004085;
        }
        .container {
            border-radius: 2px;
            border: 1px solid #82b1ff; /* Adds a solid black border */
            max-width: 1000px;
            margin:auto;
            margin-bottom:30px;
            padding: 20px;
        }
        body .packSect, .calSect, .seshSect, .childSect{
            margin-top: 10px;
            max-width: 1130px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .packHeader ,.calHeader, .seshHeader, .childHeader{
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .packHeader, .calHeade, .seshHeader, .childHeader h4 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        .packType {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap; 
            gap: 20px; 
            justify-content: center; 
        }
        .packCard {
            width: 300px;
            padding: 15px;
            text-align: center;
            background-color: #e3f2fd; 
            border-radius: 8px; 
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1); 
            transition: all 0.3s ease-in-out; 
        }
        .packCard:hover {
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2); 
            transform: translateY(-5px); 
        }
        .packCard h4 {
            margin: 8px 0;
            font-size: 16px;
            color: #004085;
            font-weight: bold; 
        }
        .packCard p {
            margin: 4px 0;
            font-size: 12px;
            color: #555;
        }
        .packCard .price {
            margin-top: 8px;
            font-size: 20px;
            font-weight: bold;
            color: #448aff; 
        }
        /* .availDate {
            margin-top: 20px;
            text-align: center; 
        } */
        .availDate {
            margin-top: 20px;
            text-align: center; 
            display: block;
            width: 100%; 
            max-width: 700px; 
            margin-left: auto;
            margin-right: auto;
        }
        #datepicker {
            font-size: 18px; 
            padding: 10px; 
            width: 50%;
            margin: 0 auto; 
            display: block; 
            border-radius: 5px; 
            border: 1px solid #ccc; 
        }
        #datepicker::placeholder {
            color: #888; 
            font-style: italic; 
        }
        .forSesh, .forChild {
            margin-top: 30px;
            width: 100%; 
            max-width: 800px; 
            margin-left: auto; 
            margin-right: auto; 
            border-collapse: collapse;
            font-size: 14px; 
            text-align: center;
            table-layout: fixed; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
            border: 2px solid #448aff; 
            box-sizing: border-box;
        }
        .forSesh th, .forChild th {
            background-color: #e3f2fd; 
            background-color: #82b1ff;
            font-weight: bold;
            text-transform: uppercase;
            color: #004085; 
            text-align: center;
            padding: 8px;
            width: auto;
        }
        .forSesh td, .forChild td {
            text-align: center;
            border: 1px solid #448aff; 
            background-color: #e3f2fd; 
            padding: 8px;
            color: #004085;
        }
        .forSesh tbody tr, .forChild tbody tr {
            border-bottom: 1px solid #448aff;
        }
        .paxSect {
            margin-left: 20px;
        }
        input[type="radio"], input[type="checkbox"] {
            appearance: none; 
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 12px; 
            height: 12px;
            border-radius: 50%; 
            border: 2px solid #448aff; 
            background-color: #fff; 
            cursor: pointer; 
            transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="radio"]:checked, input[type="checkbox"]:checked {
            background-color: #448aff; 
            border-color: #448aff; 
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3); 
        }
        input[type="radio"]:disabled, input[type="checkbox"]:disabled {
            background-color: #e3f2fd; 
            border-color: #b3d9ff; 
            cursor: not-allowed; 
        }
        input[type="radio"]:disabled:checked, input[type="checkbox"]:disabled:checked {
            background-color: #b3d9ff; 
            border-color: #b3d9ff; 
            box-shadow: none;
        }
        .btnBookNow {
            margin: 20px auto; 
            display: block;
            width: 800px;  
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
        .btnBookNow:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); 
            transform: translateY(-3px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); 
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            /* padding-top: 150px; */
            /* padding-top: 60px; Adjust based on the height of your navbar */
        }
        .main {
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
        .note {
        width: 700px;
        margin-top: 10px;
        font-size: 10px;
        color: #555;
        }
        .note p {
            margin: 0;
            line-height: 1.5;
        }
    </style>

    <body>
        <div class="main">
            <div class="title">
                <h1><strong>Select Booking Options:</strong></h1>
            </div>

            <div class="container">
                <form action="activityConfirm.php" method="POST">
                    <div class="packSect">
                        <div class="packHeader">
                            <h4><b>Package Available</b></h4>
                        </div>
                    </div>

                    <div class="packType">
                        <?php foreach ($packages as $package) { ?>
                            <div class="packCard">
                                <input
                                    type="radio"
                                    id="package_<?php echo htmlspecialchars($package['PRICING_TYPE']); ?>"
                                    name="package_type"
                                    value="<?php echo htmlspecialchars($package['PRICING_TYPE']); ?>"
                                    required
                                >
                                <label for="package_<?php echo htmlspecialchars($package['PRICING_TYPE']); ?>">
                                    <h4><?php echo htmlspecialchars($package['PRICING_TYPE']); ?></h4>
                                    <p>
                                        <?php
                                        if ($package['PRICING_TYPE'] === 'TRIAL CLASS') {
                                            echo '*Total of 1 class only';
                                        } elseif($package['PRICING_TYPE'] === 'ONE MONTH PACKAGE'){
                                            echo '*Total of 4 class only';
                                        }else {
                                            echo '*Total of 48 classes';
                                        }
                                        ?>
                                    </p>
                                    <div class="price">RM <?php echo htmlspecialchars($package['PRICE']); ?></div>
                                </label>
                                <input type="hidden" name="package_price_<?php echo htmlspecialchars($package['PRICING_TYPE']); ?>" value="<?php echo htmlspecialchars($package['PRICE']); ?>">
                            </div>
                        <?php } ?>
                        <div class="note">
                            <p><strong>Note:</strong></p>
                            <ul>
                                <li>Trial and 1-month packages can be canceled only before the activity starts, while 1-year packages can be canceled at any time.</li>
                                <li>Trial classes and 1-month packages are fully refundable.</li>
                                <li>One-year packages are 100% refundable before the activity starts, 75% refundable within 91 days, 50% within 182 days, and 25% within 273 days. No refunds are provided after 273 days.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="seshSect">
                        <div class="seshHeader">
                            <h4><b>Sessions Available</b></h4>
                        </div>
                    </div>

                    <div class="availSesh">
                        <table class="forSesh" border="1" style="width: 100%; text-align: left;">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Max Pax</th>
                                    <th>Slots Left</th>
                                </tr>
                            </thead>
                            <tbody id="sessionTableBody">
                                <?php foreach ($sessions as $session) { ?>
                                    <tr class="sessionRow" data-ot-type="<?php echo htmlspecialchars($session['OT_TYPE']); ?>" <?php echo ($session['OT_SLOTSLEFT'] <= 0) ? 'class="disabled"' : ''; ?>>
                                        <td>
                                            <input
                                                type="radio"
                                                name="session_id"
                                                value="<?php echo htmlspecialchars($session['OT_ID']); ?>"
                                                required
                                                <?php echo ($session['OT_SLOTSLEFT'] <= 0) ? 'disabled' : ''; ?>
                                            >
                                        </td>
                                        <td><?php echo htmlspecialchars($session['OT_DAY']); ?></td>
                                        <td><?php echo htmlspecialchars($session['OT_STARTTIME']); ?></td>
                                        <td><?php echo htmlspecialchars($session['OT_ENDTIME']); ?></td>
                                        <td><?php echo htmlspecialchars($session['OT_PAX']); ?></td>
                                        <td><?php echo htmlspecialchars($session['OT_SLOTSLEFT']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="childSect">
                        <div class="childHeader">
                            <h4><b>Select Participants</b></h4>
                        </div>
                    </div>

                    <div class="selChild">
                        <table class="forChild">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Child Name</th>
                                    <th>Age</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($children as $child) { ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                name="selected_children[]"
                                                value="<?php echo htmlspecialchars($child['child_id']); ?>"
                                                <?php
                                                    // Check if the child's age is within the range
                                                    if ($child['child_age'] < $minAge || $child['child_age'] > $maxAge) {
                                                        echo 'disabled'; // Disable if outside the age range
                                                    }
                                                ?>
                                            >
                                        </td>
                                        <td><?php echo htmlspecialchars($child['child_name']); ?></td>
                                        <td><?php echo htmlspecialchars($child['child_age']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="paxSect">
                            <h4><b>Pax: </b><span id="paxCount">0</span></h4>
                        </div>
                    </div>

                    <div class="calSect">
                        <div class="calHeader">
                            <h4><b>Available Dates</b></h4>
                        </div>
                    </div>

                    <div class="availDate">
                        <input type="text" id="datepicker" name="start_date" placeholder="Select a date" required>
                        <div class="note">
                            <p><strong>Note:</strong> If your preferred dates aren’t available, try again next week—slots open weekly.</p>
                        </div>
                    </div>
                    <input type="hidden" id="availableDays" value='<?php echo json_encode($availableDays); ?>'>

                    <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($offerId); ?>">
                    
                    <button class="btnBookNow" type="submit">Confirm</button>
                </form>

            </div>
        </div>

    
        <script>
            $(document).ready(function () {
                $('input[name="package_type"]').on('change', function () {
                    const selectedPackageType = $(this).val();
                    $('.sessionRow').hide();
                    $(`.sessionRow[data-ot-type='${selectedPackageType}']`).show();
                });

                $('input[name="package_type"]').first().trigger('change');

                const availableDays = JSON.parse($('#availableDays').val());

                const dayMapping = {
                    "SUNDAY": 0,
                    "MONDAY": 1,
                    "TUESDAY": 2,
                    "WEDNESDAY": 3,
                    "THURSDAY": 4,
                    "FRIDAY": 5,
                    "SATURDAY": 6
                };

                function getClosestDates(allowedDays) {
                    const today = new Date();
                    const closestDates = [];

                    const addedDays = new Set();

                    for (let i = 1; i < 14; i++) {
                        const tempDate = new Date();
                        tempDate.setDate(today.getDate() + i);

                        const dayOfWeek = tempDate.getDay();

                        if (allowedDays.includes(dayOfWeek) && !addedDays.has(dayOfWeek)) {
                            closestDates.push(new Date(tempDate));
                            addedDays.add(dayOfWeek);
                        }
                    }

                    return closestDates;
                }

                function updateAvailableDays(otDay) {
                    const normalizedDay = otDay.toUpperCase().trim();
                    const allowedDay = dayMapping[normalizedDay];

                    if (allowedDay === undefined) {
                        console.error(`Invalid day: ${otDay}. Please check the OT_DAY value.`);
                        return; // bila no day, tapi mesti ada day
                    }

                    console.log(`Updating datepicker to allow only ${normalizedDay} (day index: ${allowedDay})`);

                    const closestDates = getClosestDates([allowedDay]);

                    $('#datepicker').datepicker("destroy");
                    $('#datepicker').datepicker({
                        dateFormat: "dd/mm/yy",
                        minDate: 0,
                        beforeShowDay: function (date) {
                            const isValidDate = closestDates.some(d =>
                                d.getFullYear() === date.getFullYear() &&
                                d.getMonth() === date.getMonth() &&
                                d.getDate() === date.getDate()
                            );
                            return [isValidDate];
                        }
                    });
                }

                $('#sessionTableBody').on('click', 'input[name="session_id"]', function () {
                    const selectedSessionRow = $(this).closest('tr');
                    const otDay = selectedSessionRow.find('td:nth-child(2)').text().trim();

                    //udates dases on pilih OT_DAY
                    updateAvailableDays(otDay);
                });

                $('input[type="radio"][name="session_id"]').each(function () {
                    const slotsLeft = $(this).closest('tr').find('td').eq(5).text();
                    if (slotsLeft <= 0) {
                        $(this).prop('disabled', true);
                        $(this).closest('tr').addClass('disabled');
                    }
                });

                const defaultSelectedSession = $('input[name="session_id"]:checked');
                if (defaultSelectedSession.length) {
                    const defaultSelectedSessionRow = defaultSelectedSession.closest('tr');
                    const otDay = defaultSelectedSessionRow.find('td:nth-child(2)').text().trim();
                    updateAvailableDays(otDay);
                }
                $('input[name="selected_children[]"]').on('change', function () {
                    const selectedChildren = $('input[name="selected_children[]"]:checked').length;
                    $('#paxCount').text(selectedChildren);
                });

                $('#submitBtn').prop('disabled', true);

                //
                $('input[name="selected_children[]"], input[name="session_id"]').on('change', function () {
                    const selectedChildren = $('input[name="selected_children[]"]:checked').length;
                    $('#paxCount').text(selectedChildren);

                    const selectedSessionId = $('input[name="session_id"]:checked').val(); 

                    if (selectedSessionId) {
                        const selectedSessionRow = $('tr').filter(function () {
                            return $(this).find('input[name="session_id"][value="' + selectedSessionId + '"]').length;
                        });

                        const slotsLeft = parseInt(selectedSessionRow.find('td').eq(5).text());  

                        if (selectedChildren > slotsLeft) {
                            //btn submit x boelh
                            $('#submitBtn').prop('disabled', true);
                            alert("Sorry, you have selected more participants than the available slots for this session.");
                        } else {
                            //btn submit bole
                            $('#submitBtn').prop('disabled', false);
                        }
                    }
                });

                $('#sessionTableBody').on('click', 'input[name="session_id"]', function () {
                    const selectedSessionRow = $(this).closest('tr');
                    const slotsLeft = parseInt(selectedSessionRow.find('td').eq(5).text()); 
                    const selectedChildren = $('input[name="selected_children[]"]:checked').length; 

                    // If selected children exceed available slots, disable submit button
                    if (selectedChildren > slotsLeft) {
                        $('#submitBtn').prop('disabled', true);
                        alert("Sorry, you have selected more participants than the available slots for this session.");
                    } else {
                        $('#submitBtn').prop('disabled', false);
                    }
                });

                // Form submit event
                $('form').on('submit', function (e) {
                    const selectedChildren = $('input[name="selected_children[]"]:checked').length;
                    const selectedSessionId = $('input[name="session_id"]:checked').val(); 

                    if (selectedSessionId) {
                        const selectedSessionRow = $('tr').filter(function () {
                            return $(this).find('input[name="session_id"][value="' + selectedSessionId + '"]').length;
                        });

                        const slotsLeft = parseInt(selectedSessionRow.find('td').eq(5).text());  

                        if (selectedChildren > slotsLeft) {
                            e.preventDefault(); // Prevent form submission
                            alert("Sorry, you have selected more participants than the available slots for this session.");
                        }
                    }
                });
            });
        </script>

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