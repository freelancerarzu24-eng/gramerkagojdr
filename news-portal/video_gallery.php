<?php
include 'templates/header.php';

$db->query("SELECT * FROM videos WHERE status = 'active' ORDER BY id DESC LIMIT 20");
$videos = $db->resultSet();
?>

<div class="container mt-4">
    <h2 class="border-bottom border-primary border-3 pb-2 mb-4"><?php echo $translations['video_gallery']; ?></h2>

    <div class="row">
        <?php if ($videos): ?>
            <?php foreach ($videos as $vid): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="ratio ratio-16x9">
                            <?php
                            // Simplified embedding logic for gallery
                            if ($vid['video_type'] == 'youtube') {
                                $vid_id = explode('v=', $vid['video_url'])[1] ?? '';
                                echo '<iframe src="https://www.youtube.com/embed/'.$vid_id.'" allowfullscreen></iframe>';
                            } else {
                                echo $vid['video_url']; // Fallback
                            }
                            ?>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title fw-bold"><?php echo $vid['title_' . $lang]; ?></h6>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-info">No videos found.</div></div>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
