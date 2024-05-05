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
    <script>
        const apiUrl = 'http://localhost:8012/murica_api/';
    </script>
    <script src="lib/jquery-3.7.1.min.js"></script>
    <script src="js/common.js"></script>
    <?php
        echo match ($context) {
            'login' => '<script src="js/login.js"></script>',
            'index' => '<script src="js/index.js"></script>',
            'admin' => '<script src="js/admin.js"></script>',
            'student' => '<script src="js/student.js"></script>',
            'teacher' => '<script src="js/teacher.js"></script>',
            default => ''
        };

    if ($context == 'login' || $context == 'index' ) {
    ?>
        <link rel="stylesheet" href="css/login.css">
    <?php } ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="icon" type="image/svg" href="img/svg/murica-square-small.svg">
    <title>MURICA<?php
            echo match ($context) {
                'login' => ' | Login',
                'admin' => ' | Administrator',
                'student' => ' | Student',
                'teacher' => ' | Teacher',
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
            <ul id="navbar" class="navbar">
                <li><img id="navbar-logo" src="/murica_ui/img/svg/murica-square-small.svg" alt="logo"></li>
                <li style="float:right"><span id="navbar-username"></span></li>
                <li style="float:right"><span id="navbar-logout">Log out</span></li>
                <li style="float:right"><select id="navbar-role-select"></select></li>
            </ul>
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
        <li style="float:right"><select id="navbar-role-select"></select></li>
    </ul>
</nav>
<?php
        break;
        case 'student': ?>
<nav>
    <ul id="navbar" class="navbar">
        <li><img id="navbar-logo" src="img/svg/murica-square-small.svg" alt="logo"></li>
        <li class="navbar-dropdown">
            <span id="navbar-courses">Courses</span>
            <ul class="submenu">
                <li><span id="navbar-taken-courses">Taken courses</span></li>
                <li><span id="navbar-course-registration">Course registration</span></li>
            </ul>
        </li>
        <li class="navbar-dropdown">
            <span id="navbar-exams">Exams</span>
            <ul class="submenu">
                <li><span id="navbar-taken-exam">Taken exams</span></li>
                <li><span id="navbar-exam-registration">Exam registration</span></li>
            </ul>
        </li>
        <li style="float:right"><span id="navbar-username"></span></li>
        <li style="float:right"><span id="navbar-logout">Log out</span></li>
        <li style="float:right"><select id="navbar-role-select"></select></li>
    </ul>
</nav>
<?php
        break;
        case 'teacher':?>
<nav>
    <ul id="navbar" class="navbar">
        <li><img id="navbar-logo" src="img/svg/murica-square-small.svg" alt="logo"></li>
        <li><span id="navbar-courses">Courses</span></li>
        <li><span id="navbar-exams">Exams</span></li>
        <li style="float:right"><span id="navbar-username"></span></li>
        <li style="float:right"><span id="navbar-logout">Log out</span></li>
        <li style="float:right"><select id="navbar-role-select"></select></li>
    </ul>
</nav>
<?php } ?>


