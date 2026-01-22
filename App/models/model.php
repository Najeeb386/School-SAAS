<?php

abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    protected function query(string $sql, array $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
