//  document.getElementById("addReviewBtn").addEventListener("click", function() {
//      $('#reviewModal').modal('show');
//  });

// $(document).ready(function(){
//     // Add click event listener to the "Add Review" button
//     $("#addReviewBtn").click(function(){
//         // Open the review modal
//         $("#reviewModal").modal('show');
//     });
// });

// $(document).ready(function() {
//     $('#reviewForm').submit(function(event) {
//         event.preventDefault(); // Prevent default form submission

//         // Get form data
//         var formData = $(this).serialize();

//         // AJAX call to post review
//         $.ajax({
//             type: 'POST',
//             url: 'your_php_script.php', // Replace 'your_php_script.php' with the actual PHP script URL
//             data: formData,
//             success: function(response) {
//                 // Handle success response, if needed
//                 alert(response); // Show a success message
//                 $('#reviewModal').modal('hide'); // Close modal after successful submission
//             },
//             error: function(xhr, status, error) {
//                 // Handle error
//                 console.error(xhr.responseText);
//             }
//         });
//     });
// });
