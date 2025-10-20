<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$username = htmlspecialchars((string)$user['username'], ENT_QUOTES, 'UTF-8');
$isAdmin = !empty($user['is_admin']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Welcome</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">User App</a>
      <div class="d-flex ms-auto">
        <?php if ($isAdmin): ?>
          <a href="all_users.php" class="btn btn-outline-light me-2">All Users</a>
        <?php endif; ?>
        <button class="btn btn-outline-light" id="logoutBtn">Logout</button>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <h1 class="mb-3">Hello there <?php echo $username; ?>.</h1>
    <p class="text-muted">This is a protected page. You are logged in.</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.37.1/minified.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('logoutBtn').addEventListener('click', async () => {
      try {
        const res = await fetch('api.php?action=logout');
        await res.json();
      } catch (e) {}
      window.location.href = 'login.php';
    });
  </script>
</body>
</html>
