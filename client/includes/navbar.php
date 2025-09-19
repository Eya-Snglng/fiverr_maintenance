<nav class="navbar navbar-expand-lg navbar-dark p-4" style="background-color: #023E8A;">
  <a class="navbar-brand" href="index.php">Client Panel</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="catDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Categories</a>
        <div class="dropdown-menu" aria-labelledby="catDropdown">
          <?php foreach ($categoryObj->getCategories() as $cat) { ?>
            <h6 class="dropdown-header"><?php echo htmlspecialchars($cat['name']); ?></h6>
            <?php $subs = $categoryObj->getSubcategoriesByCategory($cat['category_id']); ?>
            <?php foreach ($subs as $sub) { ?>
              <a class="dropdown-item" href="#"><?php echo htmlspecialchars($sub['name']); ?></a>
            <?php } ?>
            <div class="dropdown-divider"></div>
          <?php } ?>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="project_offers_submitted.php">Project Offers Submitted </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="profile.php">Profile</a>
      </li>
      <?php if (isset($_SESSION['is_fiverr_admin']) && $_SESSION['is_fiverr_admin']) { ?>
      <li class="nav-item">
        <a class="nav-link" href="manage_categories.php">Manage Categories</a>
      </li>
      <?php } ?>
      <li class="nav-item">
        <a class="nav-link" href="core/handleForms.php?logoutUserBtn=1">Logout</a>
      </li>
    </ul>
  </div>
</nav>

