<?php
/**
 * ===================================================================
 * Archivo: footer.php
 * Propósito: Parte inferior del layout HTML del dashboard. 
 *            Cierra las etiquetas principales container y carga 
 *            los archivos JavaScript requeridos, incluyendo 
 *            scripts dinámicos inyectados por otras páginas.
 *
 * Variables esperadas antes de hacer include:
 *   $extra_scripts (array, opcional) — URLs de JS adicionales
 * ===================================================================
 */
$extra_scripts = $extra_scripts ?? [];
?>
</main>
</div><!-- /.dashboard-container -->

<!-- Dashboard Core Script -->
<script src="<?= BASE_URL ?>/src/JavaScript/dashboard.js"></script>

<?php foreach ($extra_scripts as $script): ?>
    <script src="<?php echo htmlspecialchars($script); ?>"></script>
<?php endforeach; ?>
</body>

</html>