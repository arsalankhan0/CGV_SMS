<?php
include('../includes/dbconnection.php');

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS tblplanner (
        ID INT AUTO_INCREMENT PRIMARY KEY,      
        Class INT,                              
        Planners VARCHAR(255),                  
        CreationDate DATETIME DEFAULT CURRENT_TIMESTAMP,  
        FOREIGN KEY (Class) REFERENCES tblclass(ID)  
    ) ENGINE=InnoDB;  
    ";

    $dbh->exec($sql);

    echo "Table 'tblplanner' created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
