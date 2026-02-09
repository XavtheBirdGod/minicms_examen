<section class="p-6">
    <div class="bg-white p-6 rounded shadow max-w-3xl">

        <?php if (!empty($post['deleted_at'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <strong>Let op!</strong> Deze post is verwijderd op <?php echo $post['deleted_at']; ?> en is niet zichtbaar voor bezoekers.
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold mb-4">
            <?php echo htmlspecialchars((string)$post['title'], ENT_QUOTES); ?>
        </h2>

        <p class="mb-6 whitespace-pre-wrap">
            <?php echo htmlspecialchars((string)$post['content'], ENT_QUOTES); ?>
        </p>

        <div class="text-sm text-gray-600">
            <span class="mr-4">Status: <?php echo htmlspecialchars((string)$post['status'], ENT_QUOTES); ?></span>
            <span class="mr-4">Gemaakt op: <?php echo htmlspecialchars((string)$post['created_at'], ENT_QUOTES); ?></span>
            <?php if (!empty($post['deleted_at'])): ?>
                <span class="text-red-600 font-bold">Verwijderd op: <?php echo $post['deleted_at']; ?></span>
            <?php endif; ?>
        </div>
        <div class="text-sm text-gray-600 mt-4">
            <span class="mr-4"><strong>Status:</strong> <?php echo htmlspecialchars((string)$post['status'], ENT_QUOTES); ?></span>

            <span class="mr-4">
        <strong>Publicatiedatum:</strong>
        <?php

        $pubDate = $post['published_at'] ?? $post['created_at'];
        echo htmlspecialchars((string)$pubDate, ENT_QUOTES);
        ?>

                <?php if (!empty($post['published_at']) && strtotime($post['published_at']) > time()): ?>
                    <span class="text-blue-600 font-bold">(Ingepland - Nog niet zichtbaar)</span>
                <?php endif; ?>
    </span>

            <span class="text-xs block mt-2 text-gray-400">Database ID: #<?php echo (int)$post['id']; ?> | Gemaakt op: <?php echo htmlspecialchars((string)$post['created_at'], ENT_QUOTES); ?></span>
        </div>

        <div class="mt-6 space-x-4">
            <a class="underline text-gray-500" href="<?php echo ADMIN_BASE_PATH; ?>/posts">Terug naar overzicht</a>
        </div>
    </div>
</section>