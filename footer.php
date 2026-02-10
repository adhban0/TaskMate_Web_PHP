  <footer class="border-top bg-white">
  <div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold">TaskMate</span>
        <span class="text-muted-2 small">© <span id="tmYear"></span> All rights reserved.</span>
      </div>



      <div class="small text-muted-2">
        Made for productivity
      </div>
    </div>
  </div>
</footer>

<script>
  document.getElementById("tmYear").textContent = new Date().getFullYear();
</script>
