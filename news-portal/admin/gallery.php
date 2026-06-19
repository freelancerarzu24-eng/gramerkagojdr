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
    $db->query("DELETE FROM galleries WHERE id = :id");
    $db->bind(':id', $id);
    if ($db->execute()) {
        setFlash('gallery', 'Gallery deleted successfully.');
    }
    redirect('gallery.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $title_en = sanitize($_POST['title_en']);
    $title_bn = sanitize($_POST['title_bn']);
    $status = $_POST['status'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $cover_image = '';
    if (!empty($_FILES['cover_image']['name'])) {
        $upload = uploadFile($_FILES['cover_image'], "../assets/uploads/gallery/");
        if ($upload['status']) {
            $cover_image = $upload['filename'];
        } else {
            setFlash('gallery', $upload['message'], 'alert alert-danger');
            redirect('gallery.php');
        }
    }

    if ($id > 0) {
        $sql = "UPDATE galleries SET title_en = :title_en, title_bn = :title_bn, status = :status";
        if ($cover_image) $sql .= ", cover_image = :cover_image";
        $sql .= " WHERE id = :id";
        $db->query($sql);
        $db->bind(':id', $id);
    } else {
        $db->query("INSERT INTO galleries (title_en, title_bn, cover_image, status) VALUES (:title_en, :title_bn, :cover_image, :status)");
        $db->bind(':cover_image', $cover_image);
    }

    $db->bind(':title_en', $title_en);
    $db->bind(':title_bn', $title_bn);
    $db->bind(':status', $status);
    if ($id > 0 && $cover_image) $db->bind(':cover_image', $cover_image);

    if ($db->execute()) {
        setFlash('gallery', 'Gallery saved successfully.');
    } else {
        setFlash('gallery', 'Failed to save gallery.', 'alert alert-danger');
    }
    redirect('gallery.php');
}

$db->query("SELECT * FROM galleries ORDER BY id DESC");
$galleries = $db->resultSet();

$edit_gal = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $db->query("SELECT * FROM galleries WHERE id = :id");
    $db->bind(':id', $id);
    $edit_gal = $db->single();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Admin</title>
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
                <h2>Manage Photo Gallery</h2>
                <?php displayFlash('gallery'); ?>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><?php echo $edit_gal ? 'Edit' : 'Add'; ?> Gallery</div>
                            <div class="card-body">
                                <form action="gallery.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <?php if ($edit_gal): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_gal['id']; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">Title (English)</label>
                                        <input type="text" name="title_en" class="form-control" value="<?php echo $edit_gal['title_en'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Title (Bangla)</label>
                                        <input type="text" name="title_bn" class="form-control" value="<?php echo $edit_gal['title_bn'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cover Image</label>
                                        <input type="file" name="cover_image" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo (isset($edit_gal['status']) && $edit_gal['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($edit_gal['status']) && $edit_gal['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><?php echo $edit_gal ? 'Update' : 'Save'; ?></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Gallery List</div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cover</th>
                                            <th>Title (EN)</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($galleries as $gal): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($gal['cover_image']): ?>
                                                        <img src="../assets/uploads/gallery/<?php echo $gal['cover_image']; ?>" width="50">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $gal['title_en']; ?></td>
                                                <td><span class="badge bg-<?php echo ($gal['status'] == 'active' ? 'success' : 'danger'); ?>"><?php echo ucfirst($gal['status']); ?></span></td>
                                                <td>
                                                    <a href="gallery.php?edit=<?php echo $gal['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                                    <a href="gallery.php?delete=<?php echo $gal['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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
