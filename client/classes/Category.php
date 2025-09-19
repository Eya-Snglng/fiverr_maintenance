<?php  
class Category extends Database {

	public function createCategory($name) {
		$sql = "INSERT INTO categories (name) VALUES (?)";
		return $this->executeNonQuery($sql, [$name]);
	}

	public function createSubcategory($category_id, $name) {
		$sql = "INSERT INTO subcategories (category_id, name) VALUES (?, ?)";
		return $this->executeNonQuery($sql, [$category_id, $name]);
	}

	public function getCategories() {
		$sql = "SELECT * FROM categories ORDER BY name";
		return $this->executeQuery($sql);
	}

	public function getSubcategoriesByCategory($category_id) {
		$sql = "SELECT * FROM subcategories WHERE category_id = ? ORDER BY name";
		return $this->executeQuery($sql, [$category_id]);
	}

	public function deleteCategory($category_id) {
		$sql = "DELETE FROM categories WHERE category_id = ?";
		return $this->executeNonQuery($sql, [$category_id]);
	}

	public function deleteSubcategory($subcategory_id) {
		$sql = "DELETE FROM subcategories WHERE subcategory_id = ?";
		return $this->executeNonQuery($sql, [$subcategory_id]);
	}
}
?>

