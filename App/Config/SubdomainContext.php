<?php
/**
 * Subdomain Detection & School Context Handler
 * Identifies which school is accessing the system based on subdomain
 */

class SubdomainContext {
    
    private static $instance = null;
    private $subdomain = null;
    private $school_id = null;
    private $is_admin = false;
    private $db = null;

    private function __construct($db = null) {
        $this->db = $db;
        $this->detectSubdomain();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance($db = null) {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }

    /**
     * Detect subdomain from HTTP_HOST
     * Examples:
     * - localhost:8080 -> subdomain: null, is_admin: true
     * - admin.localhost -> subdomain: admin, is_admin: true
     * - school1.localhost -> subdomain: school1, is_admin: false
     * - school1.saas.com -> subdomain: school1, is_admin: false
     */
    private function detectSubdomain() {
        $host = $_SERVER['HTTP_HOST'];
        
        // Remove port number
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If localhost or single part, it's the main admin panel
        if (count($parts) <= 1 || strpos($host, 'localhost') !== false && count($parts) <= 2) {
            $this->is_admin = true;
            $this->subdomain = null;
            return;
        }
        
        // Get the first part as subdomain
        $this->subdomain = strtolower($parts[0]);
        
        // Check if it's admin subdomain
        if ($this->subdomain === 'admin') {
            $this->is_admin = true;
            $this->subdomain = null;
            return;
        }
        
        // It's a school subdomain
        $this->is_admin = false;
        $this->resolveSchoolFromSubdomain();
    }

    /**
     * Resolve school_id from subdomain
     */
    private function resolveSchoolFromSubdomain() {
        if (!$this->db || !$this->subdomain) {
            return;
        }

        try {
            $sql = "SELECT id FROM schools WHERE subdomain = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->subdomain]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->school_id = $result['id'];
            }
        } catch (Exception $e) {
            // Log error but don't fail
            error_log('Error resolving school from subdomain: ' . $e->getMessage());
        }
    }

    /**
     * Get the detected subdomain
     */
    public function getSubdomain() {
        return $this->subdomain;
    }

    /**
     * Get the resolved school ID
     */
    public function getSchoolId() {
        return $this->school_id;
    }

    /**
     * Check if this is admin panel access
     */
    public function isAdminAccess() {
        return $this->is_admin;
    }

    /**
     * Check if this is a school subdomain access
     */
    public function isSchoolAccess() {
        return !$this->is_admin && $this->subdomain !== null;
    }

    /**
     * Get context as array
     */
    public function getContext() {
        return [
            'subdomain' => $this->subdomain,
            'school_id' => $this->school_id,
            'is_admin' => $this->is_admin,
            'is_school' => $this->isSchoolAccess(),
            'full_host' => $_SERVER['HTTP_HOST']
        ];
    }
}
?>
