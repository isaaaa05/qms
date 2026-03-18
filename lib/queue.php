<?php
/**
 * Queue Management Library
 * Handles client search, queue assignment, and service tracking
 */

class QueueManager {
    private $db;
    private $min_surname_length = 4;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Search clients by surname (minimum 4 characters)
     * @param string $surname Surname to search for
     * @return array Array of matching clients
     */
    public function searchClients($surname) {
        // Validate minimum length
        if (strlen(trim($surname)) < $this->min_surname_length) {
            return [
                'success' => false,
                'error' => 'Surname must be at least ' . $this->min_surname_length . ' characters long',
                'clients' => []
            ];
        }

        $surname = trim($surname);
        
        try {
            // Use FULLTEXT search for better results
            $query = "SELECT id, client_id, surname, full_name, phone, email 
                     FROM clients 
                     WHERE MATCH(surname) AGAINST(? IN BOOLEAN MODE)
                     OR surname LIKE ?
                     ORDER BY surname ASC
                     LIMIT 50";
            
            $stmt = $this->db->prepare($query);
            $searchTerm = $surname . '*';
            $likeTerm = '%' . $surname . '%';
            
            $stmt->bind_param('ss', $searchTerm, $likeTerm);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $clients = $result->fetch_all(MYSQLI_ASSOC);
            
            return [
                'success' => true,
                'clients' => $clients,
                'count' => count($clients)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'clients' => []
            ];
        }
    }

    /**
     * Assign a client to a counter
     * @param int $client_id Client ID
     * @param int $counter_number Counter number
     * @param string $purpose Purpose of visit
     * @return array Success status and queue entry data
     */
    public function assignClientToCounter($client_id, $counter_number, $purpose = '') {
        try {
            // Verify client exists
            $clientCheck = "SELECT id, surname, full_name FROM clients WHERE id = ?";
            $stmt = $this->db->prepare($clientCheck);
            $stmt->bind_param('i', $client_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'error' => 'Client not found'
                ];
            }
            
            $client = $result->fetch_assoc();

            // Create queue entry
            $insertQuery = "INSERT INTO queue_entries (client_id, counter_number, purpose, status) 
                           VALUES (?, ?, ?, 'waiting')";
            $stmt = $this->db->prepare($insertQuery);
            $stmt->bind_param('iis', $client_id, $counter_number, $purpose);
            $stmt->execute();

            return [
                'success' => true,
                'queue_entry_id' => $this->db->insert_id,
                'client_id' => $client_id,
                'client_name' => $client['surname'],
                'full_name' => $client['full_name'],
                'counter' => $counter_number,
                'status' => 'waiting'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to assign client: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a new client
     * @param array $clientData Client information
     * @return array Success status and client data
     */
    public function createClient($clientData) {
        try {
            // Validate required fields
            if (empty($clientData['surname']) || strlen(trim($clientData['surname'])) < $this->min_surname_length) {
                return [
                    'success' => false,
                    'error' => 'Surname must be at least ' . $this->min_surname_length . ' characters long'
                ];
            }

            // Sanitize inputs
            $client_id = isset($clientData['client_id']) ? trim($clientData['client_id']) : uniqid('CLI_');
            $surname = trim($clientData['surname']);
            $full_name = isset($clientData['full_name']) ? trim($clientData['full_name']) : $surname;
            $phone = isset($clientData['phone']) ? trim($clientData['phone']) : null;
            $email = isset($clientData['email']) ? trim($clientData['email']) : null;
            $address = isset($clientData['address']) ? trim($clientData['address']) : null;
            $notes = isset($clientData['notes']) ? trim($clientData['notes']) : null;

            $insertQuery = "INSERT INTO clients (client_id, surname, full_name, phone, email, address, notes)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($insertQuery);
            $stmt->bind_param('sssssss', $client_id, $surname, $full_name, $phone, $email, $address, $notes);
            $stmt->execute();

            return [
                'success' => true,
                'id' => $this->db->insert_id,
                'client_id' => $client_id,
                'surname' => $surname,
                'full_name' => $full_name,
                'message' => 'Client created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create client: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get current queue for a specific counter
     * @param int $counter_number Counter number
     * @return array Queue entries with client information
     */
    public function getCounterQueue($counter_number) {
        try {
            $query = "SELECT qe.id, c.id as client_id, c.surname, c.full_name, qe.purpose, 
                             qe.status, qe.created_at, qe.called_at, qe.service_started_at
                     FROM queue_entries qe
                     JOIN clients c ON qe.client_id = c.id
                     WHERE qe.counter_number = ? AND qe.status IN ('waiting', 'in_service')
                     ORDER BY qe.created_at ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $counter_number);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Call next client at counter
     * @param int $counter_number Counter number
     * @return array Next client in queue
     */
    public function callNextClient($counter_number) {
        try {
            $query = "SELECT qe.id, c.id as client_id, c.surname, c.full_name, qe.purpose
                     FROM queue_entries qe
                     JOIN clients c ON qe.client_id = c.id
                     WHERE qe.counter_number = ? AND qe.status = 'waiting'
                     ORDER BY qe.created_at ASC
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $counter_number);
            $stmt->execute();
            
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'No clients in queue'
                ];
            }

            $client = $result->fetch_assoc();
            
            // Update status to in_service
            $updateQuery = "UPDATE queue_entries SET status = 'in_service', called_at = NOW() WHERE id = ?";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bind_param('i', $client['id']);
            $updateStmt->execute();

            return [
                'success' => true,
                'entry_id' => $client['id'],
                'client_id' => $client['client_id'],
                'surname' => $client['surname'],
                'full_name' => $client['full_name'],
                'purpose' => $client['purpose']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to call next client: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Complete service for a client
     * @param int $entry_id Queue entry ID
     * @return array Success status
     */
    public function completeService($entry_id) {
        try {
            $query = "UPDATE queue_entries SET status = 'completed', service_started_at = IFNULL(service_started_at, NOW()), completed_at = NOW() WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $entry_id);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Service completed'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to complete service: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove client from queue
     * @param int $entry_id Queue entry ID
     * @return array Success status
     */
    public function removeFromQueue($entry_id) {
        try {
            $query = "UPDATE queue_entries SET status = 'no_show' WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $entry_id);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Client removed from queue'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to remove client: ' . $e->getMessage()
            ];
        }
    }
}
