<?php
require_once 'config.php';
$error = '';
$email_error= '';
$password_error= '';
$confirmpassword_error = '';
$country_error = '';
$country_code = '';
$success = true;
$form_data = []; // Array to hold POST data to repopulate the form

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitize user input (always trim and filter)
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $country_code = trim($_POST['country_code']);
    
    // Store POST data to repopulate form on error
    $form_data = [
        'email' => $email,
  'country_code' => $country_code
    ];
    $allowed_codes =  [
  'AF','AL','DZ','AD','AO','AG','AR','AM','AU','AT','AZ','BS','BH','BD','BB',
  'BY','BE','BZ','BJ','BT','BO','BA','BW','BR','BN','BG','BF','BI','KH','CM',
  'CA','CV','CF','TD','CL','CN','CO','KM','CG','CR','CI','HR','CU','CY','CZ',
  'DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FJ','FI','FR','GA',
  'GM','GE','DE','GH','GR','GD','GT','GN','GW','GY','HT','HN','HU','IS','IN',
  'ID','IR','IQ','IE','IL','IT','JM','JP','JO','KZ','KE','KW','KG','LA','LV',
  'LB','LS','LR','LY','LI','LT','LU','MG','MW','MY','MV','ML','MT','MR','MU',
  'MX','MD','MN','ME','MA','MZ','MM','NA','NP','NL','NZ','NI','NE','NG','NO',
  'OM','PK','PA','PG','PY','PE','PH','PL','PT','QA','RO','RU','RW','SA','SN',
  'RS','SG','SK','SI','SO','ZA','ES','LK','SD','SE','CH','SY','TW','TJ','TZ',
  'TH','TN','TR','UG','UA','AE','GB','US','UY','UZ','VE','VN','YE','ZM','ZW'
];


if (empty($country_code)) {
  $country_error = 'Country is required.';
  $success = false;
} elseif (!in_array($country_code, $allowed_codes, true)) {
  $country_error = 'Invalid country code.';
  $success = false;
}

    if (empty($email)){
      $email_error = 'Email is required.';
      $success = false;
    }
    if (empty($password)){
      $password_error = 'Password is required.';
      $success = false;
    }
    if (empty($confirm_password)){
      $confirmpassword_error = 'Please confirm your password';
      $success = false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)){
      $email_error='This email is invalid.';
      $success = false;
    }


    if (($password != $confirm_password) && !empty($password) && !empty($confirm_password)){
      $confirmpassword_error = 'Your passwords do not match';
      $success = false;
    }
    $passwordRegex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()\-.+]).{8,20}$/';
    if (!preg_match($passwordRegex,$password) && !empty($password))
    {
      $password_error = 'Your Password is not strong enough';
      $success = false;
    }

    
    
    // Validation
    // if ( empty($email) || empty($password) || empty($confirm_password)) {
    //     $error = 'All fields are required!';
    // } elseif ($password !== $confirm_password) {
    //     $error = 'Passwords do not match!';
    //     // changable
    // } elseif (strlen($password) < 6) {
    //     $error = 'Password must be at least 6 characters!';
    // } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     $error = 'Invalid email format!';
    if ($success){
        // --- 2. Check if username or email already exists using Prepared Statements ---
        
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt_check = mysqli_prepare($conn, $check_query);
        
        if ($stmt_check === false) {
            $error = 'Database check failed: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt_check, 's', $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $email_error = 'email already exists!';
                mysqli_stmt_close($stmt_check);
            } else {
                mysqli_stmt_close($stmt_check);
                
                // --- 3. Securely Hash Password ---
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // --- 4. Insert new user and auto-login ---
                $insert_query = "INSERT INTO users (email, password_hash, country_code) VALUES (?, ?, ?)";

                                 
                $stmt_insert = mysqli_prepare($conn, $insert_query);
                
                if ($stmt_insert === false) {
                    $error = 'Database insert failed: ' . mysqli_error($conn);
                } else {
mysqli_stmt_bind_param($stmt_insert, 'sss', $email, $hashed_password, $country_code);
                    
                    if (mysqli_stmt_execute($stmt_insert)) {
                        
                        // --- CRITICAL: Get the ID and set session for auto-login ---
                        $new_user_id = mysqli_insert_id($conn);
                        $yearNow = (int)date('Y');
                         
$holidays = $holiday_api->holidays([
    'country' => $country_code,
    'year'    => '2025',
    'public'  => true,
]);
// current year
seedUserHolidays($conn, $new_user_id, $holidays, $yearNow);


seedUserHolidays($conn, $new_user_id, $holidays, $yearNow + 1);

                        $_SESSION['id'] = $new_user_id;
                        
                        mysqli_stmt_close($stmt_insert);
                        
                        // Redirect user to the home page immediately
                        redirect('dash.php'); 
                        
                    } else {
                        $error = 'Registration failed: ' . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
        }
    }
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
                <h3 class="mb-1">Register Your Account</h3>
              </div>
              <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        

              <form id="signup-form" method="POST" action="">
                <!-- Email -->
                <div class="mb-3">
                  <label class="form-label small text-muted-2">Email address</label><span id="email_error"><?php echo $email_error;?></span>
                  <input
                    class="form-control form-control-lg"
                    placeholder="name@example.com"
                    id="form-email"
                    name ="email"
                    required
                    value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                  />
                </div>
                <!-- Country -->
<div class="mb-3">
  <label class="form-label small text-muted-2">Country (ISO code)</label>
  <span id="country_error"><?php echo $country_error; ?></span>

  <input
    class="form-control form-control-lg"
    list="country-codes"
    name="country_code"
    id="form-country"
    placeholder="e.g. YE, SA, US"
    required
    value="<?php echo htmlspecialchars($form_data['country_code'] ?? ''); ?>"
  />

<datalist id="country-codes">
  <option value="AF">Afghanistan</option>
  <option value="AL">Albania</option>
  <option value="DZ">Algeria</option>
  <option value="AD">Andorra</option>
  <option value="AO">Angola</option>
  <option value="AG">Antigua and Barbuda</option>
  <option value="AR">Argentina</option>
  <option value="AM">Armenia</option>
  <option value="AU">Australia</option>
  <option value="AT">Austria</option>
  <option value="AZ">Azerbaijan</option>
  <option value="BS">Bahamas</option>
  <option value="BH">Bahrain</option>
  <option value="BD">Bangladesh</option>
  <option value="BB">Barbados</option>
  <option value="BY">Belarus</option>
  <option value="BE">Belgium</option>
  <option value="BZ">Belize</option>
  <option value="BJ">Benin</option>
  <option value="BT">Bhutan</option>
  <option value="BO">Bolivia</option>
  <option value="BA">Bosnia and Herzegovina</option>
  <option value="BW">Botswana</option>
  <option value="BR">Brazil</option>
  <option value="BN">Brunei</option>
  <option value="BG">Bulgaria</option>
  <option value="BF">Burkina Faso</option>
  <option value="BI">Burundi</option>
  <option value="KH">Cambodia</option>
  <option value="CM">Cameroon</option>
  <option value="CA">Canada</option>
  <option value="CV">Cape Verde</option>
  <option value="CF">Central African Republic</option>
  <option value="TD">Chad</option>
  <option value="CL">Chile</option>
  <option value="CN">China</option>
  <option value="CO">Colombia</option>
  <option value="KM">Comoros</option>
  <option value="CG">Congo</option>
  <option value="CR">Costa Rica</option>
  <option value="CI">Côte d’Ivoire</option>
  <option value="HR">Croatia</option>
  <option value="CU">Cuba</option>
  <option value="CY">Cyprus</option>
  <option value="CZ">Czech Republic</option>
  <option value="DK">Denmark</option>
  <option value="DJ">Djibouti</option>
  <option value="DM">Dominica</option>
  <option value="DO">Dominican Republic</option>
  <option value="EC">Ecuador</option>
  <option value="EG">Egypt</option>
  <option value="SV">El Salvador</option>
  <option value="GQ">Equatorial Guinea</option>
  <option value="ER">Eritrea</option>
  <option value="EE">Estonia</option>
  <option value="ET">Ethiopia</option>
  <option value="FJ">Fiji</option>
  <option value="FI">Finland</option>
  <option value="FR">France</option>
  <option value="GA">Gabon</option>
  <option value="GM">Gambia</option>
  <option value="GE">Georgia</option>
  <option value="DE">Germany</option>
  <option value="GH">Ghana</option>
  <option value="GR">Greece</option>
  <option value="GD">Grenada</option>
  <option value="GT">Guatemala</option>
  <option value="GN">Guinea</option>
  <option value="GW">Guinea-Bissau</option>
  <option value="GY">Guyana</option>
  <option value="HT">Haiti</option>
  <option value="HN">Honduras</option>
  <option value="HU">Hungary</option>
  <option value="IS">Iceland</option>
  <option value="IN">India</option>
  <option value="ID">Indonesia</option>
  <option value="IR">Iran</option>
  <option value="IQ">Iraq</option>
  <option value="IE">Ireland</option>
  <option value="IL">Israel</option>
  <option value="IT">Italy</option>
  <option value="JM">Jamaica</option>
  <option value="JP">Japan</option>
  <option value="JO">Jordan</option>
  <option value="KZ">Kazakhstan</option>
  <option value="KE">Kenya</option>
  <option value="KW">Kuwait</option>
  <option value="KG">Kyrgyzstan</option>
  <option value="LA">Laos</option>
  <option value="LV">Latvia</option>
  <option value="LB">Lebanon</option>
  <option value="LS">Lesotho</option>
  <option value="LR">Liberia</option>
  <option value="LY">Libya</option>
  <option value="LI">Liechtenstein</option>
  <option value="LT">Lithuania</option>
  <option value="LU">Luxembourg</option>
  <option value="MG">Madagascar</option>
  <option value="MW">Malawi</option>
  <option value="MY">Malaysia</option>
  <option value="MV">Maldives</option>
  <option value="ML">Mali</option>
  <option value="MT">Malta</option>
  <option value="MR">Mauritania</option>
  <option value="MU">Mauritius</option>
  <option value="MX">Mexico</option>
  <option value="MD">Moldova</option>
  <option value="MN">Mongolia</option>
  <option value="ME">Montenegro</option>
  <option value="MA">Morocco</option>
  <option value="MZ">Mozambique</option>
  <option value="MM">Myanmar</option>
  <option value="NA">Namibia</option>
  <option value="NP">Nepal</option>
  <option value="NL">Netherlands</option>
  <option value="NZ">New Zealand</option>
  <option value="NI">Nicaragua</option>
  <option value="NE">Niger</option>
  <option value="NG">Nigeria</option>
  <option value="NO">Norway</option>
  <option value="OM">Oman</option>
  <option value="PK">Pakistan</option>
  <option value="PA">Panama</option>
  <option value="PG">Papua New Guinea</option>
  <option value="PY">Paraguay</option>
  <option value="PE">Peru</option>
  <option value="PH">Philippines</option>
  <option value="PL">Poland</option>
  <option value="PT">Portugal</option>
  <option value="QA">Qatar</option>
  <option value="RO">Romania</option>
  <option value="RU">Russia</option>
  <option value="RW">Rwanda</option>
  <option value="SA">Saudi Arabia</option>
  <option value="SN">Senegal</option>
  <option value="RS">Serbia</option>
  <option value="SG">Singapore</option>
  <option value="SK">Slovakia</option>
  <option value="SI">Slovenia</option>
  <option value="SO">Somalia</option>
  <option value="ZA">South Africa</option>
  <option value="ES">Spain</option>
  <option value="LK">Sri Lanka</option>
  <option value="SD">Sudan</option>
  <option value="SE">Sweden</option>
  <option value="CH">Switzerland</option>
  <option value="SY">Syria</option>
  <option value="TW">Taiwan</option>
  <option value="TJ">Tajikistan</option>
  <option value="TZ">Tanzania</option>
  <option value="TH">Thailand</option>
  <option value="TN">Tunisia</option>
  <option value="TR">Turkey</option>
  <option value="UG">Uganda</option>
  <option value="UA">Ukraine</option>
  <option value="AE">United Arab Emirates</option>
  <option value="GB">United Kingdom</option>
  <option value="US">United States</option>
  <option value="UY">Uruguay</option>
  <option value="UZ">Uzbekistan</option>
  <option value="VE">Venezuela</option>
  <option value="VN">Vietnam</option>
  <option value="YE">Yemen</option>
  <option value="ZM">Zambia</option>
  <option value="ZW">Zimbabwe</option>
</datalist>


  <div class="form-text text-muted-2">Start typing a country name or code.</div>
</div>


                <!-- Password -->
                <div class="mb-3">
                  <label class="form-label small text-muted-2">Password</label><span id="password_error"><?php echo $password_error;?></span>
                  <input
                    type="password"
                    class="form-control form-control-lg"
                    placeholder="Enter your password"
                    id="form-password"
                    name="password"
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label small text-muted-2">Confirm Password</label><span id="confirmpassword_error"><?php echo $confirmpassword_error;?></span>
                  <input
                    type="password"
                    class="form-control form-control-lg"
                    placeholder="Confirm your password"
                    id="form-confirmpass"
                    name="confirm_password"
                    required
                  />
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-dark btn-lg">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Register
                  </button>
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
<!-- Enables bootstrap js (e.g. mobile toggle button for hamburger menu) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const form = document.getElementById("signup-form");
    const email_error = document.getElementById("email_error");
    const password_error = document.getElementById("password_error");
    const confirmpassword_error = document.getElementById("confirmpassword_error");\
    const country_error = document.getElementById("country_error");


    form.addEventListener('submit', function(event){
      event.preventDefault();
      const email = document.getElementById("form-email").value.trim();
      const password = document.getElementById("form-password").value.trim();
      const confirm_password = document.getElementById("form-confirmpass").value.trim();
      const country_code = document.getElementById("form-country").value.trim().toUpperCase();
      email_error.textContent = "";
      password_error.textContent = "";
      country_error.textContent = "";
      confirmpassword_error.textContent="";
      let valid = true;
      const allowed = [
  'AF','AL','DZ','AD','AO','AG','AR','AM','AU','AT','AZ','BS','BH','BD','BB',
  'BY','BE','BZ','BJ','BT','BO','BA','BW','BR','BN','BG','BF','BI','KH','CM',
  'CA','CV','CF','TD','CL','CN','CO','KM','CG','CR','CI','HR','CU','CY','CZ',
  'DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FJ','FI','FR','GA',
  'GM','GE','DE','GH','GR','GD','GT','GN','GW','GY','HT','HN','HU','IS','IN',
  'ID','IR','IQ','IE','IL','IT','JM','JP','JO','KZ','KE','KW','KG','LA','LV',
  'LB','LS','LR','LY','LI','LT','LU','MG','MW','MY','MV','ML','MT','MR','MU',
  'MX','MD','MN','ME','MA','MZ','MM','NA','NP','NL','NZ','NI','NE','NG','NO',
  'OM','PK','PA','PG','PY','PE','PH','PL','PT','QA','RO','RU','RW','SA','SN',
  'RS','SG','SK','SI','SO','ZA','ES','LK','SD','SE','CH','SY','TW','TJ','TZ',
  'TH','TN','TR','UG','UA','AE','GB','US','UY','UZ','VE','VN','YE','ZM','ZW'
];

 

      if (!allowed.has(country_code)) {
  country_error.textContent = "Please choose a valid country from the list.";
  valid = false;
}
      if (!email) {
        email_error.textContent = "Email is required.";
        valid = false;
      }

      if (!password) {
        password_error.textContent = "Password is required.";
        valid = false;
      }
      if (!confirm_password) {
        confirmpassword_error.textContent = "Please confirm your password";
        valid = false;
      }
      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailRegex.test(email) && email) {
        email_error.textContent = "This email is invalid.";
        valid = false;
      }
      if (!(password==confirm_password) && password && confirm_password){
        confirmpassword_error.textContent = "Your passwords do not match";
        valid = false;
      }
      const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()\-.+]).{8,20}$/;
      if(!passwordRegex.test(password) && password){
        password_error.textContent = "Your password is not strong enough";
        valid = false;
      }
      if (!country_code) {
  country_error.textContent = "Country is required.";
  valid = false;
} else if (!/^[A-Z]{2}$/.test(country_code)) {
  country_error.textContent = "Use a 2-letter ISO code (e.g. YE).";
  valid = false;
}

        if (!valid) return;
      form.submit();
    })
  </script>
</body>
</html>
