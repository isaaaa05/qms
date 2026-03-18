<?php
/**
 * Clients Management Library
 * Handles bulk import and client management
 */

class ClientManager {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Import clients from array (used for Excel data)
     * @param array $clients Array of client data
     * @return array Import results
     */
    public function bulkImportClients($clients) {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            $this->db->begin_transaction();

            foreach ($clients as $client) {
                try {
                    if (empty($client['surname']) || strlen(trim($client['surname'])) < 4) {
                        $skipped++;
                        continue;
                    }

                    $client_id = isset($client['client_id']) ? trim($client['client_id']) : null;
                    $surname = trim($client['surname']);
                    $full_name = isset($client['full_name']) ? trim($client['full_name']) : $surname;
                    $phone = isset($client['phone']) ? trim($client['phone']) : null;
                    $email = isset($client['email']) ? trim($client['email']) : null;

                    // Check if client already exists
                    if ($client_id) {
                        $checkQuery = "SELECT id FROM clients WHERE client_id = ?";
                        $stmt = $this->db->prepare($checkQuery);
                        $stmt->bind_param('s', $client_id);
                        $stmt->execute();
                        
                        if ($stmt->get_result()->num_rows > 0) {
                            $skipped++;
                            continue;
                        }
                    }

                    $insertQuery = "INSERT INTO clients (client_id, surname, full_name, phone, email)
                                   VALUES (?, ?, ?, ?, ?)";
                    
                    $stmt = $this->db->prepare($insertQuery);
                    $stmt->bind_param('sssss', $client_id, $surname, $full_name, $phone, $email);
                    
                    if ($stmt->execute()) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                } catch (Exception $e) {
                    $skipped++;
                    $errors[] = 'Error importing ' . ($client['surname'] ?? 'unknown') . ': ' . $e->getMessage();
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'message' => "$imported clients imported, $skipped skipped"
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => 'Bulk import failed: ' . $e->getMessage(),
                'imported' => $imported,
                'skipped' => $skipped
            ];
        }
    }

    /**
     * Get total number of clients
     * @return int Total clients
     */
    public function getTotalClients() {
        $query = "SELECT COUNT(*) as count FROM clients";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Get client by ID
     * @param int $id Client ID
     * @return array Client data or empty array
     */
    public function getClientById($id) {
        $query = "SELECT * FROM clients WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: [];
    }

    /**
     * Update client information
     * @param int $id Client ID
     * @param array $data Update data
     * @return array Success status
     */
    public function updateClient($id, $data) {
        try {
            $allowed_fields = ['surname', 'full_name', 'phone', 'email', 'address', 'notes'];
            $updates = [];
            $params = [];
            $types = '';

            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= 's';
                }
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'error' => 'No valid fields to update'
                ];
            }

            $query = "UPDATE clients SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            
            $params[] = $id;
            $types .= 'i';

            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Client updated'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update client: ' . $e->getMessage()
            ];
        }
    }
}
