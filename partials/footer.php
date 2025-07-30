<footer class="footer pt-5">
      <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="copyright text-center text-sm text-muted text-lg-start">
              © <script>
                document.write(new Date().getFullYear())
              </script>,
              made with <i class="fa fa-heart"></i> by
              <a href="https://duendedigital.co.za" class="font-weight-bold" target="_blank">Duende Digital</a>
              for a better web.
            </div>
          </div>
          <div class="col-lg-6">
          </div>
        </div>
      </div>
    </footer>
    <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="material-icons py-2">settings</i>
    </a>
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-start">
          <h5 class="mt-3 mb-0">GIMME Settings</h5>
          <p>Configure settings & get technical help.</p>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-icons">clear</i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <div class="card-body pt-sm-3 pt-0">
        <hr class="horizontal dark my-3">
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
          </div>
        </div>
        <hr class="horizontal dark my-sm-4">
        <a class="btn btn-outline-dark w-100" href="">Open a Ticket</a>
        <div class="w-100 text-center">
          <h6 class="mt-3">Quick Links!</h6>
          <a href="https://twitter.com" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="https://www.facebook.com" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
          </a>
        </div>
      </div>
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="/assets/js/core/popper.min.js"></script>
  <script src="/assets/js/core/bootstrap.min.js"></script>
  <script src="/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="/assets/js/plugins/chartjs.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }

    function gimmeToast(message, className) {
      var color = '#2bb13f';
      if(className == 'success'){
        color = '#2bb13f';
      } else if(className == 'error'){
        color = '#f5365c';
      } else if(className == 'warning'){
        color = '#fb6340';
      } else if(className == 'info'){
        color = '#11cdef';
      }
      Toastify({
          text: message,
          avatar: "/images/Asset3.svg",
          duration: 3000,
          className: className, // `default`, `info`, `success`, `warning`, `error`
          gravity: "top", // `top` or `bottom`
          position: "center",
          style: {
              background: "#ffffff",
              border: "1px solid "+color,
              color: color,
          }
      }).showToast(); 
    }
  </script>
  <script src="/assets/js/material-dashboard.js"></script>
  <script>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
    
    window.onload = function() {
    const darkModeCookie = getCookie("darkMode");
    if (darkModeCookie === "true") {
        darkMode(document.getElementById("dark-version"));
    }
    };    
  </script>
