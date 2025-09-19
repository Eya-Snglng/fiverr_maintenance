<?php require_once 'classloader.php'; ?>
<?php 
if (!$userObj->isLoggedIn()) {
	header("Location: login.php");
}

// Allow: fiverr administrator or client (acting as admin). We'll treat is_client or is_fiverr_admin as access
$me = $userObj->getUsers($_SESSION['user_id']);
$isFiverrAdmin = isset($me['is_fiverr_admin']) && (int)$me['is_fiverr_admin'] === 1;
if (!$isFiverrAdmin) {
	header("Location: index.php");
}
?>
<!doctype html>
  <html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <style>
      body {
        font-family: "Arial";
      }
    </style>
  </head>
  <body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
      <div class="display-4 text-center">Manage Categories</div>
      <div class="row">
        <div class="col-md-4">
          <div class="card mt-4">
            <div class="card-header">Add Category</div>
            <form action="core/handleForms.php" method="POST">
              <div class="card-body">
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" class="form-control" name="name" required>
                </div>
                <input type="submit" class="btn btn-primary" name="createCategoryBtn" value="Add Category">
              </div>
            </form>
          </div>

          <div class="card mt-4">
            <div class="card-header">Add Subcategory</div>
            <form action="core/handleForms.php" method="POST">
              <div class="card-body">
                <div class="form-group">
                  <label>Category</label>
                  <select name="category_id" class="form-control" required>
                    <option value="">Select category</option>
                    <?php foreach ($categoryObj->getCategories() as $cat) { ?>
                      <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" class="form-control" name="name" required>
                </div>
                <input type="submit" class="btn btn-primary" name="createSubcategoryBtn" value="Add Subcategory">
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card mt-4">
            <div class="card-header">Categories & Subcategories</div>
            <div class="card-body">
              <?php foreach ($categoryObj->getCategories() as $cat) { ?>
                <div class="border rounded p-3 mb-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo htmlspecialchars($cat['name']); ?></h5>
                    <form action="core/handleForms.php" method="POST" class="mb-0">
                      <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                      <input type="submit" name="deleteCategoryBtn" class="btn btn-sm btn-danger" value="Delete">
                    </form>
                  </div>
                  <ul class="mt-2">
                    <?php foreach ($categoryObj->getSubcategoriesByCategory($cat['category_id']) as $sub) { ?>
                      <li class="d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($sub['name']); ?></span>
                        <form action="core/handleForms.php" method="POST" class="mb-0">
                          <input type="hidden" name="subcategory_id" value="<?php echo $sub['subcategory_id']; ?>">
                          <input type="submit" name="deleteSubcategoryBtn" class="btn btn-sm btn-outline-danger" value="Remove">
                        </form>
                      </li>
                    <?php } ?>
                  </ul>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
  </html>

