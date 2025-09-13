<?php  
/**
 * Class for handling Offer-related operations.
 * Inherits CRUD methods from the Database class.
 */
class Offer extends Database {
    /**
     * Creates a new Offer.
     * @param string $title The Offer title.
     * @param string $content The Offer content.
     * @param int $author_id The ID of the author.
     * @return int The ID of the newly created Offer.
     */
    public function createOffer($user_id, $description, $proposal_id) {
        $sql = "INSERT INTO offers (user_id, description, proposal_id) VALUES (?, ?, ?)";
        return $this->executeNonQuery($sql, [$user_id, $description, $proposal_id]);
    }

    /**
     * Retrieves Offers from the database.
     * @param int|null $id The Offer ID to retrieve, or null for all Offers.
     * @return array
     */
    public function getOffers($offer_id = null) {
        if ($id) {
            $sql = "SELECT * FROM offers WHERE offer_id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT * FROM offers JOIN fiverr_clone_users ON 
                offers.user_id = fiverr_clone_users.user_id 
                ORDER BY offers.date_added DESC";
        return $this->executeQuery($sql);
    }


    public function getOffersByProposalID($proposal_id) {
        $sql = "SELECT 
                    offers.*, fiverr_clone_users.*, 
                    offers.date_added AS offer_date_added 
                FROM Offers 
                JOIN fiverr_clone_users ON 
                    offers.user_id = fiverr_clone_users.user_id
                WHERE proposal_id = ? 
                ORDER BY Offers.date_added DESC";
        return $this->executeQuery($sql, [$proposal_id]);
    }

    /**
     * Updates an Offer.
     * @param int $id The Offer ID to update.
     * @param string $title The new title.
     * @param string $content The new content.
     * @return int The number of affected rows.
     */
    public function updateOffer($description, $offer_id) {
        $sql = "UPDATE Offers SET description = ? WHERE Offer_id = ?";
        return $this->executeNonQuery($sql, [$description, $offer_id]);
    }
    

    /**
     * Deletes an Offer.
     * @param int $id The Offer ID to delete.
     * @return int The number of affected rows.
     */
    public function deleteOffer($id) {
        $sql = "DELETE FROM Offers WHERE Offer_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }
}
?>