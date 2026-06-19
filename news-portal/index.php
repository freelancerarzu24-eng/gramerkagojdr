<?php
// (Re-writing index.php to include category blocks as well)
include 'templates/header.php';

// Get Slider News
$db->query("SELECT * FROM news WHERE is_slider = 1 AND status = 'published' ORDER BY id DESC LIMIT 5");
$slider_news = $db->resultSet();

// Get Latest News (excluding slider)
$db->query("SELECT n.*, c.name_en, c.name_bn FROM news n JOIN categories c ON n.category_id = c.id WHERE n.status = 'published' ORDER BY n.id DESC LIMIT 8");
$latest_news = $db->resultSet();

// Helper to get news by category
function getCategoryNews($db, $category_id, $limit = 4) {
    $db->query("SELECT * FROM news WHERE category_id = :cat_id AND status = 'published' ORDER BY id DESC LIMIT :limit");
    $db->bind(':cat_id', $category_id);
    $db->bind(':limit', $limit);
    return $db->resultSet();
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Hero Section -->
        <div class="col-md-8">
            <div class="swiper hero-slider">
                <div class="swiper-wrapper">
                    <?php if ($slider_news): ?>
                        <?php foreach ($slider_news as $sn): ?>
                            <div class="swiper-slide">
                                <div class="position-relative">
                                    <img src="assets/uploads/news/<?php echo $sn['featured_image']; ?>" class="d-block w-100" style="height: 450px; object-fit: cover;">
                                    <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white" style="background: linear-gradient(transparent, rgba(0,0,0,0.8));">
                                        <h3><a href="news_details.php?slug=<?php echo $sn['slug']; ?>" class="text-white text-decoration-none"><?php echo $sn['title_' . $lang]; ?></a></h3>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white font-weight-bold">
                    <?php echo $translations['latest_news']; ?>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($latest_news as $ln): ?>
                        <li class="list-group-item">
                            <a href="news_details.php?slug=<?php echo $ln['slug']; ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                <img src="assets/uploads/news/<?php echo $ln['featured_image']; ?>" width="60" height="40" class="me-2 rounded">
                                <span class="small font-weight-bold"><?php echo $ln['title_' . $lang]; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Category News Blocks -->
    <div class="row mt-5">
        <?php foreach ($menu_categories as $cat): ?>
            <?php
            $cat_news = getCategoryNews($db, $cat['id']);
            if (!$cat_news) continue;
            ?>
            <div class="col-md-6 mb-4">
                <div class="d-flex justify-content-between align-items-center border-bottom border-primary border-3 mb-3">
                    <h4 class="bg-primary text-white px-3 py-1 mb-0"><?php echo $cat['name_' . $lang]; ?></h4>
                    <a href="category.php?slug=<?php echo $cat['slug']; ?>" class="text-primary text-decoration-none"><?php echo $translations['view_all']; ?></a>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="news-card">
                            <img src="assets/uploads/news/<?php echo $cat_news[0]['featured_image']; ?>">
                            <div class="p-3">
                                <a href="news_details.php?slug=<?php echo $cat_news[0]['slug']; ?>" class="news-title"><?php echo $cat_news[0]['title_' . $lang]; ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <?php for($i=1; $i<count($cat_news); $i++): ?>
                                <li class="mb-2 pb-2 border-bottom">
                                    <a href="news_details.php?slug=<?php echo $cat_news[$i]['slug']; ?>" class="text-dark text-decoration-none"><?php echo $cat_news[$i]['title_' . $lang]; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if(document.querySelector('.hero-slider')){
            new Swiper('.hero-slider', {
                loop: true,
                pagination: { el: '.swiper-pagination', clickable: true },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                autoplay: { delay: 5000 },
            });
        }
    });
</script>

<?php include 'templates/footer.php'; ?>
