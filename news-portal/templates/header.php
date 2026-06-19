<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set language
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'] == 'en' ? 'en' : 'bn';
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'bn';
$translations = require "languages/{$lang}.php";

$db = new Database();

// Get settings
$db->query("SELECT * FROM settings LIMIT 1");
$site_settings = $db->single();

// Get categories for menu
$db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC");
$menu_categories = $db->resultSet();

// Get breaking news
$db->query("SELECT * FROM news WHERE is_breaking = 1 AND status = 'published' ORDER BY id DESC LIMIT 10");
$breaking_news = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $meta_title = $site_settings['site_name_' . $lang] ?? 'News Portal';
    echo generateSEOTags($meta_title, $meta_title, $meta_title);
    ?>
    <?php if (basename($_SERVER['PHP_SELF']) == 'news_details.php' && isset($news)): ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "<?php echo addslashes($news['title_' . $lang]); ?>",
      "image": ["<?php echo "http://$_SERVER[HTTP_HOST]/assets/uploads/news/" . $news['featured_image']; ?>"],
      "datePublished": "<?php echo $news['publish_date']; ?>",
      "dateModified": "<?php echo $news['updated_at']; ?>",
      "author": [{
          "@type": "Person",
          "name": "<?php echo $news['author_name']; ?>"
        }]
    }
    </script>
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <style>
        :root { --primary-color: #c00; --secondary-color: #333; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; }
        .top-bar { background: #fff; padding: 5px 0; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
        .header { background: #fff; padding: 20px 0; border-bottom: 2px solid var(--primary-color); }
        .logo h1 { color: var(--primary-color); font-weight: bold; margin: 0; }
        .navbar { background: #fff !important; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .nav-link { font-weight: 500; color: #333 !important; text-transform: uppercase; }
        .nav-link:hover { color: var(--primary-color) !important; }
        .breaking-news { background: #fff; margin-top: 10px; border: 1px solid #ddd; display: flex; overflow: hidden; }
        .breaking-title { background: var(--primary-color); color: #fff; padding: 5px 15px; font-weight: bold; white-space: nowrap; }
        .ticker { padding: 5px 15px; }
        .footer { background: #222; color: #ccc; padding: 40px 0; margin-top: 50px; }
        .footer h5 { color: #fff; margin-bottom: 20px; }
        .footer-bottom { background: #111; padding: 15px 0; color: #777; font-size: 0.9rem; }
        .news-card { background: #fff; border: 1px solid #ddd; margin-bottom: 20px; transition: 0.3s; }
        .news-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .news-card img { width: 100%; height: 200px; object-fit: cover; }
        .news-card .p-3 { padding: 15px !important; }
        .news-title { font-size: 1.1rem; font-weight: bold; color: #333; text-decoration: none; display: block; margin-top: 10px; }
        .news-title:hover { color: var(--primary-color); }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="far fa-calendar-alt me-1"></i> <?php echo date('l, F j, Y'); ?>
            </div>
            <div>
                <a href="?lang=en" class="text-dark text-decoration-none me-3">English</a>
                <a href="?lang=bn" class="text-dark text-decoration-none">বাংলা</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container text-center">
            <div class="logo">
                <a href="index.php" class="text-decoration-none">
                    <h1><?php echo $site_settings['site_name_' . $lang] ?? 'NEWS PORTAL'; ?></h1>
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><?php echo $translations['home']; ?></a></li>
                    <?php foreach ($menu_categories as $cat): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="category.php?slug=<?php echo $cat['slug']; ?>">
                                <?php echo $cat['name_' . $lang]; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form class="d-flex" action="search.php" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="<?php echo $translations['search']; ?>">
                    <button class="btn btn-outline-danger" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Breaking News -->
    <?php if ($breaking_news): ?>
    <div class="container">
        <div class="breaking-news">
            <div class="breaking-title"><?php echo $translations['breaking_news']; ?></div>
            <div class="ticker">
                <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
                    <?php foreach ($breaking_news as $bn): ?>
                        <a href="news_details.php?slug=<?php echo $bn['slug']; ?>" class="text-dark text-decoration-none me-4">
                            • <?php echo $bn['title_' . $lang]; ?>
                        </a>
                    <?php endforeach; ?>
                </marquee>
            </div>
        </div>
    </div>
    <?php endif; ?>
