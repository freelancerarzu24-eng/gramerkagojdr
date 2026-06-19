<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$db = new Database();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $site_name_en = sanitize($_POST['site_name_en']);
    $site_name_bn = sanitize($_POST['site_name_bn']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address_en = sanitize($_POST['address_en']);
    $address_bn = sanitize($_POST['address_bn']);
    $footer_text_en = sanitize($_POST['footer_text_en']);
    $footer_text_bn = sanitize($_POST['footer_text_bn']);

    // Check if settings exist
    $db->query("SELECT id FROM settings LIMIT 1");
    $settings_id = $db->single();

    if ($settings_id) {
        $db->query("UPDATE settings SET site_name_en = :site_name_en, site_name_bn = :site_name_bn, email = :email, phone = :phone, address_en = :address_en, address_bn = :address_bn, footer_text_en = :footer_text_en, footer_text_bn = :footer_text_bn WHERE id = :id");
        $db->bind(':id', $settings_id['id']);
    } else {
        $db->query("INSERT INTO settings (site_name_en, site_name_bn, email, phone, address_en, address_bn, footer_text_en, footer_text_bn) VALUES (:site_name_en, :site_name_bn, :email, :phone, :address_en, :address_bn, :footer_text_en, :footer_text_bn)");
    }

    $db->bind(':site_name_en', $site_name_en);
    $db->bind(':site_name_bn', $site_name_bn);
    $db->bind(':email', $email);
    $db->bind(':phone', $phone);
    $db->bind(':address_en', $address_en);
    $db->bind(':address_bn', $address_bn);
    $db->bind(':footer_text_en', $footer_text_en);
    $db->bind(':footer_text_bn', $footer_text_bn);

    if ($db->execute()) {
        setFlash('settings', 'Settings updated successfully.');
    } else {
        setFlash('settings', 'Failed to update settings.', 'alert alert-danger');
    }
    redirect('settings.php');
}

// Get current settings
$db->query("SELECT * FROM settings LIMIT 1");
$settings = $db->single();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Settings - Admin</title>
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
                <h2>Website Settings</h2>
                <?php displayFlash('settings'); ?>

                <div class="card mt-4 shadow-sm">
                    <div class="card-body">
                        <form action="settings.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site Name (English)</label>
                                    <input type="text" name="site_name_en" class="form-control" value="<?php echo $settings['site_name_en'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site Name (Bangla)</label>
                                    <input type="text" name="site_name_bn" class="form-control" value="<?php echo $settings['site_name_bn'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $settings['email'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo $settings['phone'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Address (English)</label>
                                    <textarea name="address_en" class="form-control"><?php echo $settings['address_en'] ?? ''; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Address (Bangla)</label>
                                    <textarea name="address_bn" class="form-control"><?php echo $settings['address_bn'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Footer Text (English)</label>
                                    <textarea name="footer_text_en" class="form-control"><?php echo $settings['footer_text_en'] ?? ''; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Footer Text (Bangla)</label>
                                    <textarea name="footer_text_bn" class="form-control"><?php echo $settings['footer_text_bn'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary px-5">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
