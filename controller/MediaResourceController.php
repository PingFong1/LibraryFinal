<?php
require_once 'Session.php';
require_once '../config/Database.php';

class MediaResourceController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createMediaResource($mediaData) {
        try {
            // Begin transaction
            $this->conn->beginTransaction();

            // Handle image upload
            $coverImage = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/covers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('cover_') . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
                    $coverImage = 'uploads/covers/' . $fileName;
                }
            }

            // First, insert into library_resources
            $resourceQuery = "INSERT INTO library_resources 
                            (title, accession_number, category, status, cover_image) 
                            VALUES (:title, :accession_number, 'Media', 'available', :cover_image)";
            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":title", $mediaData['title']);
            $resourceStmt->bindParam(":accession_number", $mediaData['accession_number']);
            $resourceStmt->bindParam(":cover_image", $coverImage);
            $resourceStmt->execute();

            // Get the last inserted resource_id
            $resourceId = $this->conn->lastInsertId();

            // Then, insert into media_resources
            $mediaQuery = "INSERT INTO media_resources 
                          (resource_id, format, runtime, media_type) 
                          VALUES (:resource_id, :format, :runtime, :media_type)";
            $mediaStmt = $this->conn->prepare($mediaQuery);
            $mediaStmt->bindParam(":resource_id", $resourceId);
            $mediaStmt->bindParam(":format", $mediaData['format']);
            $mediaStmt->bindParam(":runtime", $mediaData['runtime']);
            $mediaStmt->bindParam(":media_type", $mediaData['media_type']);
            $mediaStmt->execute();

            // Commit transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Create media resource error: " . $e->getMessage());
            return false;
        }
    }

    public function getMediaResources() {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.accession_number, lr.category, lr.status, lr.cover_image,
                             mr.format, mr.runtime, mr.media_type
                      FROM library_resources lr
                      JOIN media_resources mr ON lr.resource_id = mr.resource_id
                      ORDER BY lr.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get media resources error: " . $e->getMessage());
            return [];
        }
    }

    public function updateMediaResource($resourceId, $mediaData) {
        try {
            // Begin transaction
            $this->conn->beginTransaction();

            // Handle image upload
            $coverImage = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if exists
                $query = "SELECT cover_image FROM library_resources WHERE resource_id = :resource_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":resource_id", $resourceId);
                $stmt->execute();
                $oldImage = $stmt->fetch(PDO::FETCH_ASSOC)['cover_image'];
                
                if ($oldImage && file_exists('../' . $oldImage)) {
                    unlink('../' . $oldImage);
                }

                $uploadDir = '../uploads/covers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('cover_') . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
                    $coverImage = 'uploads/covers/' . $fileName;
                }
            }

            // Update library_resources
            $resourceQuery = "UPDATE library_resources 
                            SET title = :title, 
                                accession_number = :accession_number, 
                                category = :category" .
                                ($coverImage ? ", cover_image = :cover_image" : "") . 
                            " WHERE resource_id = :resource_id";
            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":title", $mediaData['title']);
            $resourceStmt->bindParam(":accession_number", $mediaData['accession_number']);
            $resourceStmt->bindParam(":category", $mediaData['category']);
            $resourceStmt->bindParam(":cover_image", $coverImage);
            $resourceStmt->bindParam(":resource_id", $resourceId);
            $resourceStmt->execute();

            // Update media_resources
            $mediaQuery = "UPDATE media_resources 
                          SET format = :format,
                              runtime = :runtime,
                              media_type = :media_type
                          WHERE resource_id = :resource_id";
            $mediaStmt = $this->conn->prepare($mediaQuery);
            $mediaStmt->bindParam(":format", $mediaData['format']);
            $mediaStmt->bindParam(":runtime", $mediaData['runtime']);
            $mediaStmt->bindParam(":media_type", $mediaData['media_type']);
            $mediaStmt->bindParam(":resource_id", $resourceId);
            $mediaStmt->execute();

            // Commit transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Update media resource error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMediaResource($resourceId) {
        try {
            // Check if media resource is currently borrowed
            $borrowQuery = "SELECT COUNT(*) as borrow_count 
                           FROM borrowings 
                           WHERE resource_id = :resource_id 
                           AND status = 'active'";
            $borrowStmt = $this->conn->prepare($borrowQuery);
            $borrowStmt->bindParam(":resource_id", $resourceId);
            $borrowStmt->execute();
            $borrowResult = $borrowStmt->fetch(PDO::FETCH_ASSOC);

            if ($borrowResult['borrow_count'] > 0) {
                throw new Exception("Cannot delete: Media resource is currently borrowed");
            }

            // Begin transaction
            $this->conn->beginTransaction();

            // Delete from media_resources first
            $mediaQuery = "DELETE FROM media_resources WHERE resource_id = :resource_id";
            $mediaStmt = $this->conn->prepare($mediaQuery);
            $mediaStmt->bindParam(":resource_id", $resourceId);
            $mediaStmt->execute();

            // Update library_resources status to 'deleted' instead of deleting
            $resourceQuery = "UPDATE library_resources 
                             SET status = 'deleted' 
                             WHERE resource_id = :resource_id";
            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":resource_id", $resourceId);
            $resourceStmt->execute();

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Rollback transaction if started
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Delete media resource error: " . $e->getMessage());
            throw $e;
        }
    }

    // Generate unique Accession Number
    public function generateAccessionNumber($resourceType = 'R') {
        try {
            $currentYear = date('Y');
            $prefix = $resourceType . '-' . $currentYear . '-';
            
            $query = "SELECT MAX(accession_number) as last_number 
                      FROM library_resources 
                      WHERE accession_number LIKE :prefix";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":prefix", $prefix . '%');
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['last_number']) {
                // Extract the last sequential number and increment
                $lastNumber = intval(substr($result['last_number'], -3));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }
            
            return $prefix . $newNumber;
        } catch (PDOException $e) {
            error_log("Generate Accession Number error: " . $e->getMessage());
            return null;
        }
    }

    // Get total media resources
    public function getTotalMediaResources() {
        try {
            $query = "SELECT COUNT(*) as total FROM media_resources";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Get total media resources error: " . $e->getMessage());
            return 0;
        }
    }
}