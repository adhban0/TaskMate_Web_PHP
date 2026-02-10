<header>
  <nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container py-1">
      <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="index.php">
        <span>TaskMate</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#taskmateNav"
              aria-controls="taskmateNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="taskmateNav">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
          <li class="nav-item">
            <a class="nav-link" href="index.php">
              <i class="fa-solid fa-gauge-high me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="to-do-list.php">
              <i class="fa-regular fa-square-check me-1"></i>To-do
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="calendar.php">
              <i class="fa-regular fa-calendar me-1"></i>Calendar
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="notes.php">
              <i class="fa-regular fa-note-sticky me-1"></i>Notes
            </a>
          </li>
        </ul>


        <div class="d-flex align-items-center gap-2">

          <div class="dropdown">
            <a class="dropdown-item text-danger" href="logout.php">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>