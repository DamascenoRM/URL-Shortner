<?php
class Database extends mysqli {
    private $connection;

    public function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($this->connection->connect_error) {
            die("Error connecting to the database: " . $this->connection->connect_error);
        }
    }

    public function prepareQuery($query) {
        $statement = $this->connection->prepare($query);
    
        if (!$statement) {
            throw new Exception("Error preparing query: " . $this->connection->error);
        }
    
        return $statement;
    }


    public function executeQuery($query) {
        return $this->connection->query($query);
    }

    public function closeConnection() {
        $this->connection->close();
    }

    public function addUser($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = $this->prepareQuery("INSERT INTO users (username, password) VALUES (?, ?)");
        $query->bind_param("ss", $username, $hashedPassword);
        $query->execute();
        $query->close();
    }

    public function removeUser($username) {
        $query = $this->prepareQuery("DELETE FROM users WHERE username = ?");
        $query->bind_param("s", $username);
        $query->execute();
        $query->close();
    }
}