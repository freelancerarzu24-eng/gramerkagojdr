<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$db = new Database();

// Statistics
$db->query("SELECT COUNT(*) as total FROM news");
$total_news = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM categories");
$total_categories = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM users");
$total_users = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM comments WHERE status = 'pending'");
$pending_comments = $db->single()['total'];

// Recent News
$db->query("SELECT n.*, c.name_en as category_name FROM news n JOIN categories c ON n.category_id = c.id ORDER BY n.created_at DESC LIMIT 5");
$recent_news = $db->resultSet();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - News Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #343a40; color: #fff; }
        .sidebar a { color: #fff; text-decoration: none; display: block; padding: 10px 20px; }
        .sidebar a:hover { background: #495057; }
        .main-content { padding: 20px; }
        .card-counter { padding: 20px; border-radius: 10px; color: #fff; margin-bottom: 20px; }
        .bg-blue { background-color: #007bff; }
        .bg-green { background-color: #28a745; }
        .bg-orange { background-color: #fd7e14; }
        .bg-red { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3">
                    <h4>Admin Panel</h4>
                </div>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="categories.php"><i class="fas fa-list me-2"></i> Categories</a>
                <a href="news.php"><i class="fas fa-newspaper me-2"></i> News</a>
                <a href="users.php"><i class="fas fa-users me-2"></i> Users</a>
                <a href="gallery.php"><i class="fas fa-images me-2"></i> Gallery</a>
                <a href="advertisements.php"><i class="fas fa-ad me-2"></i> Advertisements</a>
                <a href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>Welcome, <?php echo $_SESSION['full_name']; ?> (<?php echo $_SESSION['user_role']; ?>)</div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card-counter bg-blue">
                            <i class="fas fa-newspaper fa-3x"></i>
                            <span class="float-end fs-2"><?php echo $total_news; ?></span>
                            <div class="mt-2">Total News</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter bg-green">
                            <i class="fas fa-list fa-3x"></i>
                            <span class="float-end fs-2"><?php echo $total_categories; ?></span>
                            <div class="mt-2">Categories</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter bg-orange">
                            <i class="fas fa-users fa-3x"></i>
                            <span class="float-end fs-2"><?php echo $total_users; ?></span>
                            <div class="mt-2">Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-counter bg-red">
                            <i class="fas fa-comments fa-3x"></i>
                            <span class="float-end fs-2"><?php echo $pending_comments; ?></span>
                            <div class="mt-2">Pending Comments</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">Recent News Articles</div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_news): ?>
                                            <?php foreach ($recent_news as $news): ?>
                                                <tr>
                                                    <td><?php echo $news['title_en']; ?></td>
                                                    <td><?php echo $news['category_name']; ?></td>
                                                    <td><span class="badge bg-<?php echo ($news['status'] == 'published' ? 'success' : 'warning'); ?>"><?php echo ucfirst($news['status']); ?></span></td>
                                                    <td><?php echo formatDate($news['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center">No news found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
