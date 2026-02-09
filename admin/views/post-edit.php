<?php
declare(strict_types=1);

$displayTitle   = $old['title'] ?? $post['title'] ?? '';
$displayContent = $old['content'] ?? $post['content'] ?? '';
$displayStatus  = $old['status'] ?? $post['status'] ?? 'draft';
$displayMediaId = $old['featured_media_id'] ?? $post['featured_media_id'] ?? '';
$postId         = $post['id'] ?? 0;
?>

<section class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold"><?= htmlspecialchars((string)($title ?? 'Post bewerken'), ENT_QUOTES) ?></h1>
            <span class="text-sm text-gray-500 font-mono">ID: #<?= (int)$postId ?></span>
        </div>

        <?php require __DIR__ . '/partials/flash.php'; ?>

        <?php if (isset($isLockedByOther) && $isLockedByOther): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Vergrendeld:</strong> Dit bericht wordt momenteel bewerkt door
                <?= htmlspecialchars((string)($lockerName ?? 'iemand anders')) ?>.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= ADMIN_BASE_PATH ?>/posts/<?= (int)$postId ?>/update" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Titel</label>
                <input class="w-full border rounded px-3 py-2 <?= (isset($isLockedByOther) && $isLockedByOther) ? 'bg-gray-100' : '' ?>"
                       type="text" name="title" value="<?= htmlspecialchars((string)$displayTitle, ENT_QUOTES) ?>"
                        <?= (isset($isLockedByOther) && $isLockedByOther) ? 'disabled' : 'required' ?>>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Inhoud</label>
                <textarea class="w-full border rounded px-3 py-2 <?= (isset($isLockedByOther) && $isLockedByOther) ? 'bg-gray-100' : '' ?>"
                          name="content" rows="10"
                          <?= (isset($isLockedByOther) && $isLockedByOther) ? 'disabled' : 'required' ?>><?= htmlspecialchars((string)$displayContent, ENT_QUOTES) ?></textarea>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <?php if (!(isset($isLockedByOther) && $isLockedByOther)): ?>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 shadow-sm" type="submit">
                        Wijzigingen opslaan
                    </button>
                <?php endif; ?>
                <a class="px-4 py-2 rounded border bg-gray-50 hover:bg-gray-100 transition-colors" href="<?= ADMIN_BASE_PATH ?>/posts">Annuleren</a>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 rounded shadow h-fit">
        <h2 class="text-xl font-bold mb-4 border-b pb-2">Revisies</h2>

        <?php if (empty($revisions)): ?>
            <p class="text-gray-500 italic text-sm">Nog geen revisies.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($revisions as $revision): ?>
                    <div class="text-sm border-b pb-3 last:border-0">
                        <div class="font-semibold text-gray-700"><?= htmlspecialchars((string)$revision['created_at']) ?></div>
                        <div class="text-gray-500 truncate mb-2"><?= htmlspecialchars((string)$revision['title']) ?></div>
                        <div class="flex gap-3">
                            <a href="<?= ADMIN_BASE_PATH ?>/revisions/show/<?= (int)$revision['id'] ?>"
                               class="text-blue-600 hover:underline">Bekijken</a>

                            <form action="<?= ADMIN_BASE_PATH ?>/revisions/restore/<?= (int)$revision['id'] ?>"
                                  method="POST" onsubmit="return confirm('Herstellen?');">
                                <button type="submit" class="text-orange-600 hover:underline">Herstellen</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="text-xs text-gray-400 mt-4 italic">
            * De slug blijft ongewijzigd bij herstel.
        </p>
    </div>
</section>