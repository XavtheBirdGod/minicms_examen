<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| View: posts show
|--------------------------------------------------------------------------
| Verwacht:
| - $post (array)
*/

ob_start();
?>

    <a href="/posts" class="text-sm text-slate-300 hover:underline">← Terug naar overzicht</a>

    <article class="mt-6 rounded-xl border border-white/10 bg-white/5 p-8">
        <h1 class="text-3xl font-semibold">
            <?= htmlspecialchars($post['title']) ?>
        </h1>

        <p class="text-sm text-slate-400 mt-3">
            <?= htmlspecialchars((string)($post['created_at'] ?? '')) ?>
        </p>

        <div class="mt-8 text-slate-200 leading-relaxed">
            <?php
            // nl2br behoudt nieuwe lijnen in HTML
            echo nl2br(htmlspecialchars((string)$post['content']));
            ?>
        </div>
    </article>

<?php
$content = ob_get_clean();

/** * Stap 4: SEO Fallback Logic
 * variabelen voorbereiden
 */
$title = !empty($post['meta_title']) ? (string)$post['meta_title'] : (string)$post['title'];

$meta_description = !empty($post['meta_description']) ? (string)$post['meta_description'] : null;

require __DIR__ . '/../layouts/public.php';
