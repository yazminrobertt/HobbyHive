<!-- header.php -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="iconhobby.png" type="image/png">

    <style>
        .navbar-default {
            background-color: #A1C3F6;
            border-color: #A1C3F6; 
        }
        .navbar-default .navbar-nav > li > a {
            color: #003366;
        }
        .navbar-default .navbar-brand {
            color: #003366;
            padding: 0;  
            display: flex;
            align-items: center;  
            height: 50px; 
        }
        .navbar-default .navbar-brand img {
            margin: 30px 20px;
            max-height: 100%;  
            max-width: 100%;  
        }
        .navbar-default .navbar-nav > li > a:hover {
            background-color: rgb(74, 148, 223); 
            color: #003366; 
        }
        .navbar-default .navbar-nav > li.active > a {
            text-decoration: underline;
            background-color: rgb(74, 148, 223); 
            color: #003366; 
        }
        .navbar-default .navbar-nav > .dropdown.active > a {
            background-color: rgb(74, 148, 223); 
            color: #003366; 
            border-bottom: 2px solid #FFD700;
        }
        .navbar-default .navbar-nav > .dropdown.active > .dropdown-menu {
            background-color: rgb(74, 148, 223);
        }
        .dropdown-menu {
            background-color: #A1C3F6;
        }
        .navbar-default .navbar-nav > .dropdown.active > a,
        .navbar-default .navbar-nav > .dropdown:hover > a {
            background-color: rgb(74, 148, 223);
            color: #003366; 
        }
        .navbar-default .navbar-nav > .dropdown-menu {
            background-color: #A1C3F6; 
            border: none; 
        }
        .navbar-default .navbar-nav > .dropdown-menu > li > a:hover {
            background-color: rgb(74, 148, 223); 
            color: #003366; 
        }
        .navbar-default .navbar-nav > .dropdown-menu > li.active > a {
            background-color: rgb(74, 148, 223); 
            color: #003366; 
        }
        .navbar-default .navbar-nav > .dropdown-menu > li.active > a {
            text-decoration: underline; 
        }
    </style>

    
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>                        
                </button>
                <a class="navbar-brand">
                    <img src="logo.png" alt="Logo" style="max-height: 70px; margin-bottom:20px;">
                </a>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav navbar-right">
                    <?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']): ?>
                        <?php if ($_SESSION['accountType'] == 'parent'): ?>
                            <!-- Parent Navigation -->
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="glyphicon glyphicon-user"></span> 
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="parentProfile.php">Profile</a></li>
                                    <li><a href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php elseif ($_SESSION['accountType'] == 'coach'): ?>
                            <!-- Coach Navigation -->
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="glyphicon glyphicon-user"></span> 
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="coachProfile.php">Profile</a></li>
                                    <li><a href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.html">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</head>
