<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <title>Volunteer</title>
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
</head>

<body>
    
    <?php
        include_once 'header.php';
    ?>
    <script src="js/breadcrumbs.js"></script>

    <div class="main-content">

        <div class="content-container-solid">
            <div class="volunteer-container">
                <div class="volunteer-content" id="one">
                    <h3>Why Volunteer?</h3>
                    <p>At Champs Chance, we are incredibly grateful for our volunteers who generously donate their time and skills to help our dog adoption group. As a non-profit organization, we heavily rely on the support of volunteers to provide the best care for our furry friends and increase their chances of finding forever homes. By volunteering with us, you not only help us achieve our mission but also gain valuable experience in animal care and handling, as well as make new friends who share your passion for animals.</p>
                    <p>Additionally, volunteering with Champs Chance can contribute to community service hours for high school students looking to fulfill their requirements. It's a great way to gain practical experience while giving back to your community and helping animals in need. Your dedication and hard work enable us to make a difference in the lives of dogs in need and create a better community for all. Thank you for considering volunteering with Champs Chance.</p>
                </div>

                <div class="volunteer-content" id="two">
                    <h3>How To Get Started</h3>
                    <p>Getting started is easy! Below, you'll find our volunteer application. Once completed, we'll email you a link to a sign-up form, where you can decide what days you would like to come and volunteer.</p>
                </div>

            </div>

            <div class="volunteer-button-container">
            <a class="volunteerbtn" href="https://www.cognitoforms.com/ChampsChanceInc1/ChampsChanceIncVolunteerApplication"><button>Apply Here to Volunteer!</button></a>
            </div>

        </div>

        <div class="cal-container">
            <div class="cal-link">
              <a class="calbtn" href="https://teamup.com/ksqrdrbcs42cj865ws" target="_blank"><img src="img/vol-cal.png" alt="Calander"></a>
            </div>
            <div class="cal-header">    
              <h3>Volunteer Calendar</h3>
              <p>Click the calendar to see a list of all of our events that we'll be at! Stop by to see some of our lovely dogs in person, talk about the adoption process, or even just support us by showing up.</p>
              <br>
              <p>We can also <i>always</i> use help, check out the <a href="/volunteer.php">Volunteer Page</a> for more information.</p>
            </div>
        </div>

        
        <div class="content-container-solid">
            <div class="volunteer-tasks-container">
                <h3>Types of Tasks</h3>
                <p>Even though all tasks are helpful and needed, volunteers can choose to perform only what they're comfortable with.</p>
                <p class="dontindent">Tasks may include:</p>
                <ul>
                    <li>Walking dogs</li>
                    <li>Feeding dogs</li>
                    <li>Cleaning kennels</li>
                    <li>Bathing dogs</li>
                    <li>Dog field-trips</li>
                    <li>Event help</li>
                    <li>Organizing storage</li>
                </ul>
            </div>
        </div>
        

    </div>
    
    <?php
        include_once 'footer.php';
    ?>

        
    </div>
</body>
</html>