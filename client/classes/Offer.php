<?php  
class Offer extends Database {

    public function createOffer($user_id, $description, $proposal_id) {
        // Check if this user already submitted an offer to this proposal
        $checkSql = "SELECT COUNT(*) as total FROM offers WHERE user_id = ? AND proposal_id = ?";
        $existing = $this->executeQuerySingle($checkSql, [$user_id, $proposal_id]);

        if ($existing && $existing['total'] > 0) {
            // Offer already exists, return false or custom message
            return [
                'success' => false,
                'message' => 'You have already submitted an offer to this proposal.'
            ];
        }

        // No existing offer, proceed to insert
        $sql = "INSERT INTO offers (user_id, description, proposal_id) VALUES (?, ?, ?)";
        $inserted = $this->executeNonQuery($sql, [$user_id, $description, $proposal_id]);

        return [
            'success' => $inserted > 0,
            'message' => $inserted > 0 
                ? 'Offer submitted successfully.' 
                : 'Failed to submit offer.'
        ];
    }

    public function getOffers($offer_id = null) {
        if ($offer_id) {
            $sql = "SELECT * FROM offers WHERE offer_id = ?";
            return $this->executeQuerySingle($sql, [$offer_id]);
        }
        $sql = "SELECT 
                    offers.*, fiverr_clone_users.*, 
                    offers.date_added AS offer_date_added
                FROM offers JOIN fiverr_clone_users ON 
                offers.user_id = fiverr_clone_users.user_id 
                ORDER BY offers.date_added DESC";
        return $this->executeQuery($sql);
    }

    public function getOffersByProposalID($proposal_id) {
        $sql = "SELECT 
                    offers.*, fiverr_clone_users.*, 
                    offers.date_added AS offer_date_added 
                FROM offers 
                JOIN fiverr_clone_users ON 
                    offers.user_id = fiverr_clone_users.user_id
                WHERE proposal_id = ? 
                ORDER BY offers.date_added DESC";
        return $this->executeQuery($sql, [$proposal_id]);
    }

    public function updateOffer($description, $offer_id) {
        $sql = "UPDATE offers SET description = ? WHERE offer_id = ?";
        return $this->executeNonQuery($sql, [$description, $offer_id]);
    }

    public function deleteOffer($id) {
        $sql = "DELETE FROM offers WHERE offer_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }
}
?>
