<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// If already logged in, redirect to index
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3">Sign in</h1>
            <form id="loginForm" novalidate>
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" required />
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" required />
              </div>
              <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
            <div class="mt-3 text-center">
              <a href="register.php">No account? Register</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.37.1/minified.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      if (!username || !password) {
        Swal.fire({icon: 'error', title: 'Validation', text: 'Input fields must not be empty.'});
        return;
      }

      try {
        const res = await fetch('api.php?action=login', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({username, password})
        });
        const data = await res.json();
        if (!data.ok) {
          let msg = 'Login failed.';
          if (data.error === 'INVALID_CREDENTIALS') msg = 'Invalid username or password.';
          if (data.error === 'EMPTY_FIELDS') msg = 'Input fields must not be empty.';
          Swal.fire({icon: 'error', title: 'Error', text: msg});
          return;
        }
        window.location.href = 'index.php';
      } catch (err) {
        Swal.fire({icon: 'error', title: 'Network error', text: 'Please try again.'});
      }
    });
  </script>
</body>
</html>
