<?php
require_once('./lib/Database.php');
require_once('./config/config.php');
require_once('./lib/Authenticator.php');

class LinkUpdater {
    private $database;
    private $authenticator;

    public function __construct(Database $database, Authenticator $authenticator) {
        $this->database = $database;
        $this->authenticator = $authenticator;
    }
    public function updateEnableLink($shortUrlKey) {
        if (!$this->authenticator->isAuthenticated()) {
            $this->accessDenied();
        }

        $userId = $_SESSION['id'];
        $query = "SELECT owner, is_active FROM links WHERE short_url_key = ?";
        $stmt = $this->database->prepareQuery($query);
        $stmt->bind_param("s", $shortUrlKey); // Changed "s" to "s" for string parameter
        $stmt->execute();
        $stmt->bind_result($owner, $isActive);

        if ($stmt->fetch()) {
            $stmt->close();

            // Verifica se o usuário tem permissão
            if ($this->authenticator->isAdmin() || $owner === $userId) {
                // Atualiza o link para inativo
                $this->updateActiveLinkStatus($shortUrlKey);
            } else {
                $this->accessDenied();
            }
        } else {
            $stmt->close();
            $this->accessDenied();
        }
    }
    
    public function updateDisableLink($shortUrlKey) {
        if (!$this->authenticator->isAuthenticated()) {
            $this->accessDenied();
        }

        $userId = $_SESSION['id'];
        $query = "SELECT owner, is_active FROM links WHERE short_url_key = ?";
        $stmt = $this->database->prepareQuery($query);
        $stmt->bind_param("s", $shortUrlKey); // Changed "s" to "s" for string parameter
        $stmt->execute();
        $stmt->bind_result($owner, $isActive);

        if ($stmt->fetch()) {
            $stmt->close();

            // Verifica se o usuário tem permissão
            if ($this->authenticator->isAdmin() || $owner === $userId) {
                // Atualiza o link para inativo
                $this->updateInactiveLinkStatus($shortUrlKey);
            } else {
                $this->accessDenied();
            }
        } else {
            $stmt->close();
            $this->accessDenied();
        }
    }

    public function updateInactiveLinkStatus($shortUrlKey) {
        $query = "UPDATE links SET is_active = 0 WHERE short_url_key = ?";
        $stmt = $this->database->prepareQuery($query);
        $stmt->bind_param("s", $shortUrlKey);
        $stmt->execute();
        $stmt->close();

        // Redireciona de volta para o painel
    }
    public function updateActiveLinkStatus($shortUrlKey) {
        $query = "UPDATE links SET is_active = 1 WHERE short_url_key = ?";
        $stmt = $this->database->prepareQuery($query);
        $stmt->bind_param("s", $shortUrlKey);
        $stmt->execute();
        $stmt->close();

        // Redireciona de volta para o painel
    }

    public function accessDenied() {

    }
}


?>
