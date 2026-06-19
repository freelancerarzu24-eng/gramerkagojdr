<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user_role'] != 'Super Admin') {
    redirect('dashboard.php');
}

$db = new Database();
$db->query("SELECT l.*, u.username FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.id DESC LIMIT 100");
$logs = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Reduced for brevity in this step) -->
            <div class="col-md-10 offset-md-1 py-5">
                <div class="d-flex justify-content-between mb-4">
                    <h2>Activity Logs</h2>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['created_at']; ?></td>
                                        <td><?php echo $log['username'] ?? 'System'; ?></td>
                                        <td><?php echo $log['activity']; ?></td>
                                        <td><?php echo $log['ip_address']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
