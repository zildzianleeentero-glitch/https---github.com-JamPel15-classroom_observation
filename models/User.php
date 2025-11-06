<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $name;
    public $role;
    public $department;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login method
    public function login() {
        // Prepare query
        $query = "SELECT id, username, password, name, role, department, status 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  AND role = :role 
                  AND status = 'active' 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind parameters
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        
        // Execute query
        if($stmt->execute()) {
            // Check if user exists
            if($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (assuming passwords are hashed)
                if(password_verify($this->password, $row['password'])) {
                    // Set user properties
                    $this->id = $row['id'];
                    $this->name = $row['name'];
                    $this->department = $row['department'];
                    $this->status = $row['status'];
                    return true;
                }
            }
        }
        return false;
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all users
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create new user
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, name, role, department, status, created_at) 
                  VALUES (:username, :password, :name, :role, :department, 'active', NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':department', $data['department']);
        
        return $stmt->execute();
    }

    // Update user
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, role = :role, department = :department";
        
        // Add password update if provided
        if(!empty($data['password'])) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':id', $id);
        
        if(!empty($data['password'])) {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
        }
        
        return $stmt->execute();
    }

    // Delete user (soft delete)
    public function delete($id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get total users count
    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Get active sessions count (simplified)
    public function getActiveSessions() {
        $query = "SELECT COUNT(DISTINCT user_id) as active_sessions FROM audit_logs 
                  WHERE action = 'LOGIN' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['active_sessions'] ?? 0;
    }
}
?>