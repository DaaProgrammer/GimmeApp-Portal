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
        <a class="nav-link <?php if($pageTitle == "Users") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/users">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">people</i>
            </div>
            <span class="nav-link-text ms-1">Users</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Golf Courses") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/golf-courses">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">place</i>
            </div>
            <span class="nav-link-text ms-1">Golf Courses</span>
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
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Event Organisers") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/event-organisers">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">person_add</i>
            </div>
            <span class="nav-link-text ms-1">Event Organisers</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Events") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="<?php if($user_role == 'admin'){ echo "/admin-events"; }else{ echo "/events"; } ?>">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">event</i>
            </div>
            <span class="nav-link-text ms-1">Events</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Chat") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/chat">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">chat</i>
            </div>
            <span class="nav-link-text ms-1">Chat</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php if($pageTitle == "Rule Book") { echo "text-white active bg-gradient-primary"; } else { echo "text-dark"; } ?>" href="/rules">
            <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons">library_books</i>
            </div>
            <span class="nav-link-text ms-1">Rule Book</span>
        </a>
    </li>
</ul>