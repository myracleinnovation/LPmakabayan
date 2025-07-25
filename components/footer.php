 <!-- FOOTER -->
 <footer class="bg-warning text-black mt-3">
     <div class="container text-center">
         <img src="assets/img/logo2.png" alt="Logo" class="mb-3" style="width:80px;">
         <div class="fw-bold mb-3">Makabayan Avellanosa Construction</div>
         <div class="mb-3">
             <div class="mb-1">Address: 34 A. Novales, Project 4, Quezon City, 1800 Kalakhang Maynila</div>
             <div class="mb-1">Contact: (0917) 596 5155 | macons2022@gmail.com</div>
         </div>
         <div class="d-flex justify-content-center flex-wrap gap-3 mb-4">
             <a href="https://maps.app.goo.gl/c12NV7UAhKwcuhrf7" target="_blank"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class='bx bx-map'></i>
             </a>
             <a href="mailto:macons2022@gmail.com"
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
             <a href="#"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class='bx bxl-twitter'></i>
             </a>
             <a href="#"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class="bx bxl-linkedin"></i>
             </a>
             <a href="#"
                 class="footer-icon rounded-circle bg-white d-flex align-items-center justify-content-center text-decoration-none text-dark"
                 style="width:50px; height:50px; font-size:1.5rem; transition: all 0.3s ease;">
                 <i class="bx bxl-youtube"></i>
             </a>
         </div>
     </div>
     <div class="bg-black text-white py-3">
         <div class="container text-center">
             <div><a href="#" data-bs-toggle="modal" data-bs-target="#termsConditionModal"
                     class="text-white text-decoration-underline">Terms and Condition</a> | <a href="#"
                     data-bs-toggle="modal" data-bs-target="#privacyStatementModal"
                     class="text-white text-decoration-underline">Privacy Statement</a></div>
             <div class="fw-bold">© <span id="year"></span> Makabayan Avellanosa Construction</div>
         </div>
     </div>
 </footer>

 <?php include_once __DIR__ . '/modals/termsCondition.php'; ?>
 <?php include_once __DIR__ . '/modals/privacyStatement.php'; ?>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
     integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
 </script>
 <script src="assets/js/main.js"></script>