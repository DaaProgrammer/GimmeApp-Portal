<?php 
$pageTitle = "Matches";
require_once 'session/session.php';
// require_once 'session/permission_handler.php';

if(!isset($_POST['event_id'])){
    echo <<<EOD
    <h2>Error:</h2>
    <p>Sorry, we could not find the event that you want to update. Please try again later or contact your administrator for support.</p><br/>
    <a href="/matches/">Go Back</a>
    EOD;
    exit;
}

if(!isset($_POST['event_name']) || !isset($_POST['event_scoring'])){
    echo <<<EOD
    <h2>Error:</h2>
    <p>Sorry, we could not find the match scores that you want to update. Please try again later or contact your administrator for support.</p><br/>
    <a href="/matches/">Go Back</a>
    EOD;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'partials/header.php'; ?>
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

.sidenav {
  z-index: 1038;
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
                    <h6 class="text-white text-capitalize ps-3">Match Play - Game Scores</h6>
                </div>
                </div>
                <div style="overflow-x: auto;padding: 20px" class="card">
                    <div class="card-header pb-3 px-3">
                        <p class="mb-0">View match scores.</p>
                    </div>

                    <!-- Loader Container -->
                    <div id="loaderContainer" style="display: none; justify-content: center; align-items: center; height: 200px;">
                        <div class="loader"></div>
                    </div>
        
                    <iframe height=200 src="<?= $_ENV['API_URL']; ?>/api/v1/scores/scorecard.php?gamemode=event&game_id=<?= $_POST['event_id'] ?>&game_type=<?= $_POST['event_scoring'] ?>" frameborder="0"></iframe>
                    
                </div>
              </div>
            </div>
        </div>  

        <?php include 'partials/footer.php'; ?>
    </div>
</main>

<script type="module">
    const API_URL = "<?php echo $_ENV['API_URL']; ?>";
    const TOKEN = "<?php echo $_COOKIE['jwt']; ?>";  

    // Loader Container
    const loaderContainer = document.getElementById('loaderContainer');
    // loaderContainer.style.display = 'flex';

    // Sort scores based on lowest value
    const totals = document.querySelectorAll('.total');
    for (let i = 0; i < totals.length; i++) {
        for (let j = i + 1; j < totals.length; j++) {
            const totalI = parseInt(totals[i].textContent);
            const totalJ = parseInt(totals[j].textContent);
            if (totalI > totalJ) {
                totals[i].parentNode.parentNode.insertBefore(totals[j].parentNode, totals[i].parentNode);
            }
        }
    }
    
</script>
</body>
</html>
