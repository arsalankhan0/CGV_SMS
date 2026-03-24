<?php
// include('../includes/dbconnection.php');

// try {
//     $dbh->beginTransaction();

//     // Drop and create the backup table with additional columns
//     $dbh->exec("DROP TABLE IF EXISTS tblreportsbackup");
//     $createTableSQL = "
//         CREATE TABLE tblreportsbackup LIKE tblreports;
//         ALTER TABLE tblreportsbackup 
//         ADD COLUMN backup_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//         ADD COLUMN backup_reason VARCHAR(255);
//     ";
//     $dbh->exec($createTableSQL);

//     $dbh->commit();
//     echo "Backup table structure created successfully!";

// } catch (PDOException $e) {
//     if ($dbh && $dbh->inTransaction()) {
//         $dbh->rollBack();
//     }
//     echo "Error: " . $e->getMessage();
//     error_log("Backup Creation Error: " . $e->getMessage());
// }

// // Function to perform backup with a specific reason
// function createBackupWithReason($dbh, $reason) {
//     try {
//         $dbh->beginTransaction();

//         // Insert data with reason and timestamp
//         $backupSQL = "INSERT INTO tblreportsbackup 
//                       SELECT *, CURRENT_TIMESTAMP, :reason FROM tblreports";
        
//         $stmt = $dbh->prepare($backupSQL);
//         $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
//         $stmt->execute();

//         $dbh->commit();
//         return true;

//     } catch (PDOException $e) {
//         if ($dbh && $dbh->inTransaction()) {
//             $dbh->rollBack();
//         }
//         error_log("Backup Error: " . $e->getMessage());
//         return false;
//     }
// }

// // Test the backup with a reason
// $reason = "Before making assignAll button in teacher module- (" . date('d-M-Y') . ")";
// if (createBackupWithReason($dbh, $reason)) {
//     echo "Backup created successfully with reason: " . $reason;
// } else {
//     echo "Failed to create backup";
// }
?>
