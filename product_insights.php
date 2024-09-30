<!DOCTYPE html>
<html lang="en">

<body>
    <?php
        include "inc/head.inc.php";
        include "inc/header.inc.php";
        include "inc/nav.inc.php";
    ?>

    <main class="container">
            <!-- The Modal -->
            <div class="container about-us">
                <div class="row">
                    <div class="col-md-6">
                        <h2>About Us</h2> <br>
                            <p>
                            SOMETHINGQLO is a clothing apparel company, which was originally founded in Yamaguchi, Japan in 1949 as a textiles manufacturer. Now it is a global brand with over 1000 stores around the world. Redefining clothing, with a focus on quality and textiles which has been unwavered since the company's origins in 1949.  
                            </p>
        <!-- Add more text as needed -->
                    </div>
                    <div class="col-md-6">
                        <img src="images\aboutus.JPG" alt="About Us" class="img-fluid">
                    </div>
                </div>
            </div>
            
            <div class="container another-section">
                <div class="row">
                    <!-- Google Maps iframe on the left -->
                    <div class="col-md-6 custom-iframe-position">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2820.449724620731!2d103.68500396448098!3d1.345490929055985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da0f328aaf151f%3A0x764d8ed2235dbf48!2ssomethingqlo!5e0!3m2!1sen!2ssg!4v1712122753332!5m2!1sen!2ssg" width="400" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="googleMaps"></iframe>
                    </div>

                    <!-- Contact information on the right -->
                   <!-- Contact information on the right -->
                    <div class="col-md-6">
                    <div class="contact-us-info">
                        <h2>Contact us</h2> <br>
                        <p>
                        Location: <br>
                        Blk 849 Jurong West Street 81, Singapore 640849 <br><br>
                        Email: enquiry@somethingqlo.com <br><br>
                        Call: +65 8076 1596 <br> <br>
                        Opening hours: <br>Monday to Friday 9:00 - 17:00 
                        </p>
                        <!-- Add more text as needed -->
                    </div>
                    </div>

                </div>
            </div>

            <div class="container google-reviews">
            <div class="row">
                <div class="col-12">
                <h2>Customer Reviews</h2> <!-- You can customize the header as needed -->
                <iframe src="https://widget.tagembed.com/144159?view" style="width:100%;height:500px;overflow:auto" frameborder="0" allowtransparency="true" title="googleReview"></iframe>
                <!-- Adjusted button with higher contrast ratio -->
                </div>
            </div>
            </div>

    </div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
    <script src="script.js"></script>
</body>