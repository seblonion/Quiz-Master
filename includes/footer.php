</main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Tous droits réservés</p>
        </div>
    </footer>
    
    <script src="assets/js/script.js"></script>
    <?php if (isset($scripts_supplementaires)): ?>
        <?php foreach ($scripts_supplementaires as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>