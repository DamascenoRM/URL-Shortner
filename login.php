<?php
include('./config/config.php');
include_once('./lib/Database.php');
include('./lib/Authenticator.php');
if ($DEBUG){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
$database = new Database();
$authenticator = new Authenticator($database);

if ($authenticator->isAuthenticated()) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $authenticator->logout();
        header("Location: ./login.php");
        exit();
    }
    header("Location: ./index.php"); // Redireciona para a página de criação de links se já estiver autenticado
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $authenticator->authenticate($username, $password);

    if ($authenticator->isAuthenticated()) {
        header("Location: ./index.php");
        exit();
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Adicionando Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1,h2 {
            color: #007bff;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #dc3545;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-around">
        <div>
            <?php echo "<a href='" . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "' class='text-reset text-decoration-none'>"; ?>
                <h1 class="mt-4 translate-middle top-0">URL Shortener</h1>
            <?php echo "</a>"; ?>
            <h2 class="mt-4 translate-middle top-0">Login</h2>
        </div>
        <div>
            <a href="login.php?action=logout" class="btn btn-outline-secondary"><i class="bi bi-power"></i> Logout</a>
            <a href="./settings.php" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Settings</a>
        </div>
        </div>
        <form method="post">
            <div class="mb-3">
        <?php if (isset($error)) { echo "<p class='alert alert-danger'>$error</p>"; } ?>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <!-- Adicionando Bootstrap JS (opcional, dependendo das funcionalidades que você deseja usar) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>