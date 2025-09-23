<?php

namespace App\Controllers\Backend;

use App\Models\UserModel;
use App\Models\NewsModel;
use App\Controllers\BaseController;

class AuthController extends BaseController
{
    // Security configuration constants
    private const MIN_PASSWORD_LENGTH = 8;

    // Model instances for lazy loading
    private $userModel;
    private $newsModel;

    /**
     * Lazy loading getter for UserModel
     * Uses null coalescing assignment operator to instantiate only when needed
     * 
     * @return UserModel
     */
    private function getUserModel()
    {
        return $this->userModel ??= new UserModel();
    }

    /**
     * Lazy loading getter for NewsModel
     * Uses null coalescing assignment operator to instantiate only when needed
     * 
     * @return NewsModel
     */
    private function getNewsModel()
    {
        return $this->newsModel ??= new NewsModel();
    }

    /**
     * Verify user password against stored hash
     * Centralized password verification to avoid code duplication
     * 
     * @param string $inputPassword The password to verify
     * @param array|null $user The user data containing hashed password
     * @return bool True if password is valid
     */
    private function verifyUserPassword(string $inputPassword, ?array $user): bool
    {
        return $user && password_verify($inputPassword, $user['password']);
    }

    /**
     * Generate a safe cache key for throttling
     * Removes reserved characters that are not allowed in cache keys
     * 
     * @param string $email The email to create cache key for
     * @return string Safe cache key
     */
    private function generateThrottleKey(string $email): string
    {
        // Replace reserved characters {}()/\@: with safe alternatives
        $safeEmail = str_replace(['@', '.', '+', '-'], ['_at_', '_dot_', '_plus_', '_dash_'], $email);
        return 'login_' . $safeEmail;
    }

    /**
     * Handle validation errors by adding them to the validator
     * Centralizes error handling to avoid code duplication
     * 
     * @param array $errors Array of field => error message
     * @return RedirectResponse
     */
    private function handleValidationErrors(array $errors): \CodeIgniter\HTTP\RedirectResponse
    {
        foreach ($errors as $field => $error) {
            $this->validator->setError($field, $error);
        }
        return redirect()->back()->withInput()->with('validation', $this->validator);
    }

    /**
     * Get password validation error message
     * Centralized password requirement message
     * 
     * @return string The password validation error message
     */
    private function getPasswordErrorMessage(): string
    {
        return 'Le mot de passe doit contenir au moins ' . self::MIN_PASSWORD_LENGTH . ' caractères, une majuscule, une minuscule, un chiffre et un caractère spécial';
    }

    // Display the login form
    public function login()
    {
        $data['error'] = session()->getFlashdata('error');
        return twig(false, false, false)->render('login.twig', $data);
    }

    /**
     * Handle user authentication
     * Implements security measures: input validation, rate limiting, and secure password verification
     */
    public function authenticate()
    {
        // Input validation rules
        // The 'is_not_unique[users.email]' rule checks if email exists in database before proceeding
        $rules = [
            'email' => 'required|valid_email|is_not_unique[users.email]',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Données invalides');
        }

        // Rate limiting implementation to prevent brute force attacks
        $throttler = \Config\Services::throttler();
        $email = esc($this->request->getPost('email'));
        $throttleKey = $this->generateThrottleKey($email);

        $DataReceive = esc($this->request->getPost());
        $password = $DataReceive['password'];

        // Database lookup for user credentials
        $user = $this->getUserModel()->where('email', $email)->first();

        // Secure password verification using centralized method
        if (!$this->verifyUserPassword($password, $user)) {
            // Check rate limiting ONLY on failed attempt
            if ($throttler->check($throttleKey, 5, MINUTE)) {
                return redirect()->back()->with('error', 'Trop de tentatives de connexion. Réessayez dans 1 minute.');
            }
            return redirect()->back()->with('error', 'Identifiants invalides');
        }

        // Clear throttler on successful login
        $throttler->remove($throttleKey);

        // Session security: regenerate session ID to prevent session fixation attacks
        session()->regenerate(); 
        session()->set('user_id', $user['id']);
        return redirect()->route('homeIndex');
    }

    // Handle user logout
    public function logout()
    {
        session()->destroy();
        return redirect()->route('homeIndex');
    }

    /**
     * Display the registration form
     * Handles both validation errors and general error messages for consistent UI feedback
     */
    public function register()
    {
        $validation = session()->getFlashdata('validation');
        $errorMessage = session()->getFlashdata('error');
        
        $data = [
            'old_name' => old('name'),
            'old_email' => old('email'),
            'validation' => $validation,
            // Convert validation errors to array format expected by template
            'error' => $validation ? $validation->getErrors() : ($errorMessage ? ['general' => $errorMessage] : null)
        ];
        return twig(false, false, false)->render('register.twig', $data);
    }

    /**
     * Create a new user account
     * Implements comprehensive validation including custom password strength requirements
     */
    public function createUser()
    {
        // Standard CodeIgniter validation rules
        $rules = [
            'name' => 'required|max_length[100]|min_length[2]',
            'email' => 'required|valid_email|is_unique[users.email]|max_length[255]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $DataReceive = esc($this->request->getPost());
        $email = $DataReceive['email'];
        $password = $DataReceive['password'];
        $name = $DataReceive['name'];
        
        // Additional custom password complexity validation
        // Checks for uppercase, lowercase, digit, and special character requirements
        if (!$this->validatePassword($password)) {
            // Add custom validation error to the validator object for consistent error handling
            return $this->handleValidationErrors(['password' => $this->getPasswordErrorMessage()]);
        }

        $data = [
            'email' => $email,
            'password' => $password, // Will be hashed by UserModel
            'name' => $name,
            'role' => 'user' // Default role assignment
        ];
        
        try {
            if ($this->getUserModel()->save($data)) {
                return redirect()->route('loginIndex')->with('success', 'Inscription réussie, vous pouvez vous connecter');
            } else {
                // Handle model validation errors by integrating them into the validator
                return $this->handleValidationErrors($this->getUserModel()->errors());
            }
        } catch (\Exception $e) {
            // Catch any database or system exceptions
            return $this->handleValidationErrors(['general' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Validate password strength using regex pattern
     * Enforces security requirements: length, uppercase, lowercase, digit, special character
     * 
     * @param string $password The password to validate
     * @return bool True if password meets all requirements
     */
    private function validatePassword($password)
    {
        return strlen($password) >= self::MIN_PASSWORD_LENGTH && 
               preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&-_])[A-Za-z\d@$!%*?&\-_]{' . self::MIN_PASSWORD_LENGTH . ',}$/', $password);
    }

    /**
     * Display user profile page with role-based access control
     * Admins can view all users, regular users see only their own profile
     */
    public function userPage()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->route('loginIndex');
        }

        $currentUser = $this->getUserModel()->find($userId);
        
        // Role-based data access: only admins can view all users
        $allUsers = [];
        if ($currentUser && $currentUser['role'] === 'admin') {
            $allUsers = $this->getUserModel()->findAll();
        }
        $data = [
            'currentUser' => $currentUser,
            'allUsers' => $allUsers,
            'errors' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
            'old_email' => old('email'),
            'old_name' => old('name'),
        ];
        return twig(false, false, false)->render('userPage.twig', $data);
    }
    
    /**
     * Update user profile with comprehensive validation and security checks
     * Includes old password verification and uniqueness constraints
     */
    public function updateUser($id)
    {
        $currentUserId = session()->get('user_id');
        
        // Authorization check: users can only update their own profile
        if ($currentUserId != $id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas modifier le profil d\'un autre utilisateur');
        }

        $user = $this->getUserModel()->find($id);
        
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé');
        }
        
        $DataReceive = esc($this->request->getPost());
        $email = $DataReceive['email'];
        $name = $DataReceive['name'];
        $password = $DataReceive['password'];
        $oldPassword = $DataReceive['old_password'];
        
        // Security requirement: verify current password before allowing changes
        if (!$this->getUserModel()->verifyPassword($oldPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect');
        }
        
        // Uniqueness validation: check if email is already taken by another user
        if ($email !== $user['email']) {
            $existingEmail = $this->getUserModel()->where('email', $email)->where('id !=', $id)->first();
            if ($existingEmail) {
                return redirect()->back()->with('error', 'Cette adresse email est déjà utilisée');
            }
        }
        
        // Uniqueness validation: check if name is already taken by another user
        if ($name !== $user['name']) {
            $existingName = $this->getUserModel()->where('name', $name)->where('id !=', $id)->first();
            if ($existingName) {
                return redirect()->back()->with('error', 'Ce nom est déjà utilisé');
            }
        }
        
        $data = [
            'email' => $email,
            'name' => $name
        ];
        
        // Optional password update with complexity validation
        if (!empty($password)) {
            if (!$this->validatePassword($password)) {
                return redirect()->back()->with('error', $this->getPasswordErrorMessage());
            }
            $data['password'] = $password;
        }
        
        // Skip model validation since we're doing custom validation
        $this->getUserModel()->skipValidation(true);
        
        try {
            if ($this->getUserModel()->update($id, $data)) {
                return redirect()->back()->with('success', 'Profil mis à jour avec succès');
            } else {
                return redirect()->back()->with('error', 'Erreur lors de la modification');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a user account with comprehensive security measures
     * Implements role-based permissions, password confirmation, and cascade deletion options
     */
    public function deleteUser($id)
    {
        $currentUserId = session()->get('user_id');
        if (!$currentUserId) {
            return redirect()->route('loginIndex');
        }

        $currentUser = $this->getUserModel()->find($currentUserId);

        // Authorization: only admins can delete other users, users can delete their own account
        if ($currentUser['role'] !== 'admin' && $currentUserId != $id) {
            return redirect()->back()->with('error', 'Accès non autorisé');
        }

        $confirmDelete = esc($this->request->getPost('confirm_delete'));
        $deletePassword = esc($this->request->getPost('delete_password'));
        
        $errors = [];
        
        // Require explicit confirmation to prevent accidental deletions
        if (empty($confirmDelete)) {
            $errors[] = 'Vous devez confirmer la suppression';
        }
        
        // Password verification logic based on user role and target
        if ($currentUserId == $id) {
            // Self-deletion: require user's own password
            if (empty($deletePassword)) {
                $errors[] = 'Le mot de passe est requis pour supprimer votre compte';
            } else {
                $user = $this->getUserModel()->find($id);
                if (!$this->verifyUserPassword($deletePassword, $user)) {
                    $errors[] = 'Mot de passe incorrect';
                }
            }
        } elseif ($currentUser['role'] !== 'admin') {
            // Non-admin trying to delete another user: require admin password
            if (empty($deletePassword)) {
                $errors[] = 'Le mot de passe administrateur est requis';
            } else {
                if (!$this->verifyUserPassword($deletePassword, $currentUser)) {
                    $errors[] = 'Mot de passe administrateur incorrect';
                }
            }
        }
        
        if (!empty($errors)) {
            return redirect()->back()->with('error', implode('<br>', $errors));
        }
        
        $user = $this->getUserModel()->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé');
        }

        // Cascade deletion option: optionally delete user's associated articles
        $deleteArticles = esc($this->request->getPost('delete_articles')) ? true : false;
        
        try {
            // Handle cascade deletion of associated content
            if ($deleteArticles) {
                $this->getNewsModel()->where('user_id', $id)->delete();
            }

            if ($this->getUserModel()->delete($id)) {
                // Special handling for self-deletion: destroy session and redirect to home
                if ($currentUserId == $id) {
                    session()->destroy();
                    return redirect()->route('homeIndex')->with('success', 'Votre compte a été supprimé avec succès');
                } else {
                    // Admin deletion: provide feedback about what was deleted
                    $message = $deleteArticles ? 'Utilisateur et ses articles supprimés avec succès' : 'Utilisateur supprimé avec succès';
                    return redirect()->back()->with('success', $message);
                }
            } else {
                return redirect()->back()->with('error', 'Erreur lors de la suppression');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}