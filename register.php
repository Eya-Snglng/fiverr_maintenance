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
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-7 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3">Create account</h1>
            <form id="registerForm" novalidate>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="username" required />
                </div>
                <div class="col-12 col-md-6">
                  <label for="firstname" class="form-label">First name</label>
                  <input type="text" class="form-control" id="firstname" required />
                </div>
                <div class="col-12 col-md-6">
                  <label for="lastname" class="form-label">Last name</label>
                  <input type="text" class="form-control" id="lastname" required />
                </div>
                <div class="col-12 col-md-6">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" required />
                </div>
                <div class="col-12 col-md-6">
                  <label for="confirm" class="form-label">Confirm password</label>
                  <input type="password" class="form-control" id="confirm" required />
                </div>
                <div class="col-12 col-md-6">
                  <label for="role" class="form-label">Role</label>
                  <select id="role" class="form-select">
                    <option value="user" selected>User</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
              </div>
              <button class="btn btn-primary w-100 mt-3" type="submit">Register</button>
            </form>
            <div class="mt-3 text-center">
              <a href="login.php">Already have an account? Sign in</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.37.1/minified.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const u = document.getElementById('username');
    let lastUsernameCheck = '';

    u.addEventListener('blur', async () => {
      const username = u.value.trim();
      if (!username || username === lastUsernameCheck) return;
      lastUsernameCheck = username;
      try {
        const res = await fetch('api.php?action=check_username&username=' + encodeURIComponent(username));
        const data = await res.json();
        if (data.ok && data.available === false) {
          Swal.fire({icon: 'warning', title: 'Username taken', text: 'Please choose a different username.'});
        }
      } catch (e) {
        // ignore
      }
    });

    const form = document.getElementById('registerForm');
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const username = document.getElementById('username').value.trim();
      const firstname = document.getElementById('firstname').value.trim();
      const lastname = document.getElementById('lastname').value.trim();
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirm').value;
      const role = document.getElementById('role').value;

      if (!username || !firstname || !lastname || !password || !confirm) {
        Swal.fire({icon: 'error', title: 'Validation', text: 'Input fields must not be empty.'});
        return;
      }
      if (password.length < 8) {
        Swal.fire({icon: 'error', title: 'Validation', text: 'Password must be at least 8 characters.'});
        return;
      }
      if (password !== confirm) {
        Swal.fire({icon: 'error', title: 'Validation', text: 'Passwords do not match.'});
        return;
      }

      try {
        // Check availability before submit
        const checkRes = await fetch('api.php?action=check_username&username=' + encodeURIComponent(username));
        const checkData = await checkRes.json();
        if (checkData.ok && checkData.available === false) {
          Swal.fire({icon: 'warning', title: 'Username taken', text: 'Please choose a different username.'});
          return;
        }

        const res = await fetch('api.php?action=register', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({username, firstname, lastname, password, is_admin: role === 'admin'})
        });
        const data = await res.json();
        if (!data.ok) {
          let msg = 'Registration failed.';
          if (data.error === 'USERNAME_TAKEN') msg = 'Username is already taken.';
          if (data.error === 'WEAK_PASSWORD') msg = 'Password must be at least 8 characters.';
          if (data.error === 'EMPTY_FIELDS') msg = 'Input fields must not be empty.';
          Swal.fire({icon: 'error', title: 'Error', text: msg});
          return;
        }
        Swal.fire({icon: 'success', title: 'Registered', text: 'Your account has been created.'}).then(() => {
          window.location.href = 'login.php';
        });
      } catch (err) {
        Swal.fire({icon: 'error', title: 'Network error', text: 'Please try again.'});
      }
    });
  </script>
</body>
</html>
