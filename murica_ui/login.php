<?php
$context = 'login';
require 'php/header.php';
?>
<main>
    <div class="login-container">
    <img src="img/png/murica-oval-login.png" alt="murica logo">
    <h2 class="login-heading">LOGIN</h2>
    <form method="POST" id="login_form">
        <label for="id">ID:</label>
        <input type="text" name="id" id="id" maxlength="6"/>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password"/>
        <input type="submit" value="Submit" name="submit" class="submit" id="submit" />
    </form>
    <p id="error" class="error-message"></p>
    </div>
</main>
<?php
require 'php/footer.php';
?>
