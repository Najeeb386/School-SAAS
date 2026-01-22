<?php
class AuthController {
    
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    /**
     * Login user with email and password
     */
    public function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email and password are required'
            ];
        }

        try {
            // Find user by email in super_admin table
            $sql = "SELECT * FROM super_admin WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }

            // Verify password - handle both hashed and plain text
            $passwordValid = false;
            
            // Check if password is hashed (bcrypt format)
            if (strpos($user['password'], '$2y$') === 0 || strpos($user['password'], '$2a$') === 0 || strpos($user['password'], '$2b$') === 0) {
                // Password is hashed, use password_verify
                $passwordValid = password_verify($password, $user['password']);
            } else {
                // Password is plain text, compare directly
                $passwordValid = ($password === $user['password']);
                
                // If password matches, hash it for future logins
                if ($passwordValid) {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $hashSql = "UPDATE super_admin SET password = ? WHERE id = ?";
                    $hashStmt = $this->db->prepare($hashSql);
                    $hashStmt->execute([$hashedPassword, $user['id']]);
                }
            }
            
            if (!$passwordValid) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;

            // Update last login timestamp
            $this->updateLastLogin($user['id']);

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ]
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Register new super admin user
     */
    public function register($data) {
        // Validate input
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return [
                'success' => false,
                'message' => 'Name, email, and password are required'
            ];
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        try {
            // Check if email exists in super_admin table
            $sql = "SELECT id FROM super_admin WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Insert new super admin
            $sql = "INSERT INTO super_admin (name, email, password, created_at, updated_at) 
                    VALUES (?, ?, ?, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $hashedPassword
            ]);

            $userId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user details
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name']
        ];
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        try {
            $sql = "SELECT id, username, email, first_name, last_name, role, status FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Update password
     */
    public function updatePassword($userId, $oldPassword, $newPassword) {
        try {
            // Get user from super_admin
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Get password from database
            $sql = "SELECT password FROM super_admin WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify old password
            if (!password_verify($oldPassword, $result['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password
            $sql = "UPDATE super_admin SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$hashedPassword, $userId]);

            return [
                'success' => true,
                'message' => 'Password updated successfully'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            $sql = "SELECT id, email FROM super_admin WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found in our system'
                ];
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));

            return [
                'success' => true,
                'message' => 'Password reset token generated',
                'token' => $token
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password (in production, verify token)
            $sql = "UPDATE super_admin SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);

            return [
                'success' => true,
                'message' => 'Password reset successfully'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update last login time
     */
    private function updateLastLogin($userId) {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // Silently fail
        }
    }

    /**
     * Get all users (admin only)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        try {
            $sql = "SELECT id, username, email, first_name, last_name, role, status, created_at, last_login 
                    FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Update user status
     */
    public function updateUserStatus($userId, $status) {
        try {
            $validStatus = ['active', 'inactive', 'suspended'];
            if (!in_array($status, $validStatus)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status'
                ];
            }

            $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $userId]);

            return [
                'success' => true,
                'message' => 'User status updated'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete super admin user
     */
    public function deleteUser($userId) {
        try {
            $sql = "DELETE FROM super_admin WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
}

// Legacy function for backward compatibility
function login() {
    // Placeholder for backward compatibility
}
?>