<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <!-- Light Logo-->
        <a href="home.php" class="logo logo-light">
            <span class="logo-sm">
                <img src="assets/images/mfa.png" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="assets/images/logo-sm.png" height="80" width="200">
            </span>
        </a>

    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarConfiguration" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarConfiguration">
                        <i class="bx bx-cog"></i> <span data-key="t-configuration">Configuración</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarConfiguration">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="users.php" class="nav-link" data-key="t-users">Usuarios</a>
                            </li>
                            <li class="nav-item">
                                <a href="user_role.php" class="nav-link" data-key="t-user_role">Roles</a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
        <i class="ri-record-circle-line"></i>
    </button>
    <div class="sidebar-background"></div>
</div>