<?php
    require_once "config.php";

    function h($str){
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    $mess = [];
    $errors = [];
    $registered = false;

    if($_SERVER['REQUEST_METHOD'] === "POST") {
        $uname = trim($_POST['uname'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if($uname === '' || $password === ''){
            $errors[] = "All fields are required.";
        } elseif(strlen($uname) < 3 || strlen($uname) > 50){
            $errors[] = "Username must be 3 to 50 characters.";
        } elseif(strlen($password) < 8){
            $errors[] = "Password must be at least 8 characters.";
        } elseif($password !== $password2){
            $errors[] = "Passwords should match.";
        } else {
            try {
                $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
                $stmt->bind_param('s', $uname);
                $stmt->execute();
                $stmt->store_result();
                $exists = $stmt->num_rows > 0;
                $stmt->close();

                if($exists){
                    $errors[] = "That username is already taken.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $role = "user";
                    $status = "pending";

                    $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $uname, $hash, $role, $status);
                    $stmt->execute();

                    if($stmt->affected_rows === 1){
                        $mess[] = "Registered successfully! Please wait for the admin to approve your account.";
                        $registered = true;
                    } else {
                        $errors[] = "Something went wrong. Please try again later.";
                    }
                    $stmt->close();
                }
            } catch (mysqli_sql_exception $e) {
                error_log("Register failed: " . $e->getMessage());
                $errors[] = "Something went wrong. Please try again later.";
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | High Heaven Pavillion</title>
    <link rel="icon" type="image/svg+xml" href="assets/logo.svg">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="card">
        <div class="brand">
            <img src="assets/logo.svg" alt="High Heaven logo">
            <h1>High Heaven</h1>
            <p>Pavillion Coffee House</p>
        </div>

        <h2>Create your Account</h2>

        <?php foreach ($errors as $e): ?>
            <p class="msg error"><?= h($e) ?></p>
        <?php endforeach; ?>

        <?php foreach ($mess as $m): ?>
            <p class="msg success"><?= h($m) ?></p>
        <?php endforeach; ?>

        <form action="" method="post">
            <label for="uname">Username</label>
            <input type="text" name="uname" id="uname" placeholder="Enter username" value="<?= $registered ? '' : h($_POST['uname'] ?? '') ?>" required autofocus>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="At least 8 characters" required>

            <label for="password2">Confirm Password</label>
            <input type="password" name="password2" id="password2" placeholder="Confirm password" required>

            <input type="submit" value="Register">
        </form>

        <p class="switch">Already have an account? <a href="Sign_in.php">Sign in here</a></p>
    </main>
</body>
</html>
