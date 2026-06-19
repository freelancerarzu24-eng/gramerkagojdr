<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$db = new Database();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM users WHERE id = :id");
    $db->bind(':id', $id);
    if ($db->execute()) {
        setFlash('users', 'User deleted successfully.');
    } else {
        setFlash('users', 'Failed to delete user.', 'alert alert-danger');
    }
    redirect('users.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $role_id = (int)$_POST['role_id'];
    $status = $_POST['status'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        $sql = "UPDATE users SET username = :username, email = :email, full_name = :full_name, role_id = :role_id, status = :status";
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql .= ", password = :password";
        }
        $sql .= " WHERE id = :id";
        $db->query($sql);
        $db->bind(':id', $id);
        if (!empty($_POST['password'])) $db->bind(':password', $password);
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $db->query("INSERT INTO users (username, email, password, full_name, role_id, status) VALUES (:username, :email, :password, :full_name, :role_id, :status)");
        $db->bind(':password', $password);
    }

    $db->bind(':username', $username);
    $db->bind(':email', $email);
    $db->bind(':full_name', $full_name);
    $db->bind(':role_id', $role_id);
    $db->bind(':status', $status);

    if ($db->execute()) {
        setFlash('users', 'User saved successfully.');
    } else {
        setFlash('users', 'Failed to save user.', 'alert alert-danger');
    }
    redirect('users.php');
}

// Get all users
$db->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC");
$users = $db->resultSet();

// Get roles
$db->query("SELECT * FROM roles");
$roles = $db->resultSet();

// Get user for edit
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $id);
    $edit_user = $db->single();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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

            <div class="col-md-10 main-content">
                <h2>Manage Users & Reporters</h2>
                <?php displayFlash('users'); ?>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><?php echo $edit_user ? 'Edit' : 'Add'; ?> User</div>
                            <div class="card-body">
                                <form action="users.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <?php if ($edit_user): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo $edit_user['full_name'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?php echo $edit_user['username'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $edit_user['email'] ?? ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password <?php echo $edit_user ? '(Leave blank to keep current)' : ''; ?></label>
                                        <input type="password" name="password" class="form-control" <?php echo $edit_user ? '' : 'required'; ?>>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role_id" class="form-select" required>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo $role['id']; ?>" <?php echo (isset($edit_user['role_id']) && $edit_user['role_id'] == $role['id']) ? 'selected' : ''; ?>><?php echo $role['role_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo (isset($edit_user['status']) && $edit_user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($edit_user['status']) && $edit_user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><?php echo $edit_user ? 'Update' : 'Save'; ?></button>
                                    <?php if ($edit_user): ?>
                                        <a href="users.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">User List</div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?php echo $u['full_name']; ?> (<?php echo $u['username']; ?>)</td>
                                                <td><?php echo $u['role_name']; ?></td>
                                                <td><span class="badge bg-<?php echo ($u['status'] == 'active' ? 'success' : 'danger'); ?>"><?php echo ucfirst($u['status']); ?></span></td>
                                                <td>
                                                    <a href="users.php?edit=<?php echo $u['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                                        <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                                    <?php endif; ?>
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
