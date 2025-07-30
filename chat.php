<?php 
$pageTitle = "Chat";
require_once 'session/session.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>
<link href="/assets/css/jkanban.css" rel="stylesheet" />

<body class="g-sidenav-show bg-gray-200">

<?php include 'partials/left-nav.php'; ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<style>
.border-radius-xl {
    border-radius: 0.15rem;
}
.icon-lg {
    width: 45px;
    height: 45px;
}    
.loader {
    display: inline-block;
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 3px solid #3498db;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>    
    <!-- Navbar -->
        <?php include 'partials/top-nav.php'; ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
              <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                    <h6 class="text-white text-capitalize ps-3">Manage your app chat with Crisp</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px 0;" class="card">
                    
                <div style="margin: 0 auto;" class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                        <div class="card">
                            <div class="card-header p-3 pt-2">
                            <img style="width: 100%;height: auto;" src="/assets/img/crisp-logo.png" class="icon icon-lg">
                            <div class="text-end pt-1">
                                <h4 class="mb-0">Crisp.chat</h4>
                                <p class="text-sm mb-0 text-capitalize">Email: <input class="form-control" type="text" value="golfgimme35@gmail.com" disabled="true" /></p>
                                <p class="text-sm mb-0 text-capitalize">Password: <input class="form-control" type="text" value="GImm5@2029!" disabled="true" /></p>
                                <p class="text-sm mb-0 text-capitalize">Crisp API Key: <input class="form-control" type="text" value="d38d2e7e-da36-40de-8264-f8c8afc6db22" disabled="true" /></p>
                            </div>
                            </div>
                            <hr class="dark horizontal my-0">
                            <div class="card-footer p-3">
                            <p class="mb-0"><a target="_blank" href="https://app.crisp.chat/website/d38d2e7e-da36-40de-8264-f8c8afc6db22/inbox"><span class="text-success text-sm font-weight-bolder">Go to app >></span></a></p>
                            </div>
                        </div>
                    </div>

                </div>
              </div>  
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>

    </div>
</main>
</body>
</html>