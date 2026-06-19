<?php
include 'templates/header.php';

$slug = sanitize($_GET['slug'] ?? '');

$db->query("SELECT * FROM categories WHERE slug = :slug AND status = 'active'");
$db->bind(':slug', $slug);
$category = $db->single();

if (!$category) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Category not found.</div></div>";
    include 'templates/footer.php';
    exit;
}

// Initial News
$db->query("SELECT * FROM news WHERE category_id = :cat_id AND status = 'published' ORDER BY id DESC LIMIT 10");
$db->bind(':cat_id', $category['id']);
$cat_news = $db->resultSet();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2 class="border-bottom border-primary border-3 pb-2 mb-4"><?php echo $category['name_' . $lang]; ?></h2>

            <div id="news-container">
                <?php foreach ($cat_news as $news): ?>
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
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <button id="load-more" class="btn btn-primary px-5" data-category="<?php echo $category['id']; ?>" data-offset="10"><?php echo $translations['read_more']; ?></button>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Sidebar with Ads/Latest -->
        </div>
    </div>
</div>

<script>
document.getElementById('load-more')?.addEventListener('click', function() {
    const btn = this;
    const offset = btn.getAttribute('data-offset');
    const categoryId = btn.getAttribute('data-category');

    btn.innerHTML = 'Loading...';
    btn.disabled = true;

    fetch(`ajax/load_more.php?category_id=${categoryId}&offset=${offset}`)
        .then(response => response.text())
        .then(data => {
            if (data.trim() === '') {
                btn.innerHTML = 'No more news';
                btn.classList.replace('btn-primary', 'btn-secondary');
            } else {
                document.getElementById('news-container').insertAdjacentHTML('beforeend', data);
                btn.setAttribute('data-offset', parseInt(offset) + 10);
                btn.innerHTML = '<?php echo $translations['read_more']; ?>';
                btn.disabled = false;
            }
        });
});
</script>

<?php include 'templates/footer.php'; ?>
