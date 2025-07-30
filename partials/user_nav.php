<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Dashboard") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">dashboard</i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Events") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/events">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">event</i>
            </div>
            <span class="nav-link-text ms-1">Events</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Matches") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/matches">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">golf_course</i>
            </div>
            <span class="nav-link-text ms-1">Match Play</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Statistics") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/statistics">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">equalizer</i>
            </div>
            <span class="nav-link-text ms-1">Statistics</span>
        </a>
    </li>
</ul>