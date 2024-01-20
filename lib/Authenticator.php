<?php
require_once('./lib/Database.php');

class Authenticator {
    private $authenticated = false;
    private $database;

    public function __construct(Database $database) {
        session_start();
        $this->database = $database;
        $this->checkAuthentication();
    }

    public function checkAuthentication() {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] && isset($_SESSION['token'])  && $_SESSION['token']) {
            $user = $this->getUserByToken($_SESSION['token']);
            $userid = $this->getIdByUser($user);
            $query = $this->database->prepareQuery("SELECT is_active FROM users WHERE id = ?");
            $query->bind_param("s", $userid);
            $query->execute();
            $query->bind_result($isactive);

            if ($query->fetch() && $isactive) {
                $this->authenticated = true;
            }else{
                $this->logout();
            }
            $query->close();
        }
    }

    public function authenticate($username, $password) {
        $query = $this->database->prepareQuery("SELECT id, username, password, is_active FROM users WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->bind_result($id, $dbUsername, $dbPassword, $isactive);

        if ($query->fetch() && password_verify($password, $dbPassword) && $isactive) {
            $query->close();
            $_SESSION['token'] = $this->setToken($username);
            $_SESSION['authenticated'] = true;
            $_SESSION['id'] = $id;
            $this->authenticated = true;
        }

    }

    public function isAuthenticated() {
        return $this->authenticated;
    }
    
    public function isAdmin() {
         if (!$this->authenticated) {
            return false; // Se não estiver autenticado, não é um administrador
        }
        $user = $this->getUserByToken($_SESSION['token']);
        $userid = $this->getIdByUser($user);
        $query = $this->database->prepareQuery("SELECT is_admin FROM users WHERE id = ?");
        $query->bind_param("i", $userid);
        $query->execute();
        $query->bind_result($isadmin);

        if ($query->fetch() && $isadmin) {
            return true;
        }else{
            return false;
        }
        return false;
    }
    
    public function getUserByToken($token) {
        $query = $this->database->prepareQuery("SELECT username FROM users_token WHERE token = ? AND expires > NOW()");
        $query->bind_param("s", $token);
        $query->execute();
        $query->bind_result($username);
    
        if ($query->fetch()) {
            return $username;
        }
        $query->close();
        return null;
    }
    
    public function getIdByUser($user) {
        $query = $this->database->prepareQuery("SELECT id FROM users WHERE username = ?");
        $query->bind_param("s", $user);
        $query->execute();
        $query->bind_result($id);
    
        if ($query->fetch()) {
            return $id;
        }
    
        $query->close();
        return null; // Return null if no valid token is found
    }

    public function setToken($username) {
        $token = bin2hex(random_bytes(32)); // Generate a random token
        $expires = date('Y-m-d H:i:s', strtotime('+1 day')); // Set expiration to 1 day from now

        $insertQuery = $this->database->prepareQuery("INSERT INTO users_token (username, token, expires) VALUES (?, ?, ?)");
        $insertQuery->bind_param("sss", $username, $token, $expires);
        $insertQuery->execute();
        $insertQuery->close();

        return $token;
    }

    public function logout() {
        $_SESSION['authenticated'] = false;
        $this->authenticated = false;
        session_destroy();
    }
}