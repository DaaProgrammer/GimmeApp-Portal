<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-white" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="/" target="_blank">
            <img src="/images/Asset3.svg" class="navbar-brand-img h-100" alt="main_logo">
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto max-height-vh-100" id="sidenav-collapse-main">
        
        <?php 
            if (isset($_SESSION['USER_ROLE'] )) {
                $user_role = $_SESSION['USER_ROLE'] ;
                if ($user_role == 'admin') {
                    include 'partials/admin_nav.php';
                } else if ($user_role == 'event_organiser') {
                    include 'partials/user_nav.php';
                }
            }
        ?>

    </div>
</aside>
