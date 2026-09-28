<?php
@require_once 'db.php';

$leaveTypes = [];
$result = mysqli_query($conn, "SELECT * FROM leave_types");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $leaveTypes[] = $row;
    }
}

echo json_encode(['success' => true, 'leaveTypes' => $leaveTypes]);
