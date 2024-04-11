<?php
    if (!isset($context)) {
        header('Location: index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="lib/jquery-3.7.1.min.js"></script>
    <script src="js/common.js"></script>
    <?php
        echo match ($context) {
            'login' => '<script src="js/login.js"></script>',
            'index' => '<script src="js/index.js"></script>',
            'admin' => '<script src="js/admin.js"></script>',
            'student' => '<script src="js/student.js"></script>',
            default => ''
        };
    ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg" href="img/svg/murica-square-small.svg">
    <title>MURICA<?php
            echo match ($context) {
                'login' => ' | Login',
                'admin' => ' | Administrator',
                'student' => ' | Student',
                default => ''
            };
        ?>
    </title>
</head>
<body>
<?php
    switch ($context) {
        case 'index': ?>
<nav>
    <span id="username"></span>
    <button id="logoutButton">Logout</button>
    <a href="admin.php">Admin</a>
</nav>
<?php
        break;
        case 'admin':?>
<nav>
    <ul id="navbar" class="navbar">
        <li><img id="navbar-logo" src="img/svg/murica-square-small.svg" alt="logo"></li>
        <li><span id="navbar-subjects">Subjects</span></li>
        <li><span id="navbar-programmes">Programmes</span></li>
        <li><span id="navbar-users">Users</span></li>
        <li><span id="navbar-rooms">Rooms</span></li>
        <li style="float:right"><span id="navbar-username"></span></li>
        <li style="float:right"><span id="navbar-logout">Log out</span></li>
    </ul>
</nav>
<?php
        break;
        case 'student':?>
<nav>
    <ul id="navbar" class="navbar">
        <li><img id="navbar-logo" src="img/svg/murica-square-small.svg" alt="logo"></li>
        <li><span id="navbar-courses">Courses</span></li>
        <li><span id="navbar-exams">Exams</span></li>
        <li style="float:right"><span id="navbar-username"></span></li>
        <li style="float:right"><span id="navbar-logout">Log out</span></li>
    </ul>
</nav>
<?php } ?>


