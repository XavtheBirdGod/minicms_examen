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
            $old = [
                'title' => '',
                'content' => '',
                'status' => 'draft',
                'featured_media_id' => '',
                'meta_title' => '',       // Added
                'meta_description' => ''  // Added
            ];
        }

        View::render('post-create.php', [
            'title' => 'Nieuwe post',
            'old' => $old,
            'media' => MediaRepository::make()->getAllImages(),
        ]);
    }

    public function store(): void
    {
        $title        = trim((string)($_POST['title'] ?? ''));
        $content      = trim((string)($_POST['content'] ?? ''));
        $status       = (string)($_POST['status'] ?? 'draft');
        $publishedAt  = $_POST['published_at'] ?: null;
        $featuredRaw  = trim((string)($_POST['featured_media_id'] ?? ''));

        // SEO Fields
        $metaTitle       = trim((string)($_POST['meta_title'] ?? '')) ?: null;
        $metaDescription = trim((string)($_POST['meta_description'] ?? '')) ?: null;

        $featuredId = $this->normalizeFeaturedId($featuredRaw);
        $errors = $this->validate($title, $content, $status, $featuredId, $metaTitle, $metaDescription);

        if (!empty($errors)) {
            Flash::set('warning', $errors);
            Flash::set('old', compact('title', 'content', 'status', 'meta_title', 'meta_description') + ['featured_media_id' => $featuredRaw]);
            header('Location: ' . ADMIN_BASE_PATH . '/posts/create');
            exit;
        }

        // Pass SEO fields to Repository
        $this->posts->create($title, $content, $status, $featuredId, $publishedAt, $metaTitle, $metaDescription);

        Flash::set('success', 'Post succesvol aangemaakt.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }

    public function edit(int $id): void
    {
        $post = $this->posts->findAdminById($id);

        if (!$post) {
            Flash::set('error', 'Post niet gevonden.');
            header('Location: ' . ADMIN_BASE_PATH . '/posts');
            exit;
        }

        $old = Flash::get('old');
        if (!is_array($old)) {
            $old = [
                'title' => (string)$post['title'],
                'content' => (string)$post['content'],
                'status' => (string)$post['status'],
                'featured_media_id' => (string)($post['featured_media_id'] ?? ''),
                'meta_title' => (string)($post['meta_title'] ?? ''),             // Added
                'meta_description' => (string)($post['meta_description'] ?? ''), // Added
            ];
        }

        View::render('post-edit.php', [
            'title' => 'Post bewerken',
            'post'  => $post,
            'old'   => $old,
            'media' => MediaRepository::make()->getAllImages(),
        ]);
    }

    public function update(int $id): void
    {
        $title   = trim((string)($_POST['title'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $status  = (string)($_POST['status'] ?? 'draft');
        $featuredRaw = trim((string)($_POST['featured_media_id'] ?? ''));

        // SEO Fields
        $metaTitle       = trim((string)($_POST['meta_title'] ?? '')) ?: null;
        $metaDescription = trim((string)($_POST['meta_description'] ?? '')) ?: null;

        $featuredId = $this->normalizeFeaturedId($featuredRaw);
        $errors = $this->validate($title, $content, $status, $featuredId, $metaTitle, $metaDescription);

        if (!empty($errors)) {
            Flash::set('warning', $errors);
            header('Location: ' . ADMIN_BASE_PATH . '/posts/' . $id . '/edit');
            exit;
        }

        // Pass SEO fields to Repository
        $this->posts->update($id, $title, $content, $status, $featuredId, null, $metaTitle, $metaDescription);

        Flash::set('success', 'Post succesvol aangepast.');
        header('Location: ' . ADMIN_BASE_PATH . '/posts');
        exit;
    }

    // ... (show, deleteConfirm, delete, normalizeFeaturedId remain the same) ...

    private function validate(string $title, string $content, string $status, ?int $featuredId, ?string $metaTitle = null, ?string $metaDescription = null): array
    {
        $errors = [];

        // Existing validations
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

        // SEO Validations
        if ($metaTitle && mb_strlen($metaTitle) > 60) {
            $errors[] = 'SEO Titel mag maximaal 60 tekens bevatten.';
        }

        if ($metaDescription && mb_strlen($metaDescription) > 160) {
            $errors[] = 'SEO Beschrijving mag maximaal 160 tekens bevatten.';
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            $errors[] = 'Status moet draft of published zijn.';
        }

        if ($featuredId !== null && MediaRepository::make()->findImageById($featuredId) === null) {
            $errors[] = 'Featured image is ongeldig.';
        }

        return $errors;
    }
}