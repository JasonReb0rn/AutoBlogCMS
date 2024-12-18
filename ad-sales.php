<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <title>Medical Spotlight</title>
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
              <div class="content-block-flex-container">
                <div class="content-block-flex-photo">
                  <img src="https://champschance.s3.us-east-2.amazonaws.com/assets/alicia.png" alt="Champs Chance Story">
                </div>
                <div class="content-block-flex-content">
                  <h3>Advertise with Us and Support Our Cause!</h3>
                  <p>Thank you for considering advertising with Champs Chance! By purchasing ad space on our website, you not only promote your brand but also directly contribute to our mission of finding loving homes for dogs in need.</p>
                  <br>
                  <p>Your support enables us to cover the costs of running this website, ensuring that more resources can be allocated towards rescuing and caring for our furry friends.</p>
                </div>
              </div>

              <div class="content-block-flex-container">
                <div class="content-block-flex-content">
                    <h3>Advertising Options:</h3>
                    <ol>
                        <li><b>Homepage Banner Ad: </b>Place your brand front and center with a banner ad on our homepage. This prime real estate ensures maximum visibility to our visitors.</li>
                        <li><b>Category Page Sidebar Ad: </b>Target specific audiences by placing your ad on one of our category pages. Whether it's adoption tips, success stories, or dog care advice, you can tailor your message to reach those most interested in your offerings.</li>
                        <li><b>Featured Article Sponsorship: </b>Sponsor one of our informative articles related to dog care, adoption, or animal welfare. Your brand will be prominently featured within the content, aligning your business with our mission.</li>
                    </ol>
                </div>
              </div>
        </div>
        
        <div class="sponsor-pricing-div">
            <div class="sponsor-pricing-header">
                <h3>Pricing</h3>
            </div>
            <div class="sponsor-cards">
                <div class="sponsor-prices-card-container">
                        <div class="sponsor-price-card">
                            <img class="sponsor-card-img" src="https://champschance.s3.us-east-2.amazonaws.com/assets/alicia.png" alt="sponsor">
                            <h3>Homepage Banner Ad</h3>
                            <p>$50 | 1 month</p>
                        </div>
                    </div>

                    <div class="sponsor-prices-card-container">
                        <div class="sponsor-price-card">
                            <img class="sponsor-card-img" src="https://champschance.s3.us-east-2.amazonaws.com/assets/alicia.png" alt="sponsor">
                            <h3>Page Sidebar Ad</h3>
                            <p>$50 | month</p>
                        </div>
                    </div>

                    <div class="sponsor-prices-card-container">
                        <div class="sponsor-price-card">
                            <img class="sponsor-card-img" src="https://champschance.s3.us-east-2.amazonaws.com/assets/alicia.png" alt="sponsor">
                            <h3>Featured Article Sponsorship</h3>
                            <p>$50 | month</p>
                        </div>
                    </div>
                </div>  
            </div>
        </div>

        <div class="content-container-solid">
              <div class="content-block-flex-container">
                <div class="content-block-flex-content">
                  <h3>Terms and Conditions</h3>
                  <ul>
                    <li>All ads are subject to approval by Champs Chance to ensure they align with our mission and values.</li>
                    <li>Ad space is allocated on a first-come, first-served basis.</li>
                    <li>Advertisers are responsible for providing high-resolution images and ad copy that comply with our guidelines.</li>
                    <li>Payments are processed securely through Stripe. No refunds will be issued once an ad is live.</li>
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

<script src="js/medical-spotlight-container.js"></script>