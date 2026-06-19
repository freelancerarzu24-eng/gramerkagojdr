<?php
include 'templates/header.php';

$slug = sanitize($_GET['slug'] ?? '');

$db->query("SELECT n.*, c.name_en as cat_en, c.name_bn as cat_bn, c.slug as cat_slug, u.full_name as author_name
            FROM news n
            JOIN categories c ON n.category_id = c.id
            JOIN users u ON n.author_id = u.id
            WHERE n.slug = :slug AND n.status = 'published'");
$db->bind(':slug', $slug);
$news = $db->single();

if (!$news) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>News not found.</div></div>";
    include 'templates/footer.php';
    exit;
}

// Update view count
$db->query("UPDATE news SET view_count = view_count + 1 WHERE id = :id");
$db->bind(':id', $news['id']);
$db->execute();

// Get related news
$db->query("SELECT * FROM news WHERE category_id = :cat_id AND id != :id AND status = 'published' ORDER BY id DESC LIMIT 4");
$db->bind(':cat_id', $news['category_id']);
$db->bind(':id', $news['id']);
$related_news = $db->resultSet();

?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><?php echo $translations['home']; ?></a></li>
            <li class="breadcrumb-item"><a href="category.php?slug=<?php echo $news['cat_slug']; ?>"><?php echo $news['cat_' . $lang]; ?></a></li>
            <li class="breadcrumb-item active"><?php echo $news['title_' . $lang]; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <article class="bg-white p-4 shadow-sm">
                <h1 class="mb-3"><?php echo $news['title_' . $lang]; ?></h1>

                <div class="d-flex justify-content-between text-secondary mb-3 border-bottom pb-2">
                    <div>
                        <i class="fas fa-user me-1"></i> <?php echo $news['author_name']; ?> |
                        <i class="fas fa-calendar-alt me-1"></i> <?php echo formatDate($news['publish_date'], $lang); ?>
                    </div>
                    <div>
                        <i class="fas fa-eye me-1"></i> <?php echo $news['view_count']; ?> |
                        <i class="fas fa-clock me-1"></i> <?php echo getReadingTime($news['content_' . $lang]); ?> min read
                    </div>
                </div>

                <?php if ($news['featured_image']): ?>
                    <img src="assets/uploads/news/<?php echo $news['featured_image']; ?>" class="img-fluid w-100 mb-4 rounded">
                <?php endif; ?>

                <div class="content mb-4" style="line-height: 1.8; font-size: 1.1rem;">
                    <?php echo $news['content_' . $lang]; ?>
                </div>

                <!-- Social Share -->
                <div class="border-top border-bottom py-3 mb-4 d-flex align-items-center">
                    <span class="fw-bold me-3"><?php echo $translations['share']; ?>:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="btn btn-primary btn-sm me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="btn btn-info btn-sm text-white me-2"><i class="fab fa-twitter"></i></a>
                    <a href="whatsapp://send?text=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" class="btn btn-success btn-sm me-2"><i class="fab fa-whatsapp"></i></a>
                    <button class="btn btn-secondary btn-sm" onclick="window.print()"><i class="fas fa-print"></i></button>
                </div>

                <!-- Related News -->
                <div class="mt-5">
                    <h4 class="border-bottom border-primary border-3 pb-2 mb-4"><?php echo $translations['related_news']; ?></h4>
                    <div class="row">
                        <?php foreach ($related_news as $rn): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <img src="assets/uploads/news/<?php echo $rn['featured_image']; ?>" width="100" height="70" class="me-3 object-fit-cover rounded">
                                    <a href="news_details.php?slug=<?php echo $rn['slug']; ?>" class="text-dark text-decoration-none fw-bold small"><?php echo $rn['title_' . $lang]; ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white"><?php echo $translations['popular_news']; ?></div>
                <div class="card-body p-0">
                    <!-- Popular news list logic here -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
