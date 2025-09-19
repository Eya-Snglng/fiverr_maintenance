<?php  
class Category extends Database {

	public function getCategories() {
		$sql = "SELECT * FROM categories ORDER BY name";
		return $this->executeQuery($sql);
	}

	public function getSubcategoriesByCategory($category_id) {
		$sql = "SELECT * FROM subcategories WHERE category_id = ? ORDER BY name";
		return $this->executeQuery($sql, [$category_id]);
	}
}
?>

