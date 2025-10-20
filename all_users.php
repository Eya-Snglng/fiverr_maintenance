<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['user']['is_admin'])) {
    header('Location: index.php');
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>All Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">User App</a>
    <div class="d-flex ms-auto">
      <a href="index.php" class="btn btn-outline-light me-2">Home</a>
      <button class="btn btn-outline-light" id="logoutBtn">Logout</button>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">All Users</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
  </div>

  <div class="mb-3">
    <input type="text" id="search" class="form-control" placeholder="Search by username, first or last name..." />
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle" id="usersTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>First name</th>
          <th>Last name</th>
          <th>Admin</th>
          <th>Date added</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addUserForm" novalidate>
          <div class="mb-3">
            <label class="form-label" for="au_username">Username</label>
            <input type="text" class="form-control" id="au_username" required />
          </div>
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label" for="au_firstname">First name</label>
              <input type="text" class="form-control" id="au_firstname" required />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="au_lastname">Last name</label>
              <input type="text" class="form-control" id="au_lastname" required />
            </div>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-12 col-md-6">
              <label class="form-label" for="au_password">Password</label>
              <input type="password" class="form-control" id="au_password" required />
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label" for="au_confirm">Confirm password</label>
              <input type="password" class="form-control" id="au_confirm" required />
            </div>
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="au_is_admin" />
            <label class="form-check-label" for="au_is_admin">Is admin</label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveUserBtn">Save user</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/core-js-bundle@3.37.1/minified.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const tbody = document.querySelector('#usersTable tbody');
  const search = document.getElementById('search');

  async function loadUsers(q = '') {
    try {
      const res = await fetch('api.php?action=list_users' + (q ? '&q=' + encodeURIComponent(q) : ''));
      const data = await res.json();
      if (!data.ok) {
        Swal.fire({icon: 'error', title: 'Error', text: 'Failed to load users.'});
        return;
      }
      tbody.innerHTML = '';
      for (const u of data.users) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${u.id}</td>
          <td>${escapeHtml(u.username)}</td>
          <td>${escapeHtml(u.firstname)}</td>
          <td>${escapeHtml(u.lastname)}</td>
          <td>${u.is_admin ? 'Yes' : 'No'}</td>
          <td>${u.date_added}</td>
          <td>
            <button class="btn btn-sm btn-outline-danger" data-user-id="${u.id}">Delete</button>
          </td>
        `;
        tbody.appendChild(tr);
      }
    } catch (e) {
      Swal.fire({icon: 'error', title: 'Network', text: 'Please try again.'});
    }
  }

  function escapeHtml(s) {
    const div = document.createElement('div');
    div.innerText = String(s ?? '');
    return div.innerHTML;
  }

  search.addEventListener('input', () => {
    const q = search.value.trim();
    loadUsers(q);
  });

  tbody.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-user-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-user-id');
    const confirm = await Swal.fire({icon: 'warning', title: 'Delete user?', text: 'This cannot be undone.', showCancelButton: true});
    if (!confirm.isConfirmed) return;
    try {
      const res = await fetch('api.php?action=delete_user', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: Number(id)})
      });
      const data = await res.json();
      if (!data.ok) throw new Error('Failed');
      loadUsers(search.value.trim());
    } catch (e) {
      Swal.fire({icon: 'error', title: 'Error', text: 'Failed to delete user.'});
    }
  });

  document.getElementById('saveUserBtn').addEventListener('click', async () => {
    const username = document.getElementById('au_username').value.trim();
    const firstname = document.getElementById('au_firstname').value.trim();
    const lastname = document.getElementById('au_lastname').value.trim();
    const password = document.getElementById('au_password').value;
    const confirmPw = document.getElementById('au_confirm').value;
    const is_admin = document.getElementById('au_is_admin').checked;

    if (!username || !firstname || !lastname || !password || !confirmPw) {
      Swal.fire({icon: 'error', title: 'Validation', text: 'Input fields must not be empty.'});
      return;
    }
    if (password.length < 8) {
      Swal.fire({icon: 'error', title: 'Validation', text: 'Password must be at least 8 characters.'});
      return;
    }
    if (password !== confirmPw) {
      Swal.fire({icon: 'error', title: 'Validation', text: 'Passwords do not match.'});
      return;
    }

    try {
      // Check username availability first
      const checkRes = await fetch('api.php?action=check_username&username=' + encodeURIComponent(username));
      const checkData = await checkRes.json();
      if (checkData.ok && checkData.available === false) {
        Swal.fire({icon: 'warning', title: 'Username taken', text: 'Please choose a different username.'});
        return;
      }

      const res = await fetch('api.php?action=add_user', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({username, firstname, lastname, password, is_admin})
      });
      const data = await res.json();
      if (!data.ok) {
        let msg = 'Failed to add user.';
        if (data.error === 'USERNAME_TAKEN') msg = 'Username is already taken.';
        if (data.error === 'WEAK_PASSWORD') msg = 'Password must be at least 8 characters.';
        if (data.error === 'EMPTY_FIELDS') msg = 'Input fields must not be empty.';
        Swal.fire({icon: 'error', title: 'Error', text: msg});
        return;
      }
      const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
      modal.hide();
      document.getElementById('addUserForm').reset();
      loadUsers(search.value.trim());
      Swal.fire({icon: 'success', title: 'User added', timer: 1200, showConfirmButton: false});
    } catch (e) {
      Swal.fire({icon: 'error', title: 'Network', text: 'Please try again.'});
    }
  });

  document.getElementById('logoutBtn').addEventListener('click', async () => {
    try { const res = await fetch('api.php?action=logout'); await res.json(); } catch (e) {}
    window.location.href = 'login.php';
  });

  loadUsers();
</script>
</body>
</html>
