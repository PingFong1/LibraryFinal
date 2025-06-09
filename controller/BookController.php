<?php
require_once 'Session.php';
require_once '../config/Database.php';

class BookController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createBook($bookData) {
        try {
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
                              VALUES (:title, :accession_number, 'Book', 'available', :cover_image)";
            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":title", $bookData['title']);
            $resourceStmt->bindParam(":accession_number", $bookData['accession_number']);
            $resourceStmt->bindParam(":cover_image", $coverImage);
            $resourceStmt->execute();

            // Get the last inserted resource_id
            $resourceId = $this->conn->lastInsertId();

            // Then, insert into books
            $bookQuery = "INSERT INTO books 
                          (resource_id, author, isbn, publisher, edition, publication_date) 
                          VALUES (:resource_id, :author, :isbn, :publisher, :edition, :publication_date)";
            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(":resource_id", $resourceId);
            $bookStmt->bindParam(":author", $bookData['author']);
            $bookStmt->bindParam(":isbn", $bookData['isbn']);
            $bookStmt->bindParam(":publisher", $bookData['publisher']);
            $bookStmt->bindParam(":edition", $bookData['edition']);
            $bookStmt->bindParam(":publication_date", $bookData['publication_date']);
            $bookStmt->execute();

            // Commit transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Create book error: " . $e->getMessage());
            return false;
        }
    }

    public function getBooks() {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.accession_number, lr.category, lr.status, lr.cover_image,
                             b.author, b.isbn, b.publisher, b.edition, b.publication_date
                      FROM library_resources lr
                      JOIN books b ON lr.resource_id = b.resource_id
                      ORDER BY lr.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get books error: " . $e->getMessage());
            return [];
        }
    }

    public function getBookById($resourceId) {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.accession_number, lr.category, lr.status, lr.cover_image,
                             b.author, b.isbn, b.publisher, b.edition, b.publication_date
                      FROM library_resources lr
                      JOIN books b ON lr.resource_id = b.resource_id
                      WHERE lr.resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_id", $resourceId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get book by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function updateBook($resourceId, $bookData) {
        try {
            $this->conn->beginTransaction();

            // Handle image upload
            $coverImage = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if exists
                $oldImage = $this->getBookById($resourceId)['cover_image'];
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
            $resourceStmt->bindParam(":title", $bookData['title']);
            $resourceStmt->bindParam(":accession_number", $bookData['accession_number']);
            $resourceStmt->bindParam(":category", $bookData['category']);
            $resourceStmt->bindParam(":resource_id", $resourceId);
            if ($coverImage) {
                $resourceStmt->bindParam(":cover_image", $coverImage);
            }
            $resourceStmt->execute();

            // Update books
            $bookQuery = "UPDATE books 
                          SET author = :author, 
                              isbn = :isbn, 
                              publisher = :publisher, 
                              edition = :edition, 
                              publication_date = :publication_date
                          WHERE resource_id = :resource_id";
            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(":author", $bookData['author']);
            $bookStmt->bindParam(":isbn", $bookData['isbn']);
            $bookStmt->bindParam(":publisher", $bookData['publisher']);
            $bookStmt->bindParam(":edition", $bookData['edition']);
            $bookStmt->bindParam(":publication_date", $bookData['publication_date']);
            $bookStmt->bindParam(":resource_id", $resourceId);
            $bookStmt->execute();

            // Commit transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Update book error: " . $e->getMessage());
            return false;
        }
    }

    // public function deleteBook($resourceId) {
    //     try {
    //         // Begin transaction
    //         $this->conn->beginTransaction();

    //         // Delete from books first due to foreign key constraint
    //         $bookQuery = "DELETE FROM books WHERE resource_id = :resource_id";
    //         $bookStmt = $this->conn->prepare($bookQuery);
    //         $bookStmt->bindParam(":resource_id", $resourceId);
    //         $bookStmt->execute();

    //         // Then delete from library_resources
    //         $resourceQuery = "DELETE FROM library_resources WHERE resource_id = :resource_id";
    //         $resourceStmt = $this->conn->prepare($resourceQuery);
    //         $resourceStmt->bindParam(":resource_id", $resourceId);
    //         $resourceStmt->execute();

    //         // Commit transaction
    //         $this->conn->commit();

    //         return true;
    //     } catch (PDOException $e) {
    //         // Rollback transaction
    //         $this->conn->rollBack();
    //         error_log("Delete book error: " . $e->getMessage());
    //         return false;
    //     }
    // }

    public function deleteBook($resourceId) {
        try {
            // Check if book is currently borrowed
            $borrowQuery = "SELECT COUNT(*) as borrow_count 
                           FROM borrowings 
                           WHERE resource_id = :resource_id 
                           AND status = 'active'";
            $borrowStmt = $this->conn->prepare($borrowQuery);
            $borrowStmt->bindParam(":resource_id", $resourceId);
            $borrowStmt->execute();
            $borrowResult = $borrowStmt->fetch(PDO::FETCH_ASSOC);

            if ($borrowResult['borrow_count'] > 0) {
                throw new Exception("Cannot delete: Book is currently borrowed");
            }

            // Begin transaction
            $this->conn->beginTransaction();

            // Delete from books first
            $bookQuery = "DELETE FROM books WHERE resource_id = :resource_id";
            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(":resource_id", $resourceId);
            $bookStmt->execute();

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
            error_log("Delete book error: " . $e->getMessage());
            throw $e;
        }
    }

    // Generate unique Accession Number
    public function generateAccessionNumber($resourceType = 'B') {
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

    // Get total books
    public function getTotalBooks() {
        try {
            $query = "SELECT COUNT(*) as total FROM books";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Get total books error: " . $e->getMessage());
            return 0;
        }
    }
}