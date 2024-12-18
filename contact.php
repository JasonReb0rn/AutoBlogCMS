<?php
  // Set session cookie parameters to make it accessible across all directories
  session_set_cookie_params(0, '/');
  session_start();
?>

<!DOCTYPE html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <title>Contact</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=052523">
    <link rel="icon" type="image/png" href="https://champschance.s3.us-east-2.amazonaws.com/assets/favicon.png">
    <link rel="icon" type="image/png" href="https://champschance.s3.us-east-2.amazonaws.com/assets/favicon-16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="https://champschance.s3.us-east-2.amazonaws.com/assets/favicon-32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://champschance.s3.us-east-2.amazonaws.com/assets/favicon-48.png" sizes="48x48">
    <link rel="icon" type="image/png" href="https://champschance.s3.us-east-2.amazonaws.com/assets/favicon-64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="https://champschance.s3.us-east-2.amazonaws.com/assets/favicon-128.png" sizes="128x128">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" charset="utf-8"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LdCb70pAAAAAPWieAd9aQrzppc-Ha_SnhWoXptB"></script>
</head>

<body>
    
    <?php
        include_once 'header.php';
    ?>
    <script src="js/breadcrumbs.js"></script>

    <div class="main-content">

    <div class="contact-container">
        <h2>Contact Us!</h2>
        <p>At Champs Chance, we are dedicated to finding loving homes for dogs in need, and we want to make the adoption process as smooth and stress-free as possible. If you are considering adopting a dog or have any questions about our organization, we encourage you to reach out to us. We are always available to provide information about our adoption process, answer any questions you may have, and offer suggestions on how you can help support our mission. Don't hesitate to contact us - we are here to help!</p>
        <br>
        <p>For more information, please contact us at:</p>
        <form action="includes/contact-form.inc.php" class="contact-form" id="contact-form" method="post">
            <input type="text" name="name" placeholder="Full name">
            <input type="text" name="mail" placeholder="Your e-mail">
            <input type="text" name="subject" placeholder="Subject">
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            <textarea name="message" placeholder="Message"></textarea>
            <button type="button" onclick="submitForm()">Send Mail</button>
        </form>
        <?php
            if (isset($_GET["contact-error"])) { 
                if ($_GET["contact-error"] == "recaptcha-failed") {
                    echo "<div class=\"error-message\">reCAPTCHA failed.</div><div class=\"error-message\">Either you're a robot or something went wrong on our end.</div>";
                }
                else if ($_GET["contact-error"] == "AWS-SDK-not-Found") {
                    echo "<div class=\"error-message\">AWS SDK for PHP not found on the server. Uh oh.</div>";
                }
                else if ($_GET["contact-error"] == "failed") {
                    echo "<div class=\"error-message\">Could not send your message at this time.</div>";
                }
            }
            else if (isset($_GET["contact-success"])) {
                if ($_GET["contact-success"] == "sent") {
                    echo "<div class=\"success-message\">Success! Your message has been delivered.</div>";
                }
            }
        ?>
    </div>

    <script>
        function submitForm() {
           grecaptcha.ready(function() {
               grecaptcha.execute('6LdCb70pAAAAAPWieAd9aQrzppc-Ha_SnhWoXptB', {action: 'submit'}).then(function(token) {
                   document.getElementById("g-recaptcha-response").value = token;
                   // Now that we have the reCAPTCHA token, submit the form
                   document.getElementById("contact-form").submit();
               });
           });
        }
    </script>


    </div>
    
    <?php
        include_once 'footer.php';
    ?>

        
    </div>
</body>
</html>
