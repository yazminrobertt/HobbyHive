<?php
    include('connect.php');

    if (isset($_GET['child_id'])) {
        $childId = $_GET['child_id'];

        // Delete the child record
        $deleteSql = "DELETE FROM child WHERE CHILD_ID = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $childId);
        if ($stmt->execute()) {
            echo "<script>alert('Child removed successfully.'); window.location.href = 'parentProfile.php';</script>";
        } else {
            echo "<script>alert('Failed to remove child.'); window.location.href = 'parentProfile.php';</script>";
        }
    }
?>
