<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    private $conn;

    public function __construct()
    {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        } catch (\Exception $e) {
            // If .env fails to load, we'll rely on fallbacks in getConnection
        }
    }

    // Get the database connection
    public function getConnection()
    {
        $this->conn = null;

        try {
            $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? '3306';
            $db_name = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'smartq_db';
            $user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? 'root';
            $pass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? '';

            $this->conn = new PDO(
                "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name,
                $user,
                $pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            throw new Exception("Connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>