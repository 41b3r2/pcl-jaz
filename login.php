<?php
// Start session
session_start();

// Include database connection
require_once 'connection.php';

// Initialize variables
$email = $password = "";
$emailErr = $passwordErr = $loginErr = "";
$success = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }
    
    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // If no validation errors, attempt login
    if (empty($emailErr) && empty($passwordErr)) {
        // Prepare a statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT emp_id, fullname, email, password, u_id FROM employee WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            // IMPORTANT: In a production environment, use password_verify() for secure password checking
            // This is a security vulnerability, but keeping as-is if this is legacy code
            if ($password == $row["password"]) {
                // Password is correct, start a new session
                session_regenerate_id(true); // Added true parameter for better security
                
                // Store session data
                $_SESSION["loggedin"] = true;
                $_SESSION["emp_id"] = $row["emp_id"];
                $_SESSION["fullname"] = $row["fullname"];
                $_SESSION["email"] = $row["email"];
                $_SESSION["u_id"] = $row["u_id"];
                
                // Set success flag to trigger loading screen
                $success = true;
            } else {
                $loginErr = "Invalid email or password";
            }
        } else {
            $loginErr = "Invalid email or password";
        }
        
        $stmt->close();
    }
}

// Close connection only if it exists and is open
if (isset($conn) && $conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL Fleet Ledger Login</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .logo-container img {
            width: 120px;
            height: 120px;
            margin-right: 10px;
        }

        .logo-container h1 {
            color: red;
            font-size: 3.5rem;
            font-weight: bold;
            margin: 0;
        }

        .login-container {
            background: linear-gradient(to right, #6B0000 0%, #B00000 51%, #E80202 76%, #B00000 100%);
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .input-container {
            position: relative;
            margin-bottom: 15px;
        }

        .login-container input {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 1000px;
            outline: none;
            background-color: rgba(216, 216, 216, 1);
            box-sizing: border-box;
        }

        .login-container input[type="email"] {
            background-position: right 10px center !important;
            background-repeat: no-repeat !important;
            background-size: 25px !important;
            padding-right: 50px !important;
        }

        .login-container input[type="password"] {
            background-position: right 13px center !important;
            background-repeat: no-repeat !important;
            background-size: 25px !important;
            padding-right: 40px !important;
        }

        .login-container a {
            display: block;
            text-align: left;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            color: white;
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .login-button {
            background-color: white;
            color: black;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
        }

        .login-button:hover {
            background-color: #f0f0f0;
            transform: scale(1.05);
        }

        .error {
            color: #ffcccc;
            font-size: 14px;
            text-align: left;
            margin-left: 25px;
        }

        .alert {
            background-color: rgba(255, 0, 0, 0.2);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Loading Screen Styles */
        .loading-screen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            font-size: 24px;
            font-weight: bold;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid red;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin-right: 15px;
        }
        .input-wrapper {
            position: relative;
            width: 90%;
            margin: 0 auto;
        }

        .input-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 25px;
            height: 25px;
            pointer-events: none; /* Para hindi ito maging clickable */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .logo-container {
                flex-direction: column;
                text-align: center;
            }
            
            .logo-container img {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .logo-container h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            
            .logo-container img {
                width: 90px;
                height: 90px;
            }
            
            .logo-container h1 {
                font-size: 2rem;
            }
            
            .login-container {
                padding: 20px 15px;
            }
            
            .login-container input {
                padding: 12px;
            }
            
            .login-container a {
                font-size: 12px;
                margin-left: 15px;
            }
            
            .login-button {
                padding: 10px 25px;
                font-size: 14px;
            }
        }

        @media (max-height: 600px) {
            .logo-container img {
                width: 80px;
                height: 80px;
            }
            
            .logo-container h1 {
                font-size: 1.8rem;
            }
            
            .logo-container {
                margin-bottom: 1rem;
            }
            
            .login-container {
                padding: 15px;
            }
            
            .input-container {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Check if video file exists before trying to load it -->
    <video autoplay muted loop class="video-background">
        <source src="assets/vid/clip1.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="loading-screen" id="loading-screen">
        <div class="loader"></div>
        <span>Loading...</span>
    </div>

    <div class="container">
        <div class="logo-container">
            <img src="assets/img/pcl.png" alt="PCL Logo">
            <h1>FLEET LEDGER</h1>
        </div>
        
        <?php if (!empty($loginErr)): ?>
            <div class="alert"><?php echo htmlspecialchars($loginErr); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
            <div class="login-container">
            <div class="input-container">
    <div class="input-wrapper">
        <input type="email" placeholder="Email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <img src="assets/img/log_icon.png" class="input-icon" alt="Email icon">
    </div>
    <span class="error"><?php echo $emailErr; ?></span>
</div>

<div class="input-container">
    <div class="input-wrapper">
        <input type="password" placeholder="Password" id="password" name="password">
        <img src="assets/img/pas_icon.png" class="input-icon" alt="Password icon">
    </div>
    <span class="error"><?php echo $passwordErr; ?></span>
</div>

                <a href="forgot-password.php" id="forgotLink">Forgot Password?</a>
                <button class="login-button" type="submit">Login</button>
            </div>
        </form>
    </div>

    <?php if ($success): ?>
    <script>
        // Show loading screen when login is successful
        document.addEventListener('DOMContentLoaded', function() {
            // Show the loading screen
            document.getElementById("loading-screen").style.display = "flex";
            
            // Redirect after 1 second
            setTimeout(function() {
                window.location.href = "landingPage.php";
            }, 1000);
        });
        document.addEventListener('DOMContentLoaded', function() {
    // Manually set the styles after page loads
    let emailInput = document.querySelector('input[type="email"]');
    let passwordInput = document.querySelector('input[type="password"]');
    
    if (emailInput) {
        emailInput.style.backgroundImage = "url('assets/img/log_icon.png')";
        emailInput.style.backgroundPosition = "right 10px center";
        emailInput.style.backgroundRepeat = "no-repeat";
        emailInput.style.backgroundSize = "25px";
        emailInput.style.paddingRight = "40px";
    }
    
    if (passwordInput) {
        passwordInput.style.backgroundImage = "url('assets/img/pas_icon.png')";
        passwordInput.style.backgroundPosition = "right 13px center";
        passwordInput.style.backgroundRepeat = "no-repeat";
        passwordInput.style.backgroundSize = "25px";
        passwordInput.style.paddingRight = "40px";
    }
});
    </script>
    <?php endif; ?>
</body>
</html>