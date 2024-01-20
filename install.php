<?php
$configfile = './config/config.php';
if ($DEBUG){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
session_start();
if (file_exists($configfile)) {
    $_SESSION = null;
    $_POST = null;
    session_unset(); // Limpar a sessão após a conclusão
    session_destroy();
    header("Location: ./"); // Redireciona para a página de login
    exit();
}
$message = null;
$nextstepname = "Database Connection";

$_SESSION['message'] = $message;


function createConfigFile(){
    // Sanitize user input
    $_SESSION['db_host'] = filter_input(INPUT_POST, 'db_host', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['db_user'] = filter_input(INPUT_POST, 'db_user', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['db_password'] = filter_input(INPUT_POST, 'db_password', FILTER_SANITIZE_SPECIAL_CHARS);
    $_SESSION['db_name'] = filter_input(INPUT_POST, 'db_name', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($_SESSION['db_host']) || empty($_SESSION['db_user']) || empty($_SESSION['db_password']) || empty($_SESSION['db_name'])) {
        $_SESSION['message'] = "Invalid input data!";
        return false;
    }

    try {
        $connection = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_password'],  $_SESSION['db_name']);

        if ($connection->connect_error) {
            $_SESSION['message'] = "Connection failed: " . $connection->connect_error;
            return false;
        }

        $connection->select_db($_SESSION['db_name']);

        $configFileContent = <<<EOD
<?php
define('DB_HOST', '{$_SESSION['db_host']}');
define('DB_USER', '{$_SESSION['db_user']}');
define('DB_PASSWORD', '{$_SESSION['db_password']}');
define('DB_NAME', '{$_SESSION['db_name']}');
?>
EOD;

        file_put_contents('./config/config.php', $configFileContent);

        createTables($connection);
        
        // Close the connection
        $connection->close();

        return true;

    } catch (Exception $e) {
        $_SESSION['message'] = "An error occurred: " . $e->getMessage();
        return false;
    }
}

function createTables($connection){
    $createUsersTableQuery = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_active BOOLEAN DEFAULT 0,
        is_admin BOOLEAN DEFAULT 0,
        last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$connection->query($createUsersTableQuery)) {
        $_SESSION['message'] = "Error creating users table: " . $connection->error;
        return false;
    }

    $createUserTokenTableQuery = "CREATE TABLE IF NOT EXISTS users_token (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$connection->query($createUserTokenTableQuery)) {
        $_SESSION['message'] = "Error creating user_token table: " . $connection->error;
        return false;
    }

    $createLinksTableQuery = "CREATE TABLE IF NOT EXISTS links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        original_url VARCHAR(255) NOT NULL,
        short_url_key VARCHAR(50) NOT NULL,
        access INT DEFAULT 0,
        owner INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner) REFERENCES users(id) ON DELETE CASCADE
    )";
    if (!$connection->query($createLinksTableQuery)) {
        $_SESSION['message'] = "Error creating links table: " . $connection->error;
        return false;
    }
    $createSiteParametersTableQuery = "CREATE TABLE IF NOT EXISTS site_parameters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parameter_key VARCHAR(255) NOT NULL UNIQUE,
        parameter_value VARCHAR(255) NOT NULL,
        last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

if (!$connection->query($createSiteParametersTableQuery)) {
    $_SESSION['message'] = "Error creating site parameters table: " . $connection->error;
    return false;
}

    return true;
}

function createUser(){
    $_SESSION['adminName'] = filter_input(INPUT_POST, 'adminName', FILTER_SANITIZE_SPECIAL_CHARS);
    $defaultPassword = password_hash(filter_input(INPUT_POST, 'adminPassword', FILTER_SANITIZE_SPECIAL_CHARS), PASSWORD_DEFAULT);
    
    
    if (empty($_SESSION['db_host']) || empty($_SESSION['db_user']) || empty($_SESSION['db_password']) || empty($_SESSION['db_name'])) {
        $_SESSION['message'] = "Internal error, retry!";
        return false;
    }
    if (empty($defaultPassword) || empty($_SESSION['adminName'])) {
        $_SESSION['message'] = "Invalid input data!";
        return false;
    }

    try {
        $connection = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_password'],  $_SESSION['db_name']);

        if ($connection->connect_error) {
            $_SESSION['message'] = "Connection failed: " . $connection->connect_error;
            return false;
        }

        $connection->select_db($_SESSION['db_name']);

        $addDefaultUserQuery = "INSERT INTO users (username, password, is_active, is_admin) VALUES ('{$_SESSION['adminName']}', '$defaultPassword', 1, 1)";
        if (!$connection->query($addDefaultUserQuery)) {
            die("Error adding default user: " . $connection->error);
        }
        return true;

    } catch (Exception $e) {
        $_SESSION['message'] = "An error occurred: " . $e->getMessage();
        return false;
    }
}

function createParameters(){
    $_SESSION['site_name'] = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($_SESSION['db_host']) || empty($_SESSION['db_user']) || empty($_SESSION['db_password']) || empty($_SESSION['db_name'])) {
        $_SESSION['message'] = "Internal error, retry!";
        return false;
    }
    if (empty($_SESSION['site_name'])) {
        $_SESSION['message'] = "Invalid input data!";
        return false;
    }

    try {
        $connection = new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_password'],  $_SESSION['db_name']);

        if ($connection->connect_error) {
            $_SESSION['message'] = "Connection failed: " . $connection->connect_error;
            return false;
        }

        $connection->select_db($_SESSION['db_name']);

        $addParametersQuery = "INSERT INTO site_parameters (parameter_key, parameter_value) VALUES ('sitename', '{$_SESSION['site_name']}')";
        if (!$connection->query($addParametersQuery)) {
            die("Error adding site parameters: " . $connection->error);
        }
        return true;

    } catch (Exception $e) {
        $_SESSION['message'] = "An error occurred: " . $e->getMessage();
        return false;
    }
}

// Função para verificar se um passo foi concluído
function isStepCompleted($step) {
    return isset($_SESSION['steps'][$step]) && $_SESSION['steps'][$step] === true;
}

// Função para marcar um passo como concluído
function markStepCompleted($step) {
    $_SESSION['steps'] = $step + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_unset(); // Limpar a sessão após a conclusão
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar qual passo está sendo processado
    $currentStep = $_POST['current_step'] < 0 ? 0 : $_POST['current_step'];
    $_SESSION['message'] = isset($_POST['message']) ? $_POST['message'] : null;
    switch ($currentStep) {
        case 0:
            $nextstepname = "Database Connection";
            markStepCompleted($currentStep);
            break;
        case 1:
            if(createConfigFile()){
                $nextstepname = "Admin User";
                markStepCompleted($currentStep);
            }
            break;

        case 2:
            if(createUser()){
                $nextstepname = "Site Options";
                // Processar criação de tabelas
                markStepCompleted($currentStep);
            }
            break;

        case 3:
            if(createParameters()){
                 $nextstepname = "Security Options";
                // Processar criação de Parametros
                markStepCompleted($currentStep);
            }
            // Processar criação do perfil do usuário administrador
            markStepCompleted($currentStep);
            break;

        case 4:
            $nextstepname = "Finish";
            // Processar configurações do site
            // Implemente a lógica conforme necessário
            markStepCompleted($currentStep);
            break;

        case 5:
            $nextstepname = "Redirecting";
            // Página de sucesso
            $_SESSION = null;
            $_POST = null;
            session_unset(); // Limpar a sessão após a conclusão
            session_destroy();
            header("Location: ./"); // Redireciona para a página de login
            exit();
            break;

        default:
            showError("Invalid step");
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>

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

        .step-indicator {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .step {
            width: 20%;
            text-align: center;
            color: #007bff;
        }

        .step.completed {
            color: #28a745;
        }

        .step.current {
            font-weight: bold;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545; /* Cor da borda do input com erro */
            padding-right: calc(1.5em + 0.75rem); /* Espaçamento para o ícone de erro */
            background-image: url('path/to/error-icon.png'); /* Imagem do ícone de erro (substitua pelo caminho correto) */
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); /* Tamanho do ícone de erro */
        }
    </style>
</head>
<body>
    <div class="container>
        <div class="d-flex justify-content-around ">
            <div >
                <h1 class="mt-4">Installation</h1>
            </div>
        </div>
        <?php
        // Exibir a indicação do passo atual
        $currentStep = isset($_SESSION['steps']) ? $_SESSION['steps'] : 1;
        ?>

        <div class="step-indicator">
            <div class="step <?php echo $currentStep > 1 ? 'completed' : ''; ?>">1. Database Connection</div>
            <div class="step <?php echo $currentStep > 2 ? 'completed' : ''; ?>">2. Admin User</div>
            <div class="step <?php echo $currentStep > 3 ? 'completed' : ''; ?>">3. Site Options</div>
            <div class="step <?php echo $currentStep > 4 ? 'completed' : ''; ?>">4. Site Security</div>
            <div class="step <?php echo $currentStep > 5 ? 'completed' : ''; ?>">5. Finish</div>
        </div>
        <form method="post" onsubmit="validatePassword(event)">
            <h2 class="text-center"><?php echo $currentStep ?> . <?php echo $nextstepname ?></h2>
            <input type="hidden" name="current_step" value="<?php echo $currentStep ?>">
            <p class='<?php if(empty($_SESSION['message']) || strlen($_SESSION['message']) === 0 ){ echo "visually-hidden ";}?> error-message alert alert-danger' role='alert' id='msg_box'><?php echo "".$_SESSION['message'] ?></p>
        <?php
        // Exibir conteúdo do passo atual
        switch ($currentStep) {
            case 1:
                    // Conteúdo para o passo 1 (conexão com BD)
                    ?>
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host:</label>
                        <input type="text" name="db_host" id="db_host" class="form-control" value="<?php echo isset($_POST['db_host']) ? $_POST['db_host'] : null ; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database User:</label>
                        <input type="text" name="db_user" id="db_user" class="form-control" value="<?php echo isset($_POST['db_user']) ? $_POST['db_user'] : null ; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_password" class="form-label">Database Password:</label>
                        <input type="password" name="db_password" id="db_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name:</label>
                        <input type="text" name="db_name" id="db_name" class="form-control" value="<?php echo isset( $_POST['db_name']) ?  $_POST['db_name'] : null ; ?>" required>
                    </div>
                    <?php
                break;

            case 2:
                    ?>
                    <div class="mb-3">
                        <label for="adminName" class="form-label">Admin User:</label>
                        <input type="text" name="adminName" id="adminName" class="form-control" placeholder="Enter username for admin" value="<?php echo isset($_POST['adminName']) ? $_POST['adminName'] : null ; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="adminPassword">Password:</label>
                        <input type="password" class="form-control" id="adminPassword" name="adminPassword" placeholder="Enter password" onchange='validatePassword()' required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password:</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" onchange='validatePassword()' required>
                    </div>
                    <?php
                break;

            case 3:
                    ?>
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name:</label>
                        <input type="text" name="site_name" id="site_name" class="form-control" placeholder="Enter site name" value="<?php echo isset($_POST['site_name']) ? $_POST['site_name'] : null ; ?>" required>
                    </div>
                    <?php
                break;

            case 4:
                    ?>
                    <?php
                break;

            case 5:
                    // Página de sucesso
                    ?>
                    <p class='form-control alert alert-info' role='alert'>Installation completed successfully. Please remove the 'install.php' file for security.</p>
                    <?php
                break;

            default:
                showError("Invalid step");
                break;
        }
        ?>
            <button type="submit" class="btn btn-primary ">
                <?php
                    // Exibir a indicação do passo atual
                    if ($currentStep != 5){ echo "Next";}
                    else{echo "Finish";}
                ?>
            </button>
        </form>
    </div>

    <!-- Adicionando Bootstrap JS (opcional, dependendo das funcionalidades que você deseja usar) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        function validatePassword(event) {
            var password = document.getElementById("adminPassword");
            var confirmPassword = document.getElementById("confirmPassword");
            
            if (password.value !== confirmPassword.value) {
                changeText("Passwords do not match!");
                password.classList.toggle('is-invalid');
                confirmPassword.classList.toggle('is-invalid');
                event.preventDefault();
            }else{
                password.classList.remove('is-invalid');
                confirmPassword.classList.remove('is-invalid');
                changeText(null);
            }
        }
        function changeText(newText) {
            var msgBox = document.getElementById('msg_box');
            if (!newText){
                msgBox.textContent = newText;
                msgBox.classList.toggle('visually-hidden');
            }else{
                msgBox.classList.remove('visually-hidden');
            }
        }
    </script>

</body>
</html>
