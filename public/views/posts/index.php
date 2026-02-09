<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| View: posts index
|--------------------------------------------------------------------------
| Verwacht:
| - $posts (array)
*/

ob_start();
?>

    <header class="mb-8">
        <h1 class="text-3xl font-semibold">Posts</h1>
        <p class="text-slate-300 mt-2">Alle gepubliceerde posts.</p>
    </header>

<?php if (empty($posts)): ?>
    <div class="rounded-xl border border-white/10 bg-white/5 p-6 text-slate-300">
        Nog geen gepubliceerde posts.
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($posts as $post): ?>
            <article class="rounded-xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-xl font-semibold">
                    <a class="hover:underline" href="/posts/<?= $post['slug'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h2>

                <p class="text-sm text-slate-400 mt-2">
                    <?= htmlspecialchars((string)($post['created_at'] ?? '')) ?>
                </p>

                <p class="text-slate-300 mt-4">
                    <?php
                    $cleanText = strip_tags((string)$post['content']);
                    $preview = mb_strimwidth($cleanText, 0, 220, '...');
                    echo htmlspecialchars($preview);
                    ?>
                </p>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = 'Posts';
require __DIR__ . '/../layouts/public.php';
