<?php
require_once 'config.php';
$error = '';
$success = true;
$email_error= '';
$password_error= '';
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // changable (add validation)
    // 1. Sanitize user input
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    //print_r($_POST);
    if (empty($email)){
      $email_error = 'Email is required.';
      $success = false;
    }
    if (empty($password)){
      $password_error = 'Password is required.';
      $success = false;
    }
    if ($success){
    // Query to fetch the admin row based only on email, including the new 'status' column
    $query = "SELECT id, email, password_hash, country_code, created_at FROM users WHERE email = ?";
    
    // Prepare the statement
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt === false) {
        die('Prepare failed: ' . mysqli_error($conn));
    }
    
    // Bind the email parameter (s = string)
    mysqli_stmt_bind_param($stmt, 's', $email);
    
    // Execute the statement
    mysqli_stmt_execute($stmt);
    
    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // 2. Verify Password securely (assuming passwords are hashed with password_hash())
        if (password_verify($password, $user['password_hash'])) {
            
            // Login successful: Set session variables including the status
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            // 3. Check the status for redirection

                redirect('dash.php'); // Admin role: Go to dashboard
           
                // Non-admin role or other users: Go back to the index/home page
            
            
        } else {
            // Password mismatch
            $error = 'Invalid email or password!';
        }
    } else {
        // email not found
        $error = 'Invalid email or password!';
    }
    
    // Close the statement
    mysqli_stmt_close($stmt);}
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TaskMate — Login</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    body {
      background: #f6f7fb;
    }

    .card {
      border: 0;
      border-radius: 16px;
    }

    .shadow-soft {
      box-shadow: 0 10px 30px rgba(0,0,0,.06);
    }

    .text-muted-2 {
      color: #6b7280;
    }

    .form-control:focus {
      box-shadow: 0 0 0 .2rem rgba(17, 24, 39, .08);
      border-color: rgba(17, 24, 39, .25);
    }

    .logo-badge {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #111827;
      color: #fff;
    }
    span[id$="_error"] {
  display: inline-block;
  margin-left: 6px;
  font-size: 0.85rem;
  color: #dc2626;
  font-weight: 500;
}
  </style>
</head>

<body class="d-flex flex-column min-vh-100">
<?php include ("credentialsheader.php");?>

  <!-- Top navbar -->

  <!-- Main content -->
  <main class="flex-grow-1 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
          <div class="card shadow-soft">
            <div class="card-body p-4 p-md-5">

              <div class="mb-4 text-center">
                <h3 class="mb-1">Login</h3>
                <div class="text-muted-2">
                  Enter your email and password to continue.
                </div>
              </div>
              <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

              <form id="login-form" method="POST" action="">
                <!-- Email -->
                <div class="mb-3">
                  <label class="form-label small text-muted-2">Email address</label>
                  <span id="email_error"><?php echo $email_error;?></span>
                  <input
                    class="form-control form-control-lg"
                    placeholder="name@example.com"
                    id="form-email"
                    name="email"
                    required
                  />
                </div>

                <!-- Password -->
                <div class="mb-3">
                  <label class="form-label small text-muted-2">Password</label>
                  <span id="password_error"><?php echo $password_error;?></span>
                  <input
                    type="password"
                    class="form-control form-control-lg"
                    placeholder="Enter your password"
                    id="form-password"
                    name="password"
                    required
                  />
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-dark btn-lg">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Login
                  </button>
                </div>

                <div class="text-center mt-3 small">
                  Don’t have an account?
                  <a href="signup.php" class="fw-semibold text-decoration-none">Register</a>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->

<?php include ("footer.php");?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const form = document.getElementById("login-form");
    const email_error = document.getElementById("email_error");
    const password_error = document.getElementById("password_error");
    form.addEventListener('submit', function(event){
      event.preventDefault();
      const email = document.getElementById("form-email").value.trim();
      const password = document.getElementById("form-password").value.trim();
      email_error.textContent = "";
      password_error.textContent = "";

      let valid = true;

      if (!email) {
        email_error.textContent = "Email is required.";
        valid = false;
      }

      if (!password) {
        password_error.textContent = "Password is required.";
        valid = false;
      }
      if (!valid) return;

      form.submit();
    })
  </script>
</body>
</html>
