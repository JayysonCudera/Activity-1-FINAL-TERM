<?php
    require_once "config.php";

    function h($str){
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    $errors = [];

    if($_SERVER['REQUEST_METHOD'] === "POST") {
        $uname = trim($_POST['uname'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param('s', $uname);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if($user && password_verify($password, $user['password'])){
            if($user['status'] !== "approved"){
                $errors[] = "Your account has not been approved by the admin yet.";
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header('Location: dashboard.php');
                exit;
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | High Heaven Pavillion</title>
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

        <h2>Sign in to your Account</h2>

        <?php foreach ($errors as $e): ?>
            <p class="msg error"><?= h($e) ?></p>
        <?php endforeach; ?>

        <form action="" method="post">
            <label for="uname">Username</label>
            <input type="text" name="uname" id="uname" placeholder="Enter username" value="<?= h($_POST['uname'] ?? '') ?>" required autofocus>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required>

            <input type="submit" value="Sign In">
        </form>

        <p class="switch">No account yet? <a href="register.php">Register here</a></p>
    </main>
</body>
</html>
