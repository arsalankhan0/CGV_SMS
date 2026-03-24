<?php
include('../includes/dbconnection.php');

// try {
//     $dbh->beginTransaction();

//     // Update tblstudents with previous class, section, and session from tblstudenthistory
//     $updateQuery = "
//         UPDATE tblstudent s
//         JOIN tblstudenthistory sh ON s.ID = sh.StudentID
//         SET 
//             s.StudentClass = sh.ClassID,
//             s.StudentSection = sh.Section,
//             s.SessionID = sh.SessionID
//     ";

//     $stmt = $dbh->prepare($updateQuery);
//     $stmt->execute();

//     $rowsUpdated = $stmt->rowCount();
    
//     // If updates were made, proceed to delete history
//     if ($rowsUpdated > 0) {
//         $deleteQuery = "DELETE FROM tblstudenthistory";
//         $dbh->exec($deleteQuery);
//     }

//     $dbh->commit();

//     echo "Restoration successful! $rowsUpdated records updated and history cleared.";
// } catch (Exception $e) {
//     $dbh->rollBack();
//     echo "Error: " . $e->getMessage();
// }
?>
