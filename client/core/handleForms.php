<?php  
session_start();
require_once '../classloader.php';

$userObj = new User();
$offerObj = new Offer();
$db = new Database(); // ✅ Added to run manual queries safely
$categoryObj = new Category();

if (isset($_POST['insertNewUserBtn'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $contact_number = htmlspecialchars(trim($_POST['contact_number']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password == $confirm_password) {
            if (!$userObj->usernameExists($username)) {
                if ($userObj->registerUser($username, $email, $password, $contact_number)) {
                    header("Location: ../login.php");
                    exit;
                } else {
                    $_SESSION['message'] = "An error occurred with the query!";
                }
            } else {
                $_SESSION['message'] = "$username is already taken";
            }
        } else {
            $_SESSION['message'] = "Passwords do not match";
        }
    } else {
        $_SESSION['message'] = "Please fill in all fields";
    }
    $_SESSION['status'] = '400';
    header("Location: ../register.php");
    exit;
}

if (isset($_POST['loginUserBtn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        if ($userObj->loginUser($email, $password)) {
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['message'] = "Username/password invalid";
        }
    } else {
        $_SESSION['message'] = "Please fill in all fields";
    }
    $_SESSION['status'] = '400';
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['logoutUserBtn'])) {
    $userObj->logout();
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['updateUserBtn'])) {
    $contact_number = htmlspecialchars($_POST['contact_number']);
    $bio_description = htmlspecialchars($_POST['bio_description']);
    if ($userObj->updateUser($contact_number, $bio_description, $_SESSION['user_id'])) {
        header("Location: ../profile.php");
        exit;
    }
}

/**
 * Offer submission (with one-off check)
 */
if (isset($_POST['insertOfferBtn'])) {
    $user_id = $_SESSION['user_id'];
    $proposal_id = $_POST['proposal_id'];
    $description = htmlspecialchars($_POST['description']);

    // ✅ Check if offer already exists using a public safe query
    $stmt = $db->getConnection()->prepare(
        "SELECT offer_id FROM offers WHERE user_id = ? AND proposal_id = ?"
    );
    $stmt->execute([$user_id, $proposal_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "<script>alert('You already submitted an offer to this proposal.');window.history.back();</script>";
        exit;
    }

    if ($offerObj->createOffer($user_id, $description, $proposal_id)) {
        echo "<script>alert('Offer submitted successfully!');window.location.href='../index.php';</script>";
        exit;
    }
}

if (isset($_POST['updateOfferBtn'])) {
    $description = htmlspecialchars($_POST['description']);
    $offer_id = $_POST['offer_id'];
    if ($offerObj->updateOffer($description, $offer_id)) {
        $_SESSION['message'] = "Offer updated successfully!";
        $_SESSION['status'] = '200';
        header("Location: ../index.php");
        exit;
    }
}

if (isset($_POST['deleteOfferBtn'])) {
    $offer_id = $_POST['offer_id'];
    if ($offerObj->deleteOffer($offer_id)) {
        $_SESSION['message'] = "Offer deleted successfully!";
        $_SESSION['status'] = '200';
        header("Location: ../index.php");
        exit;
    }
}

// Fiverr Administrator: Category Management
if (isset($_POST['createCategoryBtn'])) {
    if (!empty($_POST['name'])) {
        $categoryObj->createCategory(trim($_POST['name']));
    }
    header("Location: ../manage_categories.php");
    exit;
}

if (isset($_POST['createSubcategoryBtn'])) {
    if (!empty($_POST['name']) && !empty($_POST['category_id'])) {
        $categoryObj->createSubcategory((int)$_POST['category_id'], trim($_POST['name']));
    }
    header("Location: ../manage_categories.php");
    exit;
}

if (isset($_POST['deleteCategoryBtn'])) {
    if (!empty($_POST['category_id'])) {
        $categoryObj->deleteCategory((int)$_POST['category_id']);
    }
    header("Location: ../manage_categories.php");
    exit;
}

if (isset($_POST['deleteSubcategoryBtn'])) {
    if (!empty($_POST['subcategory_id'])) {
        $categoryObj->deleteSubcategory((int)$_POST['subcategory_id']);
    }
    header("Location: ../manage_categories.php");
    exit;
}
