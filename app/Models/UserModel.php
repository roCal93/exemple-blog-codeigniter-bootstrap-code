<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'name', 'email', 'password', 'role'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    
    protected function hashPassword(array $data)
    {
        // Check if password is present in the data
        if (!isset($data['data']['password'])) {
            return $data;
        }
        
        // Hash the password with default algorithm and cost 10
        $data['data']['password'] = password_hash(
            $data['data']['password'], 
            PASSWORD_DEFAULT,
            ['cost' => 10]
        );
        
        return $data;
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        // Verify the password against the hash
        return password_verify($password, $hash);
    }
    
    public function findUserByEmail(string $email)
    {
        // Find user by email address
        return $this->where('email', $email)->first();
    }
}