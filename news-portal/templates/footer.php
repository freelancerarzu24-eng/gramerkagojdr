    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo $site_settings['site_name_' . $lang] ?? 'News Portal'; ?></h5>
                    <p><?php echo $site_settings['footer_text_' . $lang] ?? ''; ?></p>
                </div>
                <div class="col-md-2">
                    <h5><?php echo $translations['category']; ?></h5>
                    <ul class="list-unstyled">
                        <?php
                        $footer_cats = array_slice($menu_categories, 0, 5);
                        foreach ($footer_cats as $fc):
                        ?>
                            <li><a href="category.php?slug=<?php echo $fc['slug']; ?>" class="text-secondary text-decoration-none"><?php echo $fc['name_' . $lang]; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5><?php echo $translations['contact']; ?></h5>
                    <p>
                        <i class="fas fa-map-marker-alt me-2"></i> <?php echo $site_settings['address_' . $lang] ?? ''; ?><br>
                        <i class="fas fa-phone me-2"></i> <?php echo $site_settings['phone'] ?? ''; ?><br>
                        <i class="fas fa-envelope me-2"></i> <?php echo $site_settings['email'] ?? ''; ?>
                    </p>
                </div>
                <div class="col-md-3 text-center">
                    <h5><?php echo $translations['follow_us']; ?></h5>
                    <div class="fs-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div class="footer-bottom">
        <div class="container text-center">
            &copy; <?php echo date('Y'); ?> <?php echo $site_settings['site_name_' . $lang] ?? 'News Portal'; ?>. <?php echo $translations['all_rights_reserved']; ?>.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
