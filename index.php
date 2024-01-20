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
$url_components =  $_SERVER['QUERY_STRING'];
$linkUpdater = new LinkUpdater($database, $authenticator);


if (!$authenticator->isAuthenticated() and empty($url_components) and !strlen($url_components) > 0) {
    header("Location: ./login.php"); // Redireciona para a página de login
    exit();
}else{
    if (isset($url_components)) {
        if (strlen($url_components) > 0 && $url_components !== null) {
            $db = new Database();
            $query = "SELECT original_url, access FROM links WHERE short_url_key = ? AND is_active = 1 ";
            
            $stmt = $db->prepareQuery($query);
            $stmt->bind_param("s", $url_components);
            $stmt->execute();
            $stmt->bind_result($originalUrl, $access_counter);
        
            if ($stmt->fetch()) {
                $stmt->close();
                
                //header("Location: $originalUrl");
                include('./templates/banner.php');
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '$originalUrl';
                    }, 3000);
                    </script>"
                ;
                $access_new = $access_counter + 1;
                $query = "UPDATE links SET access = ? WHERE short_url_key = ?";
                $stmt = $db->prepareQuery($query);
                $stmt->bind_param("ss", $access_new, $url_components);
                $stmt->execute();
                $stmt->close();
                $db->closeConnection();
                exit();
            } else {
                $stmt->close();
                $db->closeConnection();
                echo "Link not found.";
                exit();
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    
    <!-- Adicionando Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- CSS/Bootstrap-ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Adicionando DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.10/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Adicionando jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    

    <!-- Adicionando DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.10/js/jquery.dataTables.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>


    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1 {
            color: #007bff;
        }

        form.main-form {
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

        input[type="url"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        

        button,.paginate_button  {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .paginate_button {
            padding: 3px 6px;
            margin-right: 6px;
        }
        button:hover.paginate_button {
            background-color: #0056b3;
        }
        
       

    </style>
</head>
<body>
    <div class="d-flex justify-content-around">
        <div>
            <?php echo "<a href='" . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "' class='text-reset text-decoration-none'>"; ?>
                <h1 class="mt-4 translate-middle top-0">URL Shortener</h1>
            <?php echo "</a>"; ?>
        </div>
        <div>
            <a href="login.php?action=logout" class="btn btn-outline-secondary"><i class="bi bi-power"></i> Logout</a>
            <a href="./settings.php" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Settings</a>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        <form class='main-form' id="shortenForm" method="post">
            <div class="mb-3">
                <label for="original_url" class="form-label">Enter your URL:</label>
                <input type="url" name="original_url" id="original_url" class="form-control" required>
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary">Shorten URL</button>
            </div>                    
            <?php                  
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if(isset($_POST['original_url'])){
                        $originalUrl = $_POST['original_url'];
                
                        $db = new Database();
                        $query = "INSERT INTO links (original_url, short_url_key, owner, is_active) VALUES (?, ?, ?, ?)";
    
                        $shortUrlKey = substr(md5(time()), 0, 6);

                        // Use a variable to hold the owner value
                        $owner = $_SESSION['id'];
                        $is_active = 1;
                        $stmt = $db->prepareQuery($query);

                        // Pass the variable by reference in bind_param
                        $stmt->bind_param("ssii", $originalUrl, $shortUrlKey, $owner, $is_active);
                        $stmt->execute();
                        $stmt->close();

                        $shortenedUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/?' . $shortUrlKey;

                        echo "<hr><div class='alert alert-success'><div class='d-flex justify-content-center'><p class='alert-heading'>Your shortened link:</p></div><div class='d-flex justify-content-center'><a target='_blank' href='$shortenedUrl'>$shortenedUrl</a></div></div>";
                    }
                }
            ?>
        </form>
    </div>
    <?php                  
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if(isset($_POST['short_url_key']) && isset($_POST['short_action'])){
                        $originalUrl = $_POST['short_url_key'];
                        if (!$_POST['short_action']){
                            $linkUpdater->updateEnableLink($originalUrl);
                        }else{
                            $linkUpdater->updateDisableLink($originalUrl);
                        }
                    }
                }
                // Obtém os links do usuário autenticado
                $userId = $_SESSION['id'];
                $onlyowner = $authenticator->isAdmin() ? " OR 1=1" : "" ;
                $db = new Database();
                $query = "SELECT short_url_key, original_url, access, is_active, owner FROM links WHERE owner = ?" . $onlyowner;
                $stmt = $db->prepareQuery($query);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->bind_result($shortUrlKey, $originalUrl, $accessCounter, $isActive, $owner);

                $linkData = array();
                while ($stmt->fetch()) {
                    $isOwner = $owner == $userId ? true : false;
                    $linkData[] = array(
                        'short_url_key' => $shortUrlKey,
                        'original_url' => $originalUrl,
                        'access_counter' => $accessCounter,
                        'is_active' => $isActive,
                        'is_owner' => $isOwner,
                    );
                }
                $stmt->close();
            ?>
    <div class="table-responsive">
        <table id="linklist" class="table table-striped" style="width:100%" id="linkTable">
            <thead>
                <tr>
                    <th>Short URL</th>
                    <th>Original URL</th>
                    <th>Access Counter</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($linkData as $link): ?>
                    <tr>
                        <td><?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/?' . $link['short_url_key']; ?></td>
                        <td><?php echo $link['original_url']; ?></td>
                        <td><?php echo $link['access_counter']; ?></td>
                        <td><?php echo ($link['is_active'] ? 'Active' : 'Inactive'); ?></td>
                        <td><?php echo ($link['is_owner'] ? 'Is Owner' : 'Not Owner'); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="short_url_key" value="<?php echo $link['short_url_key']; ?>">
                                <input type="hidden" name="short_action" value="<?php echo ($link['is_active'] ? 1 : 0); ?>">
                                <button type="submit" class="btn btn-danger">
                                    <?php echo (!$link['is_active'] ? 'Activate' : 'Deactivate'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Adicionando jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Adicionando DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready( function () {
            $('#linklist').DataTable();
        } );
    </script>
    <a href="login.php?action=logout" class="btn btn-outline-secondary"><i class="bi bi-power"></i> Logout</a>
    <a href="./settings.php" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Settings</a>
    <!-- Adicionando Bootstrap JS (opcional, dependendo das funcionalidades que você deseja usar) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<?php
}
?>