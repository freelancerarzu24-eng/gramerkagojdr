<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $title_en = sanitize($_POST['title_en']);
    $title_bn = sanitize($_POST['title_bn']);
    $category_id = (int)$_POST['category_id'];
    $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($title_en);
    $content_en = $_POST['content_en'];
    $content_bn = $_POST['content_bn'];
    $status = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $is_slider = isset($_POST['is_slider']) ? 1 : 0;
    $meta_title = sanitize($_POST['meta_title']);
    $meta_description = sanitize($_POST['meta_description']);

    $featured_image = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $target_dir = "../assets/uploads/news/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $featured_image = time() . '_' . basename($_FILES["featured_image"]["name"]);
        move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_dir . $featured_image);
    }

    $db->query("INSERT INTO news (category_id, author_id, title_en, title_bn, slug, content_en, content_bn, featured_image, is_featured, is_breaking, is_slider, status, meta_title, meta_description, publish_date)
                VALUES (:category_id, :author_id, :title_en, :title_bn, :slug, :content_en, :content_bn, :featured_image, :is_featured, :is_breaking, :is_slider, :status, :meta_title, :meta_description, NOW())");

    $db->bind(':category_id', $category_id);
    $db->bind(':author_id', $_SESSION['user_id']);
    $db->bind(':title_en', $title_en);
    $db->bind(':title_bn', $title_bn);
    $db->bind(':slug', $slug);
    $db->bind(':content_en', $content_en);
    $db->bind(':content_bn', $content_bn);
    $db->bind(':featured_image', $featured_image);
    $db->bind(':is_featured', $is_featured);
    $db->bind(':is_breaking', $is_breaking);
    $db->bind(':is_slider', $is_slider);
    $db->bind(':status', $status);
    $db->bind(':meta_title', $meta_title);
    $db->bind(':meta_description', $meta_description);

    if ($db->execute()) {
        setFlash('news', 'News added successfully.');
        redirect('news.php');
    } else {
        $error = "Something went wrong.";
    }
}

// Get categories
$db->query("SELECT * FROM categories WHERE status = 'active'");
$categories = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add News - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Add New Article</h4>
                    </div>
                    <div class="card-body">
                        <form action="news_add.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title (English)</label>
                                    <input type="text" name="title_en" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title (Bangla)</label>
                                    <input type="text" name="title_bn" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_en']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug (Optional)</label>
                                    <input type="text" name="slug" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Content (English)</label>
                                <textarea name="content_en" id="editor_en" class="form-control"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Content (Bangla)</label>
                                <textarea name="content_bn" id="editor_bn" class="form-control"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Featured Image</label>
                                    <input type="file" name="featured_image" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <div class="form-check me-3">
                                        <input type="checkbox" name="is_featured" class="form-check-input" id="feat">
                                        <label class="form-check-label" for="feat">Featured</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input type="checkbox" name="is_breaking" class="form-check-input" id="break">
                                        <label class="form-check-label" for="break">Breaking</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_slider" class="form-check-input" id="slide">
                                        <label class="form-check-label" for="slide">Slider</label>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5>SEO Settings</h5>
                            <div class="mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" class="form-control"></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="news.php" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Save Article</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        ClassicEditor.create(document.querySelector('#editor_en')).catch(error => { console.error(error); });
        ClassicEditor.create(document.querySelector('#editor_bn')).catch(error => { console.error(error); });
    </script>
</body>
</html>
