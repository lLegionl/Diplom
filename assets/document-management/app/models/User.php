<?php
class User extends Model {
    protected $table = 'users';
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? LIMIT 1";
        return $this->query($sql, [$username])->fetch(PDO::FETCH_ASSOC);
    }
}