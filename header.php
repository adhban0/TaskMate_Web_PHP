<header>
  <nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container py-1">
      <!-- Brand -->
      <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="dashboard.html">
        <span class="d-inline-flex align-items-center justify-content-center"
              style="width:38px;height:38px;border-radius:12px;background:#111827;color:#fff;">
          <i class="fa-solid fa-check"></i>
        </span>
        <span>TaskMate</span>
      </a>

      <!-- Mobile toggle -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#taskmateNav"
              aria-controls="taskmateNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Nav links -->
      <div class="collapse navbar-collapse" id="taskmateNav">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
          <li class="nav-item">
            <a class="nav-link" href="dashboard.html">
              <i class="fa-solid fa-gauge-high me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="todo.html">
              <i class="fa-regular fa-square-check me-1"></i>To-do
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="calendar.html">
              <i class="fa-regular fa-calendar me-1"></i>Calendar
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="notes.html">
              <i class="fa-regular fa-note-sticky me-1"></i>Notes
            </a>
          </li>
        </ul>

        <!-- Right side -->
        <div class="d-flex align-items-center gap-2">
          <input class="form-control form-control-sm d-none d-md-block"
                 style="width:220px;"
                 placeholder="Search…" />

          <button class="btn btn-sm btn-outline-secondary" title="Notifications">
            <i class="fa-regular fa-bell"></i>
          </button>

          <div class="dropdown">
            <button class="btn btn-sm btn-dark dropdown-toggle" data-bs-toggle="dropdown">
              Account
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.html">Profile</a></li>
              <li><a class="dropdown-item" href="settings.html">Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
