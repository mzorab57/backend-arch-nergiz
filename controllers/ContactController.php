<?php
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../middleware/protect_admin.php';
require_once __DIR__ . '/../helpers/response.php';

class ContactController {
    private $contact;
    
    public function __construct($pdo) {
        $this->contact = new Contact($pdo);
    }
    
    /**
     * Handle GET requests
     */
    public function handleGet($id = null) {
        // Require admin access for viewing contacts
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            if ($id) {
                $contactData = $this->contact->getById($id);
                if (!$contactData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Contact not found']);
                    return;
                }
                echo json_encode($contactData);
            } else {
                $contacts = $this->contact->all();
                echo json_encode($contacts);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle POST requests
     */
    public function handlePost($data) {
        try {
            $address = isset($data['address']) ? trim($data['address']) : null;
            $email = isset($data['email']) ? trim($data['email']) : null;
            $phone = isset($data['phone']) ? trim($data['phone']) : null;
            
            // Validate at least one field is provided
            if (empty($address) && empty($email) && empty($phone)) {
                http_response_code(400);
                echo json_encode(['error' => 'At least one contact field (address, email, or phone) is required']);
                return;
            }
            
            // Validate email format if provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email format']);
                return;
            }
            
            $result = $this->contact->create($address, $email, $phone);
            
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Contact created successfully',
                    'contact_id' => $this->contact->getLastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create contact']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle PUT requests
     */
    public function handlePut($id, $data) {
        // Require admin access for contact management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if contact exists
            $existingContact = $this->contact->getById($id);
            if (!$existingContact) {
                http_response_code(404);
                echo json_encode(['error' => 'Contact not found']);
                return;
            }
            
            $address = isset($data['address']) ? trim($data['address']) : null;
            $email = isset($data['email']) ? trim($data['email']) : null;
            $phone = isset($data['phone']) ? trim($data['phone']) : null;
            
            // Validate at least one field is provided
            if (empty($address) && empty($email) && empty($phone)) {
                http_response_code(400);
                echo json_encode(['error' => 'At least one contact field (address, email, or phone) is required']);
                return;
            }
            
            // Validate email format if provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email format']);
                return;
            }
            
            $result = $this->contact->update($id, $address, $email, $phone);
            
            if ($result) {
                echo json_encode(['message' => 'Contact updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update contact']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle DELETE requests
     */
    public function handleDelete($id) {
        // Require admin access for contact management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if contact exists
            $existingContact = $this->contact->getById($id);
            if (!$existingContact) {
                http_response_code(404);
                echo json_encode(['error' => 'Contact not found']);
                return;
            }
            
            $result = $this->contact->delete($id);
            
            if ($result) {
                echo json_encode(['message' => 'Contact deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete contact']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}