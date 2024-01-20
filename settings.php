<?php
include('./lib/Database.php');
include('./config/config.php');
include('./lib/Authenticator.php');
include('./lib/LinkUpdater.php');
if ($DEBUG){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
$database = new Database();
$authenticator = new Authenticator($database);
$linkUpdater = new LinkUpdater($database, $authenticator);


if (!$authenticator->isAuthenticated()) {
    header("Location: ./login.php"); // Redireciona para a página de login
    exit();
}else{

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração do Sistema</title>

    <!-- Adicionando Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1, h2 {
            color: #007bff;
        }

        .nav-tabs {
            margin-bottom: 20px;
        }

        .tab-content {
            margin-top: 20px;
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
            <h2 class="mt-4 translate-middle top-0">Configuração do Sistema</h2>
        </div>
        <div>
            <a href="login.php?action=logout" class="btn btn-outline-secondary"><i class="bi bi-power"></i> Logout</a>
            <a href="./settings.php" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Settings</a>
        </div>
        </div>
        <!-- Abas de navegação -->
        <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item">
                <a class="nav-link active" id="user-config-tab" data-bs-toggle="tab" href="#user-config">Configurações do Usuário</a>
            </li>
            <?php 
                if ($authenticator->isAdmin()){
            ?> 
                <li class="nav-item">
                    <a class="nav-link" id="users-tab" data-bs-toggle="tab" href="#users">Gestão de Usuários</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="links-tab" data-bs-toggle="tab" href="#links">Gestão de Links</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="system-params-tab" data-bs-toggle="tab" href="#system-params">Parâmetros do Sistema</a>
                </li>
            <?php 
                }
            ?>

        </ul>

        <!-- Conteúdo das abas -->
        <div class="tab-content">
            <!-- Tab 1: Gestão de Usuários -->
            <div class="tab-pane fade" id="users">
                <!-- Seu conteúdo para gestão de usuários aqui -->
            </div>

            <!-- Tab 2: Gestão de Links -->
            <div class="tab-pane fade" id="links">
                <!-- Seu conteúdo para gestão de links aqui -->
            </div>

            <!-- Tab 3: Parâmetros do Sistema -->
            <div class="tab-pane fade" id="system-params">
                <!-- Seu conteúdo para parâmetros do sistema aqui -->
            </div>

            <!-- Tab 4: Configurações do Usuário -->
            <div class="tab-pane fade show active" id="user-config">
                <!-- Seu conteúdo para configurações do usuário aqui -->
            </div>
        </div>
    </div>

    <!-- Adicionando Bootstrap JS (opcional, dependendo das funcionalidades que você deseja usar) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        var myTabs = new bootstrap.Tab(document.getElementById('user-config-tab'));
        myTabs.show();
    </script>
</body>
</html>
<?php
}
?>