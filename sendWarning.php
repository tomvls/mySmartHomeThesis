<?php
    $receiver = "tchantzakos@gmail.com";
    $subject = "Smart Home Warning";
    // the message
    $msg = $_GET['warningMsg'];

    // replace %20 with whitespaces
    $finalmsg = str_replace('%20', ' ', $msg);

    // use wordwrap() if lines are longer than 70 characters
    $msg = wordwrap($msg,70);

    // send email
    mail($receiver,$subject,$finalmsg);
?>