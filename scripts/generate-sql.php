<?php
// Simple script to dump SQLite tables and data into MySQL-compatible SQL
$sqlitePath = 'database/database.sqlite';

if (!file_exists($sqlitePath)) {
    die("SQLite database not found at $sqlitePath\n");
}

try {
    $db = new PDO("sqlite:$sqlitePath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

    $output = "-- Daser GYM MySQL Dump\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $output .= "-- Dumping structure for table $table\n";
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Use a simple mapping of types for MySQL
        $createSql = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
        
        // Translation logic (better handling for MySQL/MariaDB)
        $mysqlCreate = strtolower($createSql);
        $mysqlCreate = str_replace('"', '`', $mysqlCreate);
        $mysqlCreate = str_replace('autoincrement', 'AUTO_INCREMENT', $mysqlCreate);
        
        // Handle VARCHAR without length
        $mysqlCreate = preg_replace('/varchar(?!\s*\()/', 'VARCHAR(255)', $mysqlCreate);
        
        // Map types
        $mysqlCreate = str_replace('integer', 'INT', $mysqlCreate);
        $mysqlCreate = str_replace('text', 'LONGTEXT', $mysqlCreate);
        $mysqlCreate = str_replace('datetime', 'DATETIME', $mysqlCreate);
        
        // Ensure primary key is outside the column definition or handled correctly
        // MySQL often prefers: `id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)
        // But `id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL usually works.
        // Let's try to make it even safer.
        
        $output .= $mysqlCreate . " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        $output .= "-- Dumping data for table $table\n";
        $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $cols = array_keys($rows[0]);
            $output .= "INSERT INTO `$table` (`" . implode("`, `", $cols) . "`) VALUES \n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = array_map(function($val) use ($db) {
                    if ($val === null) return 'NULL';
                    return $db->quote($val);
                }, $row);
                $values[] = "(" . implode(", ", $rowValues) . ")";
            }
            $output .= implode(",\n", $values) . ";\n\n";
        }
    }

    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    file_put_contents('database_dump_for_mysql.sql', $output);
    echo "Successfully generated database_dump_for_mysql.sql\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
