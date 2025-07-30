<?php 
if(isset($_SESSION['USER_ROLE'])){
  $name = $_SESSION['USER_NAME'];
  $surname = $_SESSION['USER_SURNAME'];
  $user_role = $_SESSION['USER_ROLE'];
} else {
  $name = '';
  $surname = '';
  $user_role = '';
}
?>

<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page"><?php echo $pageTitle ? $pageTitle : ''; ?></li>
          </ol>
          <h6 class="font-weight-bolder mb-0"><?php echo $pageTitle ? $pageTitle : ''; ?></h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">
            <li class="list-group-item border-0 d-flex align-items-center px-4 mb-2 pt-0">
                <div class="avatar me-3">
                  <img src="../assets/img/profile.png" alt="kal" class="border-radius-lg shadow">
                </div>
                <div class="d-flex align-items-start flex-column justify-content-center">
                  <h6 class="mb-0 text-sm">Welcome <?php echo $name; ?></h6>
                  <p class="mb-0 text-xs">
                    <a style="text-decoration: underline;" href="/profile">Edit Profile</a>
                  </p>
                </div>
            </li>
            <li class="mt-2">
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>
            <li class="nav-item d-flex align-items-center">
              <a href="/logout" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">Sign Out</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
<!-- End Navbar -->    