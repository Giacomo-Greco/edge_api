<?php

class Database {
    
    private $host = 'localhost';
    private $db_name = 'dbjdi2wq6ahxvb';
    private $username = 'ugjunrlbkwdma';
    private $password = 'provaprova22';

    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . 
                ';dbname=' . $this->db_name, $this->username, $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connenction error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}