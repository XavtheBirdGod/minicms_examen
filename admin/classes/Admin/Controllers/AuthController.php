<?php
declare(strict_types=1);

namespace Admin\Controllers;

use Admin\Core\View;
use Admin\Core\Auth;
use Admin\Core\Flash; // Added this import
use Admin\Repositories\UsersRepository;

class AuthController
{
    private UsersRepository $usersRepository;

    private string $clientId = "Ov23li0kjPSj2ECRCQ1i";
    private string $clientSecret = "6ddb5f379134ccd5b6b4bee9b5c1f59da3e869ce";
    private string $redirectUri = "http://minicms.test/admin";

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    public function showLogin(): void
    {
        View::render('login.php', [
            'title' => 'Login',
            'errors' => [],
            'old' => ['email' => ''],
        ]);
    }

    public function login(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            View::render('login.php', [
                'title' => 'Login',
                'errors' => ['Email en wachtwoord zijn verplicht.'],
                'old' => ['email' => $email],
            ]);
            return;
        }

        $user = $this->usersRepository->findByEmail($email);

        if ($user === null || !password_verify($password, (string)$user['password_hash'])) {
            View::render('login.php', [
                'title' => 'Login',
                'errors' => ['Deze login is niet correct.'],
                'old' => ['email' => $email],
            ]);
            return;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_role'] = (string)$user['role_name'];

        header('Location: /admin');
        exit;
    }

    /**
     * Redirect user to GitHub
     */
    public function githubRedirect(): void
    {
        $url = "https://github.com/login/oauth/authorize?client_id={$this->clientId}&redirect_uri={$this->redirectUri}&scope=user:email";
        header("Location: $url");
        exit;
    }

    /**
     * Handle the GitHub Response
     */
    public function githubCallback(): void
    {
        $code = $_GET['code'] ?? null;
        var_dump("Code: " . $code);

        $tokenData = $this->getGitHubToken($code);
        var_dump($tokenData);

        if (!$code) {
            Flash::set('GitHub login geannuleerd.', 'error');
            header('Location: /admin/login');
            exit;
        }

        $tokenData = $this->getGitHubToken($code);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            Flash::set('Kon geen toegangstoken ophalen van GitHub.', 'error');
            header('Location: /admin/login');
            exit;
        }

        $githubUser = $this->getGitHubUser($accessToken);
        $externalId = (string)$githubUser['id'];

        $email = $githubUser['email'] ?? $this->getGitHubEmail($accessToken);
        $name = $githubUser['name'] ?? $githubUser['login'];

        $user = $this->usersRepository->findByExternalId('github', $externalId);

        if ($user === null) {
            $newUserId = $this->usersRepository->createExternalUser($email, $name, 'github', $externalId, 1);
            $user = $this->usersRepository->findById($newUserId);
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_role'] = (string)$user['role_name'];

        Flash::set("Welkom terug, " . $user['name'], "success");
        header('Location: /admin');
        exit;
    }

    private function getGitHubToken(string $code): array
    {
        $ch = curl_init('https://github.com/login/oauth/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ADD THIS LINE
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);

        // DEBUG: Check for cURL errors
        if(curl_errno($ch)) { die('Curl error: ' . curl_error($ch)); }

        curl_close($ch);
        return json_decode($response, true) ?? [];
    }

    private function getGitHubUser(string $token): array
    {
        $ch = curl_init('https://api.github.com/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ADD THIS LINE
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'User-Agent: MiniCMS-App'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }

    private function getGitHubEmail(string $token): ?string
    {
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'User-Agent: MiniCMS-App'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $emails = json_decode($response, true) ?? [];

        foreach ($emails as $email) {
            if (($email['primary'] ?? false) && ($email['verified'] ?? false)) {
                return (string)$email['email'];
            }
        }
        return null;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /admin/login');
        exit;
    }
}