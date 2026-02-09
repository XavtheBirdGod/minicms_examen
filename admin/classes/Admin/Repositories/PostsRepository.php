<?php
declare(strict_types=1);

namespace Admin\Repositories;

use Admin\Core\Database;
use Admin\Services\SlugService;
use PDO;

final class PostsRepository
{
    public function __construct(
        private PDO $pdo,
        private SlugService $slugService
    ) {}

    public static function make(): self
    {
        return new self(Database::getConnection(), new SlugService());
    }

    // Admin
    public function getAll(): array
    {
        // Added meta_title and meta_description
        $sql = "SELECT id, title, slug, content, status, featured_media_id, published_at, created_at, meta_title, meta_description
            FROM posts
            WHERE deleted_at IS NULL
            ORDER BY id DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(string $slug): ?array
    {
        // Added meta_title and meta_description
        $sql = "SELECT id, title, slug, content, status, featured_media_id, created_at, meta_title, meta_description
                FROM posts
                WHERE slug = :slug
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    // Frontend: homepage
    public function getPublishedLatest(int $limit = 6): array
    {
        $limit = max(1, min(50, $limit));

        // SELECT * already includes SEO fields automatically
        $sql = "SELECT * FROM posts
            WHERE status = 'published' 
            AND published_at <= NOW()
            AND deleted_at IS NULL
            ORDER BY published_at DESC
            LIMIT " . (int)$limit;

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Frontend: detailpagina /posts/{id}
    public function findPublishedBySlug(string $slug): ?array
    {
        // Added meta_title and meta_description
        $sql = "SELECT id, title, slug, content, status, featured_media_id, created_at, meta_title, meta_description
                FROM posts
                WHERE slug = :slug AND status = 'published'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    // Admin: create/update/delete
    public function create(
        string $title,
        string $content,
        string $status,
        ?int $featuredMediaId = null,
        ?string $publishedAt = null,
        ?string $metaTitle = null,      // New
        ?string $metaDescription = null // New
    ): int {
        $slug = $this->slugService->createSlug($title);

        $sql = "INSERT INTO posts (title, slug, content, status, featured_media_id, published_at, created_at, meta_title, meta_description)
            VALUES (:title, :slug, :content, :status, :featured_media_id, :published_at, NOW(), :meta_title, :meta_description)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'title'             => $title,
            'slug'              => $slug,
            'content'           => $content,
            'status'            => $status,
            'featured_media_id' => $featuredMediaId,
            'published_at'      => $publishedAt ?: date('Y-m-d H:i:s'),
            'meta_title'        => $metaTitle,
            'meta_description'  => $metaDescription,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(
        int $id,
        string $title,
        string $content,
        string $status,
        ?int $featuredMediaId = null,
        ?string $publishedAt = null,
        ?string $metaTitle = null,      // New
        ?string $metaDescription = null // New
    ): void {
        $slug = $this->slugService->createSlug($title);

        $sql = "UPDATE posts
            SET title = :title, 
                slug = :slug, 
                content = :content, 
                status = :status, 
                featured_media_id = :featured_media_id, 
                published_at = :published_at,
                meta_title = :meta_title,
                meta_description = :meta_description
            WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id'                => $id,
            'title'             => $title,
            'slug'              => $slug,
            'content'           => $content,
            'status'            => $status,
            'featured_media_id' => $featuredMediaId,
            'published_at'      => $publishedAt,
            'meta_title'        => $metaTitle,
            'meta_description'  => $metaDescription,
        ]);
    }

    public function findAdminById(int $id): ?array
    {
        // SELECT * already includes SEO fields
        $sql = "SELECT * FROM posts WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function deleteById(int $id): void
    {
        $sql = "UPDATE posts SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public function getAllDeleted(): array
    {
        $sql = "SELECT * FROM posts WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
// post lock - examen
    public function getPostWithLock(int $id)
    {
        $sql = "SELECT p.*, u.name AS locker_name 
        FROM posts p 
        LEFT JOIN users u ON p.locker_id = u.id 
        WHERE p.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLock($postId, $userId) {
        $sql = "UPDATE posts SET locker_id = :user_id, locked_at = NOW() WHERE id = :post_id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':post_id' => $postId
        ]);
    }

    public function releaseLock($postId) {
        $sql = "UPDATE posts SET locker_id = NULL, locked_at = NULL WHERE id = :post_id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':post_id' => $postId]);
    }
}