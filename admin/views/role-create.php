<?php
declare(strict_types=1);
?>

<section class="p-6">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Nieuwe post</h2>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 p-4 border border-red-200 bg-red-50 rounded">
                <p class="font-bold mb-2">Controleer je invoer:</p>
                <ul class="list-disc pl-6">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars((string)$error, ENT_QUOTES); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="/admin/posts/store" class="space-y-4">
            <div>
                <label class="block text-sm font-bold mb-1" for="title">Titel</label>
                <input
                    class="w-full border rounded p-2"
                    type="text"
                    id="title"
                    name="title"
                    value="<?php echo htmlspecialchars((string)($old['title'] ?? ''), ENT_QUOTES); ?>"
                >
            </div>



            <div class="flex gap-4">
                <button class="border rounded px-4 py-2" type="submit">
                    Opslaan
                </button>

                <a class="underline" href="/admin/roles">Annuleren</a>
            </div>
        </form>
    </div>
</section>
