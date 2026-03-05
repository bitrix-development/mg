<?php
// Assuming login logic here
if ($successfulLogin) {
    $_SESSION['welcome_once'] = 1;
    header('Location: redirect_page.php');
}
?>