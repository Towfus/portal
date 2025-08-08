<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'deped_schools');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully!<br>";

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// SQL to create tables
$sql = [
    "DROP TABLE IF EXISTS process_steps",
    "DROP TABLE IF EXISTS process_paths",
    "DROP TABLE IF EXISTS process_types",
    
    "CREATE TABLE IF NOT EXISTS process_types (
        process_id VARCHAR(50) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        announcement TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    "CREATE TABLE IF NOT EXISTS process_paths (
        path_id INT AUTO_INCREMENT PRIMARY KEY,
        process_id VARCHAR(50) NOT NULL,
        path_type ENUM('compliant', 'nonCompliant') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (process_id) REFERENCES process_types(process_id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    "CREATE TABLE IF NOT EXISTS process_steps (
        step_id INT AUTO_INCREMENT PRIMARY KEY,
        path_id INT NOT NULL,
        step_order INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (path_id) REFERENCES process_paths(path_id) ON DELETE CASCADE,
        INDEX (path_id, step_order)
    ) ENGINE=InnoDB"
];

// Execute table creation
foreach ($sql as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table operation successful: " . strtok($query, " ") . "<br>";
    } else {
        echo "Error executing '" . strtok($query, " ") . "': " . $conn->error . "<br>";
    }
}

// Rest of the code remains the same...

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Start transaction
$conn->begin_transaction();

try {
    // Sample processes
    $sampleProcesses = [
        [
            'process_id' => 'new_permit',
            'title' => 'Process of Application of New Government Permit',
            'description' => 'New Permit Application Process',
            'announcement' => 'Submit all requirements before deadline'
        ],
        [
            'process_id' => 'gov_recognition',
            'title' => 'Application of Government Recognition',
            'description' => 'Government Recognition Process',
            'announcement' => ''
        ]
    ];

    foreach ($sampleProcesses as $process) {
        $stmt = $conn->prepare("INSERT INTO process_types (process_id, title, description, announcement) 
                               VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $process['process_id'], $process['title'], $process['description'], $process['announcement']);
        $stmt->execute();
        echo "Process '{$process['title']}' inserted<br>";
    }

    // Sample paths
    $samplePaths = [
        ['process_id' => 'new_permit', 'path_type' => 'compliant'],
        ['process_id' => 'new_permit', 'path_type' => 'nonCompliant'],
        ['process_id' => 'gov_recognition', 'path_type' => 'compliant']
    ];

    foreach ($samplePaths as $path) {
        $stmt = $conn->prepare("INSERT INTO process_paths (process_id, path_type) VALUES (?, ?)");
        $stmt->bind_param("ss", $path['process_id'], $path['path_type']);
        $stmt->execute();
        $pathId = $conn->insert_id;
        echo "Path for '{$path['process_id']}' ({$path['path_type']}) inserted (ID: $pathId)<br>";
        
        // Sample steps for each path
        if ($pathId > 0) {
            $steps = [
                ['title' => 'Submit Application', 'description' => 'Complete application form'],
                ['title' => 'Document Review', 'description' => 'Initial review of documents'],
                ['title' => 'Payment', 'description' => 'Pay required fees']
            ];
            
            foreach ($steps as $order => $step) {
                $order++;
                $stmt = $conn->prepare("INSERT INTO process_steps (path_id, step_order, title, description)
                                      VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $pathId, $order, $step['title'], $step['description']);
                $stmt->execute();
                echo "Step '{$step['title']}' added to path $pathId<br>";
            }
        }
    }

    // Commit transaction
    $conn->commit();
    echo "All operations completed successfully!<br>";

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "<br>";
}

$conn->close();