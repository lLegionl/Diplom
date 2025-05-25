<?php
class Document extends Model {
    protected $table = 'documents';
    
    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY created_at DESC";
        return $this->query($sql, [$category])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createDocument($data) {
        $docNumber = $this->generateDocNumber($data['type']);
        
        $sql = "INSERT INTO {$this->table} 
                (doc_number, title, description, type, category, created_by) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->query($sql, [
            $docNumber,
            $data['title'],
            $data['description'],
            $data['type'],
            $data['category'],
            $_SESSION['user_id']
        ]);
        
        return $docNumber;
    }
    
    private function generateDocNumber($type) {
        $prefixes = [
            'order' => 'ORD',
            'contract' => 'CONT',
            'application' => 'APP'
        ];
        
        return $prefixes[$type] . '-' . date('Y') . '-' . rand(1000, 9999);
    }
}