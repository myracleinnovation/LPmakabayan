 <!-- FOOTER -->
 <footer class="bg-warning text-black mt-3">
     <div class="container text-center">
         <img src="assets/img/logo.png" alt="Logo" class="mb-3" style="width:80px;">
         <div class="fw-bold mb-3">Makabayan Avellanosa Construction</div>
         <div class="mb-3">
             <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2 mb-2">
                 <span>Centro Tumalim, Nasugbu, Batangas, Philippines</span>
                 <span class="footer-separator">•</span>
                 <span>JP Laurel St., Bgry. 2, Nasugbu, Batangas, Philippines</span>
             </div>
             <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                 <span>+63 917 596 5155</span>
                 <span class="footer-separator">•</span>
                 <span>macons2022@gmail.com</span>
                 <span class="footer-separator">•</span>
                 <span>connect@makabayanavellanosa.com</span>
             </div>
         </div>
         <div class="d-flex justify-content-center flex-wrap gap-3 mb-4">
             <a href="https://maps.app.goo.gl/v6PRdFqaEsTURANN7" target="_blank"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class='bx bx-map'></i>
             </a>
             <a href="mailto:connect@makabayanavellanosa.com"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class='bx bx-envelope'></i>
             </a>
             <a href="tel:+639175965155"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class="bx bx-phone"></i>
             </a>
             <a href="https://www.facebook.com/makabayanavellanosa" target="_blank"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class="bx bxl-facebook"></i>
             </a>
         </div>
     </div>
     <div class="bg-black text-white py-3">
         <div class="container text-center">
             <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                 <a href="#" data-bs-toggle="modal" data-bs-target="#termsConditionModal"
                     class="text-white text-decoration-underline">Terms and Condition</a>
                 <span class="footer-separator text-white">•</span>
                 <a href="#" data-bs-toggle="modal" data-bs-target="#privacyStatementModal"
                     class="text-white text-decoration-underline">Privacy Statement</a>
             </div>
             <div>© <span id="year"></span> Makabayan Avellanosa Construction</div>
         </div>
     </div>
 </footer>

 <?php include_once __DIR__ . '/modals/termsCondition.php'; ?>
 <?php include_once __DIR__ . '/modals/PrivacyStatement.php'; ?>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
     integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
 </script>
 <script src="assets/js/main.js"></script>
