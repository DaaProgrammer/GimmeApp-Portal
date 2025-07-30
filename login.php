<!DOCTYPE html>
<html style="font-size: 16px;" lang="en"><head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title>GIMME Login | Plan, Play, Improve</title>
    <link rel="stylesheet" href="/nicepage.css" media="screen">
    <link rel="stylesheet" href="/Login.css" media="screen">
    <!-- <script class="u-script" type="text/javascript" src="/nicepage.js" defer=""></script> -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/htmx/1.9.12/htmx.min.js" integrity="sha512-JvpjarJlOl4sW26MnEb3IdSAcGdeTeOaAlu2gUZtfFrRgnChdzELOZKl0mN6ZvI0X+xiX5UMvxjK2Rx2z/fliw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <meta name="generator" content="Nicepage 6.0.3, nicepage.com">
    <meta name="referrer" content="origin">
    <link id="u-theme-google-font" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i">

    <script type="application/ld+json">{
		"@context": "http://schema.org",
		"@type": "Organization",
		"name": "GIMME"
}</script>
<style>
#response {
    color: #111111;
    font-size: 12px;
    padding: 3px;
}  
</style>
    <meta name="theme-color" content="#478ac9">
    <meta property="og:title" content="Login">
    <meta property="og:description" content="">
    <meta property="og:type" content="website">
  <meta data-intl-tel-input-cdn-path="intlTelInput/"></head>
  <body data-path-to-root="./" data-include-products="false" class="u-body u-xl-mode" data-lang="en">
    <section class="u-clearfix u-container-align-center u-section-1" id="sec-727e">
      <div class="u-clearfix u-sheet u-sheet-1">
        <div class="u-container-style u-group u-white u-group-1">
          <div class="u-container-layout u-container-layout-1">
            <img class="u-image u-image-default u-preserve-proportions u-image-1" src="/images/Asset3.svg" alt="" data-image-width="129" data-image-height="150">
            <div class="u-align-center u-container-align-center u-container-style u-expanded-width u-group u-radius u-shape-round u-white u-group-2">
              <div class="u-container-layout u-container-layout-2">
                <h1 class="u-align-center u-text u-text-default u-text-1">Sign In</h1>
                <div class="u-form u-form-1">
                  <form hx-post="https://duendedisplay.co.za/gimme/api/v1/auth/login_htmx.php" hx-encoding="multipart/form-data" hx-target="#response" hx-swap="innerHTML" htmx.config.withCredentials = true; class="u-clearfix u-form-spacing-15 u-form-vertical u-inner-form" style="padding: 15px;" source="email" name="form">
                    <div class="u-form-email u-form-group u-label-none">
                      <label for="name-6797" class="u-label">Email</label>
                      <input type="email" placeholder="Enter your email" id="name-6797" name="email" class="u-border-grey-10 u-gradient u-input u-input-rectangle u-none u-radius u-input-1" required="required">
                    </div>
                    <div class="u-form-group u-label-none">
                      <label for="email-6797" class="u-label">Password</label>
                      <input type="password" placeholder="Enter your password" id="email-6797" name="password" class="u-border-grey-10 u-gradient u-input u-input-rectangle u-none u-radius u-input-2" required="required">
                    </div>
                    <div class="u-form-checkbox u-form-group u-label-none u-form-group-3">
                      <input type="checkbox" id="checkbox-ed86" name="checkbox" value="On" class="u-field-input">
                      <label for="checkbox-ed86" class="u-field-label" style="font-size: 0.75rem;">Remember Me</label>
                    </div>
                    <div class="u-align-center u-form-group u-form-submit u-label-none">
                     <input type="submit" value="Sign In" class="u-border-none u-btn u-btn-submit u-button-style u-custom-color-1 u-btn-1">
                    </div>
                  </form>
                  <div id="response"></div>
                </div>
                <a href="#" class="u-active-none u-border-2 u-border-custom-color-1 u-border-no-left u-border-no-right u-border-no-top u-btn u-btn-rectangle u-button-style u-hover-none u-none u-btn-2">Forgot Password?</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
<script>
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
</body></html>