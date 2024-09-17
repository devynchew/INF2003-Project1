<!DOCTYPE html>
<html lang="en">
    <body>
      <?php
        include "inc/head.inc.php";
        include "inc/header.inc.php";
        include "inc/nav.inc.php";
      ?>
      
      
      <main id="uniqueSectionHeading"> <!-- Start of the main content -->
    <section aria-labelledby="carouselHeading">
      <h2 id="carouselHeading" class="sr-only">Product Carousel</h2>
      <?php include "inc/carousel.inc.php"; ?>
    </section>
    
    <section aria-labelledby="featuredProductsHeading">
    <h2 id="featuredProductsHeading" class="sr-only">Featured Products</h2>
          <div class="container-main">
              <div class="text-container">
                <h1>Clothes that are made to impress.</h1>
                <p>Made from high quality materials, crafted with excellence, worn with comfort. </p>
                <a href=".\product.php" class="button" id="unique-button">Shop Now</a>
              </div>
              <div class="image-container">
                <!-- Images are stacked with CSS; no need for separate divs for each image -->
                <img src="images/woman1.png" alt="Clothes Theme 1" class="image">
                <img src="images/woman2.png" alt="Clothes Theme 2" class="image">
                <img src="images/woman3.png" alt="Clothes Theme 3" class="image">
              </div>
            </div>
    
          </div>
    </section>
  </main>
        
       
            


        <!--Popup Container-->
        <!-- <div class="popup-container" id="popupContainer" onclick="closePopup()">
            <img class="popup-content" id="popupImg" src="" alt="Popup Image">
        </div> -->

        <?php
          include "inc/footer.inc.php";
        ?>
         
    </body>
</html>