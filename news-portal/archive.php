<?php
include 'templates/header.php';

$date = sanitize($_GET['date'] ?? date('Y-m-d'));

$db->query("SELECT n.*, c.name_en, c.name_bn FROM news n
            JOIN categories c ON n.category_id = c.id
            WHERE DATE(n.publish_date) = :date AND n.status = 'published' ORDER BY n.id DESC");
$db->bind(':date', $date);
$archive_news = $db->resultSet();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2 class="border-bottom border-primary border-3 pb-2 mb-4"><?php echo $translations['archive']; ?>: <?php echo $date; ?></h2>

            <?php if ($archive_news): ?>
                <?php foreach ($archive_news as $news): ?>
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="assets/uploads/news/<?php echo $news['featured_image']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="card-title"><a href="news_details.php?slug=<?php echo $news['slug']; ?>" class="text-dark text-decoration-none fw-bold"><?php echo $news['title_' . $lang]; ?></a></h5>
                                    <p class="card-text"><small class="text-muted"><?php echo $news['name_' . $lang]; ?></small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info"><?php echo $translations['no_news_found']; ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><?php echo $translations['archive']; ?></div>
                <div class="card-body">
                    <form action="archive.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $date; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?php echo $translations['search']; ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
