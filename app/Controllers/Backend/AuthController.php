<?php

namespace App\Controllers\Backend;

use App\Models\UserModel;
use App\Models\NewsModel;
use App\Controllers\BaseController;

class AuthController extends BaseController
{
    // Constants for login security settings
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 15 * 60; // 15 minutes in seconds
    private const MIN_PASSWORD_LENGTH = 8;
    private const PASSWORD_REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&-_])[A-Za-z\d@$!%*?&\-_]{' . self::MIN_PASSWORD_LENGTH . ',}$/';

    // Display the login form
    public function login()
    {
        $data['error'] = session()->getFlashdata('error');
        return $this->renderTwig('login.twig', $data);
    }

    // Handle user authentication
    public function authenticate()
    {
        // Validate input data
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Données invalides');
        }
        
        $model = new UserModel();
        $DataReceive = esc($this->request->getPost());
        $email = $DataReceive['email'];
        $password = $DataReceive['password'];

        // Find user by email
        $user = $model->where('email', $email)->first();

        // Check for login attempts and lockout
        $attempts = session()->get('login_attempts') ?? 0;
        $lockoutTime = session()->get('login_lockout_time') ?? 0;

        if ($lockoutTime > time()) {
            $remainingTime = ceil(($lockoutTime - time()) / 60);
            return redirect()->back()->with('error', "Compte temporairement bloqué. Réessayez dans {$remainingTime} minute(s).");
        }

        // Verify credentials
        if (!$user || !password_verify($password, $user['password'])) {
            $attempts++;
            session()->set('login_attempts', $attempts);
            
            if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $lockoutTime = time() + self::LOCKOUT_DURATION;
                session()->set('login_lockout_time', $lockoutTime);
                session()->set('login_attempts', 0);
                return redirect()->back()->with('error', 'Trop de tentatives. Compte bloqué pour 15 minutes.');
            }
            
            return redirect()->back()->with('error', 'Identifiants invalides');
        }

        // Clear session data on successful login
        session()->remove('login_attempts');
        session()->remove('login_lockout_time');

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

    // Display the registration form
    public function register()
    {
        $data = [
            'old_name' => old('name'),
            'old_email' => old('email'),
            'validation' => session()->getFlashdata('validation'),
            'error' => session()->getFlashdata('error')
        ];
        return $this->renderTwig('register.twig', $data);
    }

    // Create a new user account
    public function createUser()
    {
        $DataReceive = esc($this->request->getPost());
        $email = $DataReceive['email'];
        $password = $DataReceive['password'];
        $confirm_password = $DataReceive['confirm_password'];
        $name = $DataReceive['name'];
        
        $errors = [];
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide';
        } else {
            $model = new UserModel();
            if ($model->where('email', $email)->first()) {
                $errors['email'] = 'Cet email est déjà utilisé';
            }
        }
        
        // Validate password
        if (!$this->validatePassword($password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins ' . self::MIN_PASSWORD_LENGTH . ' caractères, une majuscule, une minuscule, un chiffre et un caractère spécial';
        }
        
        // Check password confirmation
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
        }
        
        // Validate name length
        if (strlen($name) < 2 || strlen($name) > 50) {
            $errors['name'] = 'Le nom doit avoir entre 2 et 50 caractères';
        }
        
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', $errors);
        }
        
        $model = new UserModel();
        
        $data = [
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'role' => 'user'
        ];
        
        try {
            if ($model->save($data)) {
                return redirect()->route('loginIndex')->with('success', 'Inscription réussie, vous pouvez vous connecter');
            } else {
                return redirect()->back()->withInput()->with('error', $model->errors());
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // Validate password strength
    private function validatePassword($password)
    {
        return strlen($password) >= self::MIN_PASSWORD_LENGTH && preg_match(self::PASSWORD_REGEX, $password);
    }

    // Display user profile page
    public function userPage()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->route('loginIndex');
        }
        
        $model = new UserModel();
        $currentUser = $model->find($userId);
        
        $allUsers = [];
        if ($currentUser && $currentUser['role'] === 'admin') {
            $allUsers = $model->findAll();
        }
        $data = [
            'currentUser' => $currentUser,
            'allUsers' => $allUsers,
            'errors' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
            'old_email' => old('email'),
            'old_name' => old('name'),
        ];
        return $this->renderTwig('userPage.twig', $data);
    }
    
    // Update user profile
    public function updateUser($id)
    {
        $currentUserId = session()->get('user_id');
        if ($currentUserId != $id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas modifier le profil d\'un autre utilisateur');
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé');
        }
        $DataReceive = esc($this->request->getPost());
        $email = $DataReceive['email'];
        $name = $DataReceive['name'];
        $password = $DataReceive['password'];
        $oldPassword = $DataReceive['old_password'];
        
        // Verify old password
        if (!$userModel->verifyPassword($oldPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect');
        }
        
        // Check for unique email
        if ($email !== $user['email']) {
            $existingEmail = $userModel->where('email', $email)->where('id !=', $id)->first();
            if ($existingEmail) {
                return redirect()->back()->with('error', 'Cette adresse email est déjà utilisée');
            }
        }
        
        // Check for unique name
        if ($name !== $user['name']) {
            $existingName = $userModel->where('name', $name)->where('id !=', $id)->first();
            if ($existingName) {
                return redirect()->back()->with('error', 'Ce nom est déjà utilisé');
            }
        }
        
        $data = [
            'email' => $email,
            'name' => $name
        ];
        
        // Update password if provided
        if (!empty($password)) {
            if (!$this->validatePassword($password)) {
                return redirect()->back()->with('error', 'Le mot de passe doit contenir au moins ' . self::MIN_PASSWORD_LENGTH . ' caractères, une majuscule, une minuscule, un chiffre et un caractère spécial');
            }
            $data['password'] = $password;
        }
        
        $userModel->skipValidation(true);
        
        try {
            if ($userModel->update($id, $data)) {
                return redirect()->back()->with('success', 'Profil mis à jour avec succès');
            } else {
                return redirect()->back()->with('error', 'Erreur lors de la modification');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }
    
    // Delete a user account
    public function deleteUser($id)
    {
        $currentUserId = session()->get('user_id');
        if (!$currentUserId) {
            return redirect()->route('loginIndex');
        }
        
        $model = new UserModel();
        $newsModel = new NewsModel();
        $currentUser = $model->find($currentUserId);

        // Check permissions
        if ($currentUser['role'] !== 'admin' && $currentUserId != $id) {
            return redirect()->back()->with('error', 'Accès non autorisé');
        }

        $confirmDelete = $this->request->getPost('confirm_delete');
        $deletePassword = $this->request->getPost('delete_password');
        
        $errors = [];
        
        if (empty($confirmDelete)) {
            $errors[] = 'Vous devez confirmer la suppression';
        }
        
        // Validate password for deletion
        if ($currentUserId == $id) {
            if (empty($deletePassword)) {
                $errors[] = 'Le mot de passe est requis pour supprimer votre compte';
            } else {
                $user = $model->find($id);
                if (!$user || !password_verify($deletePassword, $user['password'])) {
                    $errors[] = 'Mot de passe incorrect';
                }
            }
        } elseif ($currentUser['role'] !== 'admin') {
            if (empty($deletePassword)) {
                $errors[] = 'Le mot de passe administrateur est requis';
            } else {
                if (!password_verify($deletePassword, $currentUser['password'])) {
                    $errors[] = 'Mot de passe administrateur incorrect';
                }
            }
        }
        
        if (!empty($errors)) {
            return redirect()->back()->with('error', implode('<br>', $errors));
        }
        
        $user = $model->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé');
        }

        $deleteArticles = $this->request->getPost('delete_articles') ? true : false;
        
        try {
            // Delete associated articles if requested
            if ($deleteArticles) {
                $newsModel->where('user_id', $id)->delete();
            }

            if ($model->delete($id)) {
                if ($currentUserId == $id) {
                    session()->destroy();
                    return redirect()->route('homeIndex')->with('success', 'Votre compte a été supprimé avec succès');
                } else {
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