<?php
if (file_exists('install.lock')) {
    die("Installation is locked. Remove 'install/install.lock' to reinstall.");
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';

if ($step == 2 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['db_host'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $name = $_POST['db_name'];

    try {
        $conn = new PDO("mysql:host=$host", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $conn->exec("USE `$name` text"); // Just to check if we can use it

        // Read SQL file
        $sql = file_get_contents('../database.sql');
        $conn->exec($sql);

        // Create Admin Account
        $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (role_id, username, email, password, full_name, status) VALUES (1, :user, :email, :pass, 'Super Admin', 'active')");
        $stmt->execute([
            ':user' => $_POST['admin_user'],
            ':email' => $_POST['admin_email'],
            ':pass' => $admin_pass
        ]);

        // Write config file
        $config = "<?php\n" .
                 "define('DB_HOST', '$host');\n" .
                 "define('DB_USER', '$user');\n" .
                 "define('DB_PASS', '$pass');\n" .
                 "define('DB_NAME', '$name');\n" .
                 "?>";
        file_put_contents('../config/database.php', $config);

        // Lock installation
        file_put_contents('install.lock', time());

        $step = 3;
    } catch (PDOException $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>News Portal Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h4 class="mb-0">News Portal Setup Wizard</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                            <h5>Step 1: Welcome</h5>
                            <p>This wizard will configure your database and create your Super Admin account.</p>
                            <a href="?step=2" class="btn btn-primary">Start Installation</a>
                        <?php elseif ($step == 2): ?>
                            <form method="POST">
                                <h6 class="text-primary mb-3">Database Connection</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Host</label>
                                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">DB Name</label>
                                        <input type="text" name="db_name" class="form-control" value="news_portal" required>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small">User</label>
                                        <input type="text" name="db_user" class="form-control" value="root" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Password</label>
                                        <input type="password" name="db_pass" class="form-control">
                                    </div>
                                </div>

                                <h6 class="text-primary mb-3">Super Admin Account</h6>
                                <div class="mb-3">
                                    <label class="form-label small">Username</label>
                                    <input type="text" name="admin_user" class="form-control" value="admin" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Email</label>
                                    <input type="email" name="admin_email" class="form-control" placeholder="admin@example.com" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small">Password</label>
                                    <input type="password" name="admin_pass" class="form-control" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Install Now</button>
                                </div>
                            </form>
                        <?php elseif ($step == 3): ?>
                            <div class="text-center">
                                <div class="alert alert-success">
                                    🎉 Installation completed successfully!
                                </div>
                                <p>The system is now configured. For security, the installer has been locked.</p>
                                <div class="d-grid gap-2">
                                    <a href="../admin/index.php" class="btn btn-dark">Login to Admin Panel</a>
                                    <a href="../index.php" class="btn btn-outline-primary">View Website</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
