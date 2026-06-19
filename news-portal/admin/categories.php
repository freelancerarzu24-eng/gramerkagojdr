<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$db = new Database();
$success = $error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM categories WHERE id = :id");
    $db->bind(':id', $id);
    if ($db->execute()) {
        setFlash('category', 'Category deleted successfully.');
    } else {
        setFlash('category', 'Failed to delete category.', 'alert alert-danger');
    }
    redirect('categories.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $name_en = sanitize($_POST['name_en']);
    $name_bn = sanitize($_POST['name_bn']);
    $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($name_en);
    $status = $_POST['status'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        $db->query("UPDATE categories SET name_en = :name_en, name_bn = :name_bn, slug = :slug, status = :status WHERE id = :id");
        $db->bind(':id', $id);
    } else {
        $db->query("INSERT INTO categories (name_en, name_bn, slug, status) VALUES (:name_en, :name_bn, :slug, :status)");
    }

    $db->bind(':name_en', $name_en);
    $db->bind(':name_bn', $name_bn);
    $db->bind(':slug', $slug);
    $db->bind(':status', $status);

    if ($db->execute()) {
        setFlash('category', 'Category saved successfully.');
    } else {
        setFlash('category', 'Failed to save category.', 'alert alert-danger');
    }
    redirect('categories.php');
}

// Get all categories
$db->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $db->resultSet();

// Get category for edit
$edit_cat = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $db->query("SELECT * FROM categories WHERE id = :id");
    $db->bind(':id', $id);
    $edit_cat = $db->single();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
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
                <h2>Manage Categories</h2>
                <?php displayFlash('category'); ?>

                <div class="row mt-4">
                    <!-- Form -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><?php echo $edit_cat ? 'Edit' : 'Add'; ?> Category</div>
                            <div class="card-body">
                                <form action="categories.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <?php if ($edit_cat): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_cat['id']; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">Name (English)</label>
                                        <input type="text" name="name_en" class="form-control" value="<?php echo $edit_cat['name_en'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Name (Bangla)</label>
                                        <input type="text" name="name_bn" class="form-control" value="<?php echo $edit_cat['name_bn'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Slug (Optional)</label>
                                        <input type="text" name="slug" class="form-control" value="<?php echo $edit_cat['slug'] ?? ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo (isset($edit_cat['status']) && $edit_cat['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($edit_cat['status']) && $edit_cat['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><?php echo $edit_cat ? 'Update' : 'Save'; ?></button>
                                    <?php if ($edit_cat): ?>
                                        <a href="categories.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Category List</div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name (EN)</th>
                                            <th>Name (BN)</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo $cat['name_en']; ?></td>
                                                <td><?php echo $cat['name_bn']; ?></td>
                                                <td><span class="badge bg-<?php echo ($cat['status'] == 'active' ? 'success' : 'danger'); ?>"><?php echo ucfirst($cat['status']); ?></span></td>
                                                <td>
                                                    <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                                    <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
