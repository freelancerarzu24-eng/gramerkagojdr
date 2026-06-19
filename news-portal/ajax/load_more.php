<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$category_id = (int)($_GET['category_id'] ?? 0);
$offset = (int)($_GET['offset'] ?? 0);
$lang = $_SESSION['lang'] ?? 'bn';

$db = new Database();
$db->query("SELECT * FROM news WHERE category_id = :cat_id AND status = 'published' ORDER BY id DESC LIMIT 10 OFFSET :offset");
$db->bind(':cat_id', $category_id);
$db->bind(':offset', $offset);
$news_list = $db->resultSet();

if ($news_list) {
    foreach ($news_list as $news) {
        ?>
        <div class="card mb-4 news-card-horizontal shadow-sm border-0">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="assets/uploads/news/<?php echo $news['featured_image']; ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title"><a href="news_details.php?slug=<?php echo $news['slug']; ?>" class="text-dark text-decoration-none fw-bold"><?php echo $news['title_' . $lang]; ?></a></h5>
                        <p class="card-text text-secondary small"><?php echo substr(strip_tags($news['content_' . $lang]), 0, 150); ?>...</p>
                        <p class="card-text"><small class="text-muted"><?php echo formatDate($news['publish_date'], $lang); ?></small></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
