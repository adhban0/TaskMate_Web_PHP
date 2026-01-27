<footer class="border-top bg-white">
  <div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold">TaskMate</span>
        <span class="text-muted-2 small">© <span id="tmYear"></span> All rights reserved.</span>
      </div>

      <div class="d-flex flex-wrap justify-content-center gap-3 small">
        <a href="#" class="text-decoration-none text-muted-2">Dashboard</a>
        <a href="#" class="text-decoration-none text-muted-2">To-do</a>
        <a href="#" class="text-decoration-none text-muted-2">Calendar</a>
        <a href="#" class="text-decoration-none text-muted-2">Notes</a>
        <a href="#" class="text-decoration-none text-muted-2">Settings</a>
      </div>

      <div class="small text-muted-2">
        Made with <span aria-hidden="true">☕</span> for productivity
      </div>
    </div>
  </div>
</footer>

<script>
  // Footer year
  document.getElementById("tmYear").textContent = new Date().getFullYear();
</script>
