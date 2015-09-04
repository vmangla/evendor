<?php
//CAPTCHA Matching code
session_start();
if ($_SESSION["code"] == $_POST["captcha"]) {
    echo "Form Submitted successfully....!";
} else {
    die("Wrong TEXT Entered");
}
?>
