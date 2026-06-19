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
    $db->query("DELETE FROM advertisements WHERE id = :id");
    $db->bind(':id', $id);
    if ($db->execute()) {
        setFlash('ads', 'Advertisement deleted successfully.');
    }
    redirect('advertisements.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $title = sanitize($_POST['title']);
    $position = sanitize($_POST['position']);
    $ad_type = $_POST['ad_type'];
    $ad_content = $_POST['ad_content'];
    $link_url = sanitize($_POST['link_url']);
    $status = $_POST['status'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $image_path = '';
    if (!empty($_FILES['ad_image']['name'])) {
        $upload = uploadFile($_FILES['ad_image'], "../assets/uploads/ads/");
        if ($upload['status']) {
            $image_path = $upload['filename'];
        } else {
            setFlash('ads', $upload['message'], 'alert alert-danger');
            redirect('advertisements.php');
        }
    }

    if ($id > 0) {
        $sql = "UPDATE advertisements SET title = :title, position = :position, ad_type = :ad_type, ad_content = :ad_content, link_url = :link_url, status = :status";
        if ($image_path) $sql .= ", image_path = :image_path";
        $sql .= " WHERE id = :id";
        $db->query($sql);
        $db->bind(':id', $id);
    } else {
        $db->query("INSERT INTO advertisements (title, position, ad_type, ad_content, image_path, link_url, status) VALUES (:title, :position, :ad_type, :ad_content, :image_path, :link_url, :status)");
        $db->bind(':image_path', $image_path);
    }

    $db->bind(':title', $title);
    $db->bind(':position', $position);
    $db->bind(':ad_type', $ad_type);
    $db->bind(':ad_content', $ad_content);
    $db->bind(':link_url', $link_url);
    $db->bind(':status', $status);
    if ($id > 0 && $image_path) $db->bind(':image_path', $image_path);

    if ($db->execute()) {
        setFlash('ads', 'Advertisement saved successfully.');
    } else {
        setFlash('ads', 'Failed to save advertisement.', 'alert alert-danger');
    }
    redirect('advertisements.php');
}

$db->query("SELECT * FROM advertisements ORDER BY id DESC");
$ads = $db->resultSet();

$edit_ad = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $db->query("SELECT * FROM advertisements WHERE id = :id");
    $db->bind(':id', $id);
    $edit_ad = $db->single();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advertisements - Admin</title>
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

            <div class="col-md-10 main-content">
                <h2>Manage Advertisements</h2>
                <?php displayFlash('ads'); ?>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><?php echo $edit_ad ? 'Edit' : 'Add'; ?> Ad</div>
                            <div class="card-body">
                                <form action="advertisements.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <?php if ($edit_ad): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_ad['id']; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">Ad Title</label>
                                        <input type="text" name="title" class="form-control" value="<?php echo $edit_ad['title'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Position</label>
                                        <select name="position" class="form-select" required>
                                            <option value="header" <?php echo (isset($edit_ad['position']) && $edit_ad['position'] == 'header') ? 'selected' : ''; ?>>Header</option>
                                            <option value="sidebar" <?php echo (isset($edit_ad['position']) && $edit_ad['position'] == 'sidebar') ? 'selected' : ''; ?>>Sidebar</option>
                                            <option value="footer" <?php echo (isset($edit_ad['position']) && $edit_ad['position'] == 'footer') ? 'selected' : ''; ?>>Footer</option>
                                            <option value="between_news" <?php echo (isset($edit_ad['position']) && $edit_ad['position'] == 'between_news') ? 'selected' : ''; ?>>Between News</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ad Type</label>
                                        <select name="ad_type" class="form-select" required>
                                            <option value="image" <?php echo (isset($edit_ad['ad_type']) && $edit_ad['ad_type'] == 'image') ? 'selected' : ''; ?>>Image Ad</option>
                                            <option value="google_adsense" <?php echo (isset($edit_ad['ad_type']) && $edit_ad['ad_type'] == 'google_adsense') ? 'selected' : ''; ?>>Google AdSense</option>
                                            <option value="html_script" <?php echo (isset($edit_ad['ad_type']) && $edit_ad['ad_type'] == 'html_script') ? 'selected' : ''; ?>>HTML/Script</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ad Content / Script</label>
                                        <textarea name="ad_content" class="form-control" rows="4"><?php echo $edit_ad['ad_content'] ?? ''; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ad Image (for Image Ad)</label>
                                        <input type="file" name="ad_image" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Link URL (for Image Ad)</label>
                                        <input type="url" name="link_url" class="form-control" value="<?php echo $edit_ad['link_url'] ?? ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo (isset($edit_ad['status']) && $edit_ad['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($edit_ad['status']) && $edit_ad['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><?php echo $edit_ad ? 'Update' : 'Save'; ?></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Ad List</div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Position</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ads as $ad): ?>
                                            <tr>
                                                <td><?php echo $ad['title']; ?></td>
                                                <td><?php echo ucfirst($ad['position']); ?></td>
                                                <td><?php echo ucfirst($ad['ad_type']); ?></td>
                                                <td><span class="badge bg-<?php echo ($ad['status'] == 'active' ? 'success' : 'danger'); ?>"><?php echo ucfirst($ad['status']); ?></span></td>
                                                <td>
                                                    <a href="advertisements.php?edit=<?php echo $ad['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                                    <a href="advertisements.php?delete=<?php echo $ad['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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
        </div>
    </div>
</body>
</html>
