<?php

declare(strict_types=1);



namespace Admin\Controllers;



use Admin\Core\Flash;
use Admin\Core\View;
use Admin\Repositories\MediaRepository;
use Admin\Repositories\PostsRepository;



final class PostsController
{
    private PostsRepository $posts;
    public function __construct(PostsRepository $posts)
    {
        $this->posts = $posts;
    }
    public function index(): void
    {
        View::render('posts.php', [
            'title' => 'Posts',
            'posts' => $this->posts->getAll(),
        ]);
    }



    public function create(): void
    {
        $old = Flash::get('old');
        if (!is_array($old)) {
            $old = ['title' => '', 'content' => '', 'status' => 'draft', 'featured_media_id' => ''];
        }

        View::render('post-create.php', [
            'title' => 'Nieuwe post',
            'old' => $old,
            'media' => MediaRepository::make()->getAllImages(),
        ]);

    }

    public function store(): void
    {
        $title = trim((string)($_POST['title'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $status = (string)($_POST['status'] ?? 'draft');
        $publishedAt = $_POST['published_at'] ?: null; // Capture the date
        $featuredRaw = trim((string)($_POST['featured_media_id'] ?? ''));
        $featuredId = $this->normalizeFeaturedId($featuredRaw);
        $errors = $this->validate($title, $content, $status, $featuredId);

        if (!empty($errors)) {
            Flash::set('warning', $errors);
            Flash::set('old', compact('title', 'content', 'status') + ['featured_media_id' => $featuredRaw]);
            header('Location: ' . ADMIN_BASE_PATH . '/posts/create');
            exit;

        }
        $this->posts->create($title, $content, $status, $featuredId, $publishedAt);

        Flash::set('success', 'Post succesvol aangemaakt.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }



    public function edit(int $id): void
    {
        $post = $this->posts->getPostWithLock($id);

        if (!$post) {
            Flash::set('error', 'Post niet gevonden.');
            header('Location: ' . ADMIN_BASE_PATH . '/posts');
            exit;
        }

        $currentUserId = $_SESSION['user_id'] ?? 0;
        $lockLimit = 15 * 60; //(15 minuten)
        $isLockedByOther = false;

        if (!empty($post['locker_id']) && (int)$post['locker_id'] !== (int)$currentUserId) {
            $timePassed = time() - strtotime($post['locked_at']);
            if ($timePassed < $lockLimit) {
                $isLockedByOther = true;
            }
        }

        if (!$isLockedByOther) {
            $this->posts->updateLock($id, $currentUserId);
        }

        View::render('post-edit.php', [
            'title' => 'Post bewerken',
            'post'  => $post,
            'revisions' => $this->posts->getRevisions($id),
            'media' => MediaRepository::make()->getAllImages(),
            'isLockedByOther' => $isLockedByOther,
            'lockerName' => $post['locker_name'] ?? 'Onbekende admin'
        ]);
    }

    public function show(int $id): void

    {
        $post = $this->posts->findAdminById($id);
        if (!$post) {
            Flash::set('error', 'Post niet gevonden.');
            header('Location: ' . ADMIN_BASE_PATH . '/posts');
            exit;
        }

        View::render('post-show.php', [
            'title' => 'Post bekijken',
            'post' => $post,
        ]);

    }

    public function update(int $id): void
    {
        $post = $this->posts->getPostWithLock($id);

        $currentUserId = $_SESSION['user_id'] ?? 0;
        $lockLimit = 15 * 60;

        if ($post['locker_id'] && (int)$post['locker_id'] !== (int)$currentUserId) {
            $timePassed = time() - strtotime($post['locked_at']);
            if ($timePassed < $lockLimit) {
                Flash::set('error', 'Fout: Deze post is vergrendeld door een andere admin.');
                header('Location: ' . ADMIN_BASE_PATH . '/posts');
                exit;
            }
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $status = (string)($_POST['status'] ?? 'draft');
        $metaTitle = trim((string)($_POST['meta_title'] ?? ''));
        $metaDescription = trim((string)($_POST['meta_description'] ?? ''));

        $featuredRaw = trim((string)($_POST['featured_media_id'] ?? ''));

        $featuredId = $this->normalizeFeaturedId($featuredRaw);
        $errors = $this->validate($title, $content, $status, $featuredId);

        if (!empty($errors)) {
            Flash::set('warning', $errors);
            Flash::set('old', compact('title', 'content', 'status', 'meta_title', 'meta_description') + ['featured_media_id' => $featuredRaw]);
            header('Location: ' . ADMIN_BASE_PATH . '/posts/' . $id . '/edit');
            exit;
        }
        $this->posts->update(
            $id,
            $title,
            $content,
            $status,
            $featuredId,
            null,
            $metaTitle,
            $metaDescription
        );

        $this->posts->releaseLock($id);

        Flash::set('success', 'Post succesvol aangepast en revisie opgeslagen.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }


    public function deleteConfirm(int $id): void
    {
        $post = $this->posts->findAdminById($id);
        if (!$post) {
            Flash::set('error', 'Post niet gevonden.');
            header('Location: ' . ADMIN_BASE_PATH . '/posts');
            exit;
        }

        View::render('post-delete.php', [
            'title' => 'Post verwijderen',
            'post' => $post,
        ]);
    }

    public function delete(int $id): void
    {
        $this->posts->deleteById($id);
        Flash::set('success', 'Post succesvol verwijderd.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }


    private function normalizeFeaturedId(string $raw): ?int
    {
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }
        $id = (int)$raw;
        return $id > 0 ? $id : null;
    }


    private function validate(string $title, string $content, string $status, ?int $featuredId): array
    {
        $errors = [];
        if ($title === '') {
            $errors[] = 'Titel is verplicht.';
        } elseif (mb_strlen($title) < 3) {
            $errors[] = 'Titel moet minstens 3 tekens bevatten.';
        }

        if ($content === '') {
            $errors[] = 'Inhoud is verplicht.';
        } elseif (mb_strlen($content) < 10) {
            $errors[] = 'Inhoud moet minstens 10 tekens bevatten.';

        }

        if (!in_array($status, ['draft', 'published'], true)) {
            $errors[] = 'Status moet draft of published zijn.';
        }

        if ($featuredId !== null && MediaRepository::make()->findImageById($featuredId) === null) {
            $errors[] = 'Featured image is ongeldig.';
        }

        return $errors;
    }

    public function restore(int $revisionId): void
    {
        $this->posts->restoreRevision($revisionId);

        Flash::set('success', 'Revisie succesvol hersteld.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }

    public function showRevision(int $id): void
    {
        $revision = $this->posts->findRevisionById($id);

        if (!$revision) {
            Flash::set('error', 'Revisie niet gevonden.');
            header('Location: ' . ADMIN_BASE_PATH . '/posts');
            exit;
        }

        View::render('revision-show.php', [
            'title'    => 'Revisie bekijken',
            'revision' => $revision,
        ]);
    }

    /**
     * Herstelt een revisie
     */
    public function fullRestore(int $id): void
    {
        $this->posts->restoreRevision($id);

        Flash::set('success', 'De post is succesvol hersteld naar de geselecteerde versie.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }

}