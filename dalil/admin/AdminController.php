<?php
/**
 * AdminController - Base controller for admin operations
 * Handles common admin functionality and reduces code duplication
 */

class AdminController {
    protected $pdo;
    protected $site_settings;
    public $csrf_token;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeSession();
        $this->loadSiteSettings();
        $this->generateCSRFToken();
    }
    
    /**
     * Initialize session and check authentication
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ../login.php');
            exit;
        }
    }
    
    /**
     * Load site settings
     */
    private function loadSiteSettings() {
        $this->site_settings = load_site_settings($this->pdo);
    }
    
    /**
     * Generate CSRF token
     */
    private function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->csrf_token = $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission) {
        return !empty($_SESSION['permissions'][$permission]);
    }
    
    /**
     * Redirect with error if no permission
     */
    public function requirePermission($permission, $redirect = 'dashboard.php') {
        if (!$this->hasPermission($permission)) {
            $_SESSION['error_message'] = 'ليس لديك صلاحية للوصول لهذه الصفحة';
            header("Location: $redirect");
            exit;
        }
    }
    
    /**
     * Set success message
     */
    public function setSuccessMessage($message) {
        $_SESSION['success_message'] = $message;
    }
    
    /**
     * Set error message
     */
    public function setErrorMessage($message) {
        $_SESSION['error_message'] = $message;
    }
    
    /**
     * Get and clear success message
     */
    public function getSuccessMessage() {
        if (isset($_SESSION['success_message'])) {
            $message = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            return $message;
        }
        return null;
    }
    
    /**
     * Get and clear error message
     */
    public function getErrorMessage() {
        if (isset($_SESSION['error_message'])) {
            $message = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            return $message;
        }
        return null;
    }
    
    /**
     * Render message alerts
     */
    public function renderMessages() {
        $success = $this->getSuccessMessage();
        $error = $this->getErrorMessage();
        
        if ($success) {
            echo "<div class='message success'><i class='fas fa-check-circle'></i> " . htmlspecialchars($success) . "</div>";
        }
        
        if ($error) {
            echo "<div class='message error'><i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($error) . "</div>";
        }
    }
    
    /**
     * Sanitize input data
     */
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate required fields
     */
    public function validateRequired($data, $required_fields) {
        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "الحقل '$field' مطلوب";
            }
        }
        return $errors;
    }
    
    /**
     * Handle file upload
     */
    public function handleFileUpload($file, $upload_dir = '../uploads/', $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'لم يتم رفع الملف بشكل صحيح'];
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            return ['success' => false, 'message' => 'نوع الملف غير مسموح'];
        }
        
        $file_name = 'ad_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return ['success' => true, 'filename' => $file_name, 'path' => $file_path];
        }
        
        return ['success' => false, 'message' => 'فشل في رفع الملف'];
    }
    
    /**
     * Get current user info
     */
    public function getCurrentUser() {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['username'],
            'permissions' => $_SESSION['permissions']
        ];
    }
    
    /**
     * Log admin action
     */
    public function logAction($action, $details = '') {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO admin_logs (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['admin_id'], $action, $details]);
        } catch (PDOException $e) {
            // Log error but don't break the flow
            error_log("Failed to log admin action: " . $e->getMessage());
        }
    }
    
    /**
     * Redirect with message
     */
    public function redirect($url, $message = '', $type = 'success') {
        if ($message) {
            if ($type === 'success') {
                $this->setSuccessMessage($message);
            } else {
                $this->setErrorMessage($message);
            }
        }
        header("Location: $url");
        exit;
    }

    /**
     * Renders the admin header template.
     * @param string $page_title The title for the specific page.
     */
    public function renderHeader($page_title = '') {
        // Make controller variables available to the template
        $guide_name = htmlspecialchars($this->site_settings['guide_name'] ?? 'لوحة التحكم');
        $logo_path = htmlspecialchars($this->site_settings['logo_path'] ?? 'https://i.postimg.cc/sxNCrL6d/logo-white-03.png');
        if (!empty($this->site_settings['logo_path']) && file_exists('../' . $this->site_settings['logo_path'])) {
            $logo_path = '../' . $this->site_settings['logo_path'];
        }

        include __DIR__ . '/templates/admin_header.php';
    }

    /**
     * Renders the admin footer template.
     */
    public function renderFooter() {
        include __DIR__ . '/templates/admin_footer.php';
    }

    /**
     * Handles the logic for adding a new user.
     * @return array An array containing 'success' or 'error' message.
     */
    public function handleAddUser() {
        $response = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Token Validation
            if (!$this->verifyCSRFToken($_POST['csrf_token'])) {
                $response['error'] = "فشل التحقق من الطلب (CSRF).";
            } else {
                $username = $this->sanitizeInput($_POST['username']);
                $password = $_POST['password']; // Passwords are not sanitized to preserve special characters
                $confirm_password = $_POST['confirm_password'];

                try {
                    // Validate inputs
                    if (empty($username) || empty($password) || empty($confirm_password)) {
                        $response['error'] = "جميع الحقول مطلوبة 🚫";
                    } elseif ($password !== $confirm_password) {
                        $response['error'] = "كلمتا المرور غير متطابقتين 🚫";
                    } elseif (strlen($username) > 50) {
                        $response['error'] = "اسم المستخدم طويل جدًا (50 حرفًا كحد أقصى) 🚫";
                    } elseif (strlen($password) < 8) {
                        $response['error'] = "كلمة المرور يجب أن تكون 8 أحرف على الأقل 🚫";
                    } else {
                        // Check if username exists
                        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
                        $stmt->execute([$username]);

                        if ($stmt->rowCount() > 0) {
                            $response['error'] = "اسم المستخدم موجود بالفعل 🚫";
                        } else {
                            // Hash password and insert user
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                            $stmt->execute([$username, $hashed_password]);

                            $response['success'] = "تم إضافة المستخدم بنجاح ✅";
                            $this->logAction('Add User', "Added user: " . $username);
                        }
                    }
                } catch (PDOException $e) {
                    $response['error'] = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
                }
            }
        }
        return $response;
    }

    /**
     * Handles the logic for deleting a program.
     * @param int $program_id The ID of the program to delete.
     * @return array An array containing 'success' or 'error' message.
     */
    public function handleDeleteProgram($program_id) {
        $response = [];
        $this->requirePermission('can_delete_programs');

        if (empty($program_id)) {
            $response['error'] = "معرف البرنامج غير متوفر.";
            return $response;
        }

        try {
            // Fetch program title for confirmation message
            $stmt = $this->pdo->prepare("SELECT title FROM programs WHERE id = ?");
            $stmt->execute([$program_id]);
            $program = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$program) {
                $response['error'] = "البرنامج غير موجود.";
                return $response;
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (!$this->verifyCSRFToken($_POST['csrf_token'])) {
                    $response['error'] = "فشل التحقق من الطلب (CSRF).";
                } else {
                    $stmt = $this->pdo->prepare("DELETE FROM programs WHERE id = ?");
                    $stmt->execute([$program_id]);
                    $response['success'] = "تم حذف البرنامج بنجاح ✅";
                    $this->logAction('Delete Program', "Deleted program: " . $program['title'] . " (ID: " . $program_id . ")");
                }
            } else {
                // For GET request, just return program info for display
                $response['program'] = $program;
            }
        } catch (PDOException $e) {
            $response['error'] = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
        }
        return $response;
    }

    /**
     * Handles the logic for deleting a user.
     * @param int $user_id_to_delete The ID of the user to delete.
     * @return array An array containing 'success' or 'error' message.
     */
    public function handleDeleteUser($user_id_to_delete) {
        $response = [];
        $this->requirePermission('can_manage_users');

        if (empty($user_id_to_delete)) {
            $response['error'] = "معرف المستخدم غير متوفر.";
            return $response;
        }

        // Prevent a user from deleting themselves
        if ($user_id_to_delete == $_SESSION['admin_id']) {
            $response['error'] = "لا يمكنك حذف حسابك الخاص.";
            return $response;
        }

        try {
            // Fetch user info to display on the confirmation page
            $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$user_id_to_delete]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $response['error'] = "المستخدم غير موجود.";
                return $response;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$this->verifyCSRFToken($_POST['csrf_token'])) {
                    $response['error'] = "فشل التحقق من الطلب (CSRF).";
                } else {
                    $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id_to_delete]);
                    $response['success'] = "تم حذف المستخدم بنجاح ✅";
                    $this->logAction('Delete User', "Deleted user: " . $user['username'] . " (ID: " . $user_id_to_delete . ")");
                }
            } else {
                // For GET request, just return user info for display
                $response['user'] = $user;
            }
        } catch (PDOException $e) {
            $response['error'] = "حدث خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
        }
        return $response;
    }

    /**
     * Fetches reports data.
     * @return array An array containing reports data.
     */
    public function getReportsData() {
        $data = [
            'organizers' => [],
            'directions' => [],
            'locations' => [],
            'error' => null,
        ];

        try {
            // Organizers data
            $data['organizers'] = $this->pdo->query("SELECT organizer, COUNT(*) as count FROM programs WHERE organizer IS NOT NULL AND organizer != '' GROUP BY organizer ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);

            // Directions data
            $data['directions'] = $this->pdo->query("SELECT Direction, COUNT(*) as count FROM programs WHERE Direction IS NOT NULL AND Direction != '' GROUP BY Direction ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);

            // Locations data
            $data['locations'] = $this->pdo->query("SELECT location, COUNT(*) as count FROM programs WHERE location IS NOT NULL AND location != '' GROUP BY location ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $data['error'] = 'خطأ في جلب البيانات: ' . $e->getMessage();
        }

        return $data;
    }
}
