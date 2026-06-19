<?php
require_once 'news-portal/config/database.php';

$db = new Database();

echo "Starting Seeding...\n";

// 1. Create Super Admin
$password = password_hash('admin123', PASSWORD_DEFAULT);
$db->query("INSERT INTO users (role_id, username, email, password, full_name, status) VALUES (1, 'admin', 'admin@example.com', :pass, 'Super Admin', 'active')");
$db->bind(':pass', $password);
if($db->execute()) echo "Admin created.\n";

// 2. Create Categories
$categories = [
    ['National', 'জাতীয়', 'national'],
    ['International', 'আন্তর্জাতিক', 'international'],
    ['Politics', 'রাজনীতি', 'politics'],
    ['Sports', 'খেলাধুলা', 'sports'],
    ['Entertainment', 'বিনোদন', 'entertainment'],
    ['Technology', 'তথ্যপ্রযুক্তি', 'technology']
];

foreach ($categories as $cat) {
    $db->query("INSERT INTO categories (name_en, name_bn, slug) VALUES (:en, :bn, :slug)");
    $db->bind(':en', $cat[0]);
    $db->bind(':bn', $cat[1]);
    $db->bind(':slug', $cat[2]);
    $db->execute();
    echo "Category {$cat[0]} created.\n";
}

// 3. Create Sample News
$db->query("SELECT id FROM categories LIMIT 1");
$cat_id = $db->single()['id'];

for($i=1; $i<=5; $i++) {
    $db->query("INSERT INTO news (category_id, author_id, title_en, title_bn, slug, content_en, content_bn, status, is_slider, publish_date)
                VALUES (:cat, 1, :title_en, :title_bn, :slug, :cont_en, :cont_bn, 'published', 1, NOW())");
    $db->bind(':cat', $cat_id);
    $db->bind(':title_en', "Sample News Title $i");
    $db->bind(':title_bn', "নমুনা সংবাদের শিরোনাম $i");
    $db->bind(':slug', "sample-news-$i");
    $db->bind(':cont_en', "This is the content for sample news $i in English.");
    $db->bind(':cont_bn', "এটি ইংরেজি নমুনা সংবাদের বিষয়বস্তু $i।");
    $db->execute();
    echo "News $i created.\n";
}

echo "Seeding completed.\n";
?>
