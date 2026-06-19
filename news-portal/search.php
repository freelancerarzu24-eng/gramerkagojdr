<?php
include 'templates/header.php';

$query = sanitize($_GET['q'] ?? '');

if (!empty($query)) {
    $db->query("SELECT n.*, c.name_en, c.name_bn FROM news n
                JOIN categories c ON n.category_id = c.id
                WHERE (n.title_en LIKE :q OR n.title_bn LIKE :q OR n.content_en LIKE :q OR n.content_bn LIKE :q)
                AND n.status = 'published' ORDER BY n.id DESC");
    $db->bind(':q', "%$query%");
    $search_results = $db->resultSet();
} else {
    $search_results = [];
}
?>

<div class="container mt-4">
    <h2 class="border-bottom border-primary border-3 pb-2 mb-4"><?php echo $translations['search_results']; ?>: "<?php echo $query; ?>"</h2>

    <div class="row">
        <div class="col-md-8">
            <?php if ($search_results): ?>
                <?php foreach ($search_results as $news): ?>
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="assets/uploads/news/<?php echo $news['featured_image']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <h5 class="card-title"><a href="news_details.php?slug=<?php echo $news['slug']; ?>" class="text-dark text-decoration-none fw-bold"><?php echo $news['title_' . $lang]; ?></a></h5>
                                    <p class="card-text"><small class="text-muted"><?php echo formatDate($news['publish_date'], $lang); ?></small></p>
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
            <!-- Search Sidebar -->
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
