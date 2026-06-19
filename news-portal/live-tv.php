<?php
include 'templates/header.php';

$db->query("SELECT * FROM live_tv WHERE status = 'active' LIMIT 1");
$live_tv = $db->single();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h2 class="border-bottom border-primary border-3 pb-2 mb-4"><?php echo $translations['live_tv']; ?></h2>

            <?php if ($live_tv): ?>
                <div class="card shadow-sm border-0 bg-dark text-white">
                    <div class="card-header border-secondary">
                        <h4 class="mb-0 text-danger"><i class="fas fa-circle-play me-2"></i> <?php echo $live_tv['title_' . $lang]; ?></h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <?php echo $live_tv['embed_code']; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Live TV is currently unavailable.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
