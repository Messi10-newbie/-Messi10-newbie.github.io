<footer class="an-footer mt-auto py-4">
    <div class="container">
        <div class="row gy-3 align-items-start">
            <div class="col-md-4">
                <h6 class="fw-bold text-white mb-2"><i class="bi bi-basket-fill me-1"></i><?= SITE_NAME ?></h6>
                <p class="text-white-50 small mb-0"><?= SITE_TAGLINE ?><br>Made for campus pre-orders and quick pick-up.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold text-white mb-2">Quick Links</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="<?= $rootPath ?? '' ?>index.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li><a href="<?= $rootPath ?? '' ?>index.php#stalls" class="text-white-50 text-decoration-none">Browse Stalls</a></li>
                    <li><a href="<?= $rootPath ?? '' ?>admin/index.php" class="text-white-50 text-decoration-none">Admin Dashboard</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold text-white mb-2">Contact</h6>
                <p class="text-white-50 small mb-0">
                    <i class="bi bi-geo-alt me-1"></i>Campus Food Court<br>
                    <i class="bi bi-clock me-1"></i>Pick-up slots run daily
                </p>
            </div>
        </div>
        <hr class="border-secondary mt-3">
        <p class="text-center text-white-50 small mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $cssPath ?? '' ?>js/app.js"></script>
<?php if (!empty($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>
</body>
</html>
