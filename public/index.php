<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . '/../admin/autoload.php';

use Admin\Core\Database;
use Admin\Repositories\PostsRepository;
use Admin\Services\SlugService;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$uri = rtrim($uri, '/') ?: '/';

$pdo = Database::getConnection();
$postsRepository = new PostsRepository($pdo, new SlugService());

switch ($uri) {

    case '/':
        $posts = $postsRepository->getPublishedLatest(5);
        require __DIR__ . '/views/posts/home.php';
        break;

    case '/posts':
        $posts = $postsRepository->getAll();
        require __DIR__ . '/views/posts/index.php';
        break;

    default:
        // Detail route: matches alphanumeric and hyphens
        if (preg_match('#^/posts/([a-z0-9-]+)$#', $uri, $matches)) {
            $slug = $matches[1];
            $post = $postsRepository->findPublishedBySlug($slug);

            if (!$post) {
                http_response_code(404);
                echo '404 - Post niet gevonden';
                exit;
            }

            require __DIR__ . '/views/posts/show.php';
            exit;
        }

        http_response_code(404);
        echo '404 - Pagina niet gevonden';
        break;
}