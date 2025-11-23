</main>
<footer class="site-footer">
    <div>
        <strong><?= htmlspecialchars($settings['branding']['store_name']) ?></strong>
        <p><?= htmlspecialchars($settings['branding']['tagline']) ?></p>
    </div>
    <div class="footer-grid">
        <div>
            <p class="muted">Support</p>
            <p><?= htmlspecialchars($settings['branding']['support_email'] ?? '') ?></p>
            <p><?= htmlspecialchars($settings['branding']['support_phone'] ?? '') ?></p>
        </div>
        <div>
            <p class="muted">Shipping</p>
            <?php $defaultShipping = shipping_default_option($settings); ?>
            <p><?= htmlspecialchars($defaultShipping['label'] ?? 'Express Courier') ?> · <?= htmlspecialchars($defaultShipping['eta'] ?? '24-48h') ?></p>
        </div>
        <div>
            <p class="muted">Payments</p>
            <p>Secure checkout with <?= htmlspecialchars(implode(', ', array_keys($settings['payments'] ?? []))) ?></p>
        </div>
        <div>
            <p class="muted">Visit</p>
            <p><?= htmlspecialchars($settings['branding']['address'] ?? '') ?></p>
            <p><?= htmlspecialchars($settings['branding']['hours'] ?? '') ?></p>
        </div>
    </div>
</footer>
</body>
</html>
