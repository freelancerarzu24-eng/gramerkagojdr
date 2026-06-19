<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$db = new Database();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM news WHERE id = :id");
    $db->bind(':id', $id);
    if ($db->execute()) {
        setFlash('news', 'News deleted successfully.');
    } else {
        setFlash('news', 'Failed to delete news.', 'alert alert-danger');
    }
    redirect('news.php');
}

// Get all news
$db->query("SELECT n.*, c.name_en as category_name FROM news n JOIN categories c ON n.category_id = c.id ORDER BY n.id DESC");
$all_news = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #343a40; color: #fff; }
        .sidebar a { color: #fff; text-decoration: none; display: block; padding: 10px 20px; }
        .sidebar a:hover { background: #495057; }
        .main-content { padding: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3"><h4>Admin Panel</h4></div>
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
                    <h2>Manage News</h2>
                    <a href="news_add.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add News</a>
                </div>
                <?php displayFlash('news'); ?>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_news as $news): ?>
                                    <tr>
                                        <td>
                                            <?php if ($news['featured_image']): ?>
                                                <img src="../assets/uploads/news/<?php echo $news['featured_image']; ?>" width="50">
                                            <?php else: ?>
                                                No Image
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $news['title_en']; ?></td>
                                        <td><?php echo $news['category_name']; ?></td>
                                        <td><span class="badge bg-<?php echo ($news['status'] == 'published' ? 'success' : 'warning'); ?>"><?php echo ucfirst($news['status']); ?></span></td>
                                        <td>
                                            <a href="news_edit.php?id=<?php echo $news['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                            <a href="news.php?delete=<?php echo $news['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
