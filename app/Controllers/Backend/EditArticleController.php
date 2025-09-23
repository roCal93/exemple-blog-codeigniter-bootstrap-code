<?php
namespace App\Controllers\Backend;
use App\Models\NewsModel;
use App\Models\UserModel;
use App\Controllers\BaseController;

class EditArticleController extends BaseController
{
    private $NewsModel;
    private $UserModel;

    private function getNewsModel()
    {
        return $this->NewsModel ??= new NewsModel();
    }

    private function getUserModel()
    {
        return $this->UserModel ??= new UserModel();
    }

    // Check if the current user is an admin
    private function isAdmin()
    {
        $userId = session()->get('user_id');
        if (!$userId) return false;
        $userModel = $this->getUserModel();
        $user = $userModel->find($userId);
        return $user && $user['role'] === 'admin';
    }
    
    // Check if the current user can edit the article (admin or owner)
    private function canEditArticle($articleUserId)
    {
        return $this->isAdmin() || session()->get('user_id') == $articleUserId;
    }

    // Check access permissions and article existence
    private function checkAccess($newsId = null)
    {
        if ($newsId) {
            /**
             * ℹ️ A revoir seul me NewsModel doit faire ce travail dans le fichier Model non dans le controller M-V-C First
             */
            $model = $this->getNewsModel();
            $article = $model->find($newsId);
            if (!$article) {
                return redirect()->route('blogIndex')->with('error', 'Article non trouvé.');
            }
            
            if (!$this->canEditArticle($article['user_id'])) {
                return redirect()->route('blogIndex')->with('error', 'Vous n\'êtes pas autorisé à modifier cet article.');
            }
        }
        
        return null; // No redirect, access granted
    }

    // Validate article data (title and content)
    private function validateArticleData()
    {
        $rules = [
            'title' => 'required|max_length[255]|min_length[3]',
            'content' => 'required|min_length[10]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput();
        }
        
        return null;
    }

    // Display the form for creating or editing an article
    public function showForm($id = null)
    {
        $redirect = $this->checkAccess($id);
        if ($redirect) return $redirect;

        $data = [];
        if ($id) {
            $model = $this->getNewsModel();
            $article = $model->find($id);
            $data['article'] = $article;
        }

        $data['error'] = session()->getFlashdata('error');
        $data['validation'] = session()->getFlashdata('validation');
        return twig(false, false, false)->render('createArticle.twig', $data);
    }

    // Create a new article
    public function create()
    {
        $redirect = $this->checkAccess();
        if ($redirect) return $redirect;

        $redirect = $this->validateArticleData();
        if ($redirect) return $redirect;

    $model = $this->getNewsModel();
    $data = esc($this->request->getPost());
    $data['user_id'] = session()->get('user_id');
    $data['state'] = $this->request->getPost('state') ? 1 : 0;
        
        try {
            $model->createNews($data);
            return redirect()->route('blogIndex');
        } catch (\Exception $e) {
            log_message('error', 'Erreur création article: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la création.');
        }
    }

    // Update an existing article
    public function update($newsId)
    {
        $redirect = $this->checkAccess($newsId);
        if ($redirect) return $redirect;

        $redirect = $this->validateArticleData();
        if ($redirect) return $redirect;

    $model = $this->getNewsModel();
    $data = esc($this->request->getPost()); 
    $data['state'] = $this->request->getPost('state') ? 1 : 0;
        
        try {
            if ($model->updateNews($data, $newsId)) {
                return redirect()->route('blogIndex');
            } else {
                log_message('error', 'Échec mise à jour article ID: ' . $newsId);
                return redirect()->back()->with('error', 'Erreur lors de la mise à jour.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur mise à jour article: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour.');
        }
    }

    // Delete an article
    public function delete($newsId)
    {
        $redirect = $this->checkAccess($newsId);
        if ($redirect) return $redirect;
        $model = $this->getNewsModel();
        
        try {
            if ($model->deleteNews($newsId)) {
                return redirect()->route('blogIndex');
            } else {
                return redirect()->back()->with('error', 'Erreur lors de la suppression.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur suppression article: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    // Toggle the state of an article (published/draft)
    public function updateState($newsId)
    {
        $redirect = $this->checkAccess($newsId);
        if ($redirect) return $redirect;
        $model = $this->getNewsModel();
        
        try {
            if ($model->updateState($newsId)) {
                return redirect()->back();
            } else {
                return redirect()->back()->with('error', 'Erreur lors de la mise à jour.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur mise à jour statut: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour.');
        }
    }
}