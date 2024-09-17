<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <style> 
        /* CSS for footer */ 
        .footer { 
            background-color: black;  
            color: white; 
        } 
	

    /* Additional styles for the button if needed */
    .newsletter-form button[type="submit"] {
        margin-left: 75px; 
		margin-top: 10px;
        
    }
    </style> 
</head> 

<footer class="footer p-5">
    <div class="container">
        <h2 class="text-center mb-2 footer-title">SOMETHINGQLO</h2>
        <div class="row footer-content-row">
            <div class="col-md-4">
			<h3 class="about-us-heading">Newsletter</h3>
				<form class="newsletter-form" action="news.php" method="post">Email:
        			<input type="email" name="email" value="" aria-label= "newsletter" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address.">
       				 <button type="submit" name="send">Subscribe</button>
    			</form>
            </div>
				<div class="col-md-4"> 
				<h3 class="contact-us-heading">Contact us</h3>
					<ul class="list-unstyled"> 
						<li>Email: enquiry@somethingqlo.com</li> 
						<li>Phone: +65 8076 1596</li> 
						<li>Address: Blk 849 Jurong West Street 81, Singapore 640849</li> 
					</ul> 
				</div> 
				<div class="col-md-4"> 
				<h3 class="follow-us-heading">Follow us</h3>
					<ul class="list-inline footer-links"> 
						<li class="list-inline-item"> 
						<a href="https://www.facebook.com" class="facebook-icon" aria-label="Facebook"><i class="fa-brands fa-facebook"></i><span class="sr-only">Facebook</span></a>
						</li> 
						<li class="list-inline-item"> 
						<a href="https://www.instagram.com" class="instagram-icon" aria-label="Instagram"><i class="fa-brands fa-instagram"></i><span class="sr-only">Instagram</span></a>
						</li> 
						<li class="list-inline-item"> 
						<a href="https://www.youtube.com" class="youtube-icon" aria-label="youtube"><i class="fa-brands fa-youtube"></i><span class="sr-only">Youtube</span></a>
						</li> 
						<li class="list-inline-item"> 
						<a href="https://www.twitter.com" class="twitter-icon" aria-label="Twitter"><i class="fa-brands fa-twitter"></i><span class="sr-only">Twitter</span></a>
						</li> 
					</ul> 
				</div> 
			</div> 
			<hr> 
			<div class="row end-of-footer"> 
				<div class="col-md-6"> 
					<p>© 2024 Somethingqlo. All rights reserved.</p> 
				</div> 
				<div class="col-md-6 text-end"> 
					<ul class="list-inline footer-links"> 
						<li class="list-inline-item"> 
							<a href="#" class="text-white"> 
								Privacy Policy 
							</a> 
						</li> 
						<li class="list-inline-item"> 
							<a href="#" class="text-white"> 
								Terms of Service 
							</a> 
						</li> 
						<li class="list-inline-item"> 
							<a href="#" class="text-white"> 
								Sitemap 
							</a> 
						</li> 
					</ul> 
				</div> 
			</div> 
		</div> 
	</footer> 
 

</html>
