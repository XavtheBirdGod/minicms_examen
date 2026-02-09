<?php
declare(strict_types=1);

$displayTitle   = $old['title'] ?? $post['title'] ?? '';
$displayContent = $old['content'] ?? $post['content'] ?? '';
$displayStatus  = $old['status'] ?? $post['status'] ?? 'draft';
$displayMediaId = $old['featured_media_id'] ?? $post['featured_media_id'] ?? '';
$postId         = $post['id'] ?? 0;
?>

<section class="p-6">
    <div class="bg-white p-6 rounded shadow max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold"><?= htmlspecialchars((string)($title ?? 'Post bewerken'), ENT_QUOTES) ?></h1>
            <span class="text-sm text-gray-500 font-mono">ID: #<?= (int)$postId ?></span>
        </div>

        <?php require __DIR__ . '/partials/flash.php'; ?>

        <?php if (isset($isLockedByOther) && $isLockedByOther): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Vergrendeld:</strong> Dit bericht wordt momenteel bewerkt door
                <?= htmlspecialchars((string)($lockerName ?? 'iemand anders')) ?>.
                <br>
                <small>Wijzigingen kunnen mogelijk niet worden opgeslagen.</small>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= ADMIN_BASE_PATH ?>/posts/<?= (int)$postId ?>/update" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Titel</label>
                <input class="w-full border rounded px-3 py-2 <?= (isset($isLockedByOther) && $isLockedByOther) ? 'bg-gray-100' : '' ?>"
                       type="text"
                       name="title"
                       value="<?= htmlspecialchars((string)$displayTitle, ENT_QUOTES) ?>"
                        <?= (isset($isLockedByOther) && $isLockedByOther) ? 'disabled' : 'required' ?>>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Inhoud</label>
                <textarea class="w-full border rounded px-3 py-2 <?= (isset($isLockedByOther) && $isLockedByOther) ? 'bg-gray-100' : '' ?>"
                          name="content"
                          rows="10"
                          <?= (isset($isLockedByOther) && $isLockedByOther) ? 'disabled' : 'required' ?>><?= htmlspecialchars((string)$displayContent, ENT_QUOTES) ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Status</label>
                    <select class="w-full border rounded px-3 py-2" name="status" <?= (isset($isLockedByOther) && $isLockedByOther) ? 'disabled' : '' ?>>
                        <option value="draft" <?= $displayStatus === 'draft' ? 'selected' : '' ?>>draft</option>
                        <option value="published" <?= $displayStatus === 'published' ? 'selected' : '' ?>>published</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Featured image</label>
                    <select class="w-full border rounded px-3 py-2" name="featured_media_id" <?= (isset($isLockedByOther) && $isLockedByOther) ? 'disabled' : '' ?>>
                        <option value="">Geen</option>
                        <?php foreach (($media ?? []) as $item): ?>
                            <option value="<?= (int)$item['id'] ?>" <?= ((string)$item['id'] === (string)$displayMediaId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)$item['original_name'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <?php if (!(isset($isLockedByOther) && $isLockedByOther)): ?>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 shadow-sm" type="submit">
                        Wijzigingen opslaan
                    </button>
                <?php endif; ?>

                <a class="px-4 py-2 rounded border bg-gray-50 hover:bg-gray-100 transition-colors" href="<?= ADMIN_BASE_PATH ?>/posts">
                    <?= (isset($isLockedByOther) && $isLockedByOther) ? 'Terug naar overzicht' : 'Annuleren' ?>
                </a>
            </div>
        </form>
    </div>
</section>