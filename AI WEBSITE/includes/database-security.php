<?php

class DatabaseSecurity {
    private $pdo;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
    }
    
    public function setPDO($pdo) {
        $this->pdo = $pdo;
    }
    
    // Secure SELECT query with prepared statements
    public function secureSelect($table, $columns = '*', $where = [], $orderBy = null, $limit = null) {
        if (!$this->pdo) {
            throw new RuntimeException('Database connection not initialized');
        }
        
        // Validate table name (only alphanumeric and underscore)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name');
        }
        
        $sql = "SELECT $columns FROM $table";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                // Validate column name
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                    throw new InvalidArgumentException("Invalid column name: $column");
                }
                $conditions[] = "$column = :$column";
                $params[":$column"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Secure INSERT with prepared statements
    public function secureInsert($table, $data) {
        if (!$this->pdo) {
            throw new RuntimeException('Database connection not initialized');
        }
        
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name');
        }
        
        $columns = [];
        $placeholders = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            // Validate column name
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new InvalidArgumentException("Invalid column name: $column");
            }
            $columns[] = $column;
            $placeholders[] = ":$column";
            $params[":$column"] = $value;
        }
        
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    // Secure UPDATE with prepared statements
    public function secureUpdate($table, $data, $where) {
        if (!$this->pdo) {
            throw new RuntimeException('Database connection not initialized');
        }
        
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name');
        }
        
        $setClauses = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            // Validate column name
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new InvalidArgumentException("Invalid column name: $column");
            }
            $setClauses[] = "$column = :set_$column";
            $params[":set_$column"] = $value;
        }
        
        $whereClauses = [];
        foreach ($where as $column => $value) {
            // Validate column name
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new InvalidArgumentException("Invalid column name: $column");
            }
            $whereClauses[] = "$column = :where_$column";
            $params[":where_$column"] = $value;
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $setClauses) . " WHERE " . implode(' AND ', $whereClauses);
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    // Secure DELETE with prepared statements
    public function secureDelete($table, $where) {
        if (!$this->pdo) {
            throw new RuntimeException('Database connection not initialized');
        }
        
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name');
        }
        
        $whereClauses = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            // Validate column name
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new InvalidArgumentException("Invalid column name: $column");
            }
            $whereClauses[] = "$column = :$column";
            $params[":$column"] = $value;
        }
        
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClauses);
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    // Prevent SQL injection by escaping identifiers
    public function escapeIdentifier($identifier) {
        // Remove any characters that aren't alphanumeric or underscore
        return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
    }
}

// Helper function to get database security instance
function getDBSecurity($pdo = null) {
    static $instance = null;
    if ($instance === null) {
        $instance = new DatabaseSecurity($pdo);
    } elseif ($pdo !== null) {
        $instance->setPDO($pdo);
    }
    return $instance;
}
