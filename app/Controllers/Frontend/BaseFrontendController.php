<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\UserModel;
use IntlDateFormatter;

class BaseFrontendController extends BaseController
{
    // Protected properties for shared models and user data
    protected $newsModel;
    protected $userModel;
    protected $formatter;
    protected $currentUser;
    protected $currentUserId;
    protected $isAdmin;

    // Optimized model getters - lazy loading pattern
    protected function getNewsModel()
    {
        return $this->newsModel ??= new NewsModel();
    }

    protected function getUserModel()
    {
        return $this->userModel ??= new UserModel();
    }

    // Constructor to initialize formatter and current user data
    public function __construct()
    {
        $this->formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        
        $this->currentUserId = session('user_id');
        $this->currentUser = $this->currentUserId ? $this->getUserModel()->find($this->currentUserId) : null;
        $this->isAdmin = $this->currentUser && $this->currentUser['role'] === 'admin';
    }

    /**
     * Add author names to an array of news articles.
     *
     * @param array &$news Array of news articles (modified by reference).
     */
    protected function addAuthorsToNews(array &$news): void
    {
        // Loop through each news item to add author name
        foreach ($news as &$new) {
            $userId = $new['user_id'] ?? null;
            
            // Find author and set name with fallback
            $author = $userId ? $this->getUserModel()->find($userId) : null;
            $authorName = $author ? ($author['name'] ?? 'Auteur inconnu') : 'Auteur inconnu';

            // Assign author name back to the news item
            $new['author'] = $authorName;
        }
    }

    /**
     * Check if the current user has permission to view an article.
     *
     * @param mixed $article Article data (array or object).
     * @return bool True if allowed, false otherwise.
     */
    protected function checkArticlePermissions($article): bool
    {
        // Return false if article is invalid
        if (!$article) {
            return false;
        }

        $state = $article['state'] ?? false;
        $userId = $article['user_id'] ?? null;
        
        // Allow if article is published
        if ($state) {
            return true;
        }

        // Deny if user is not logged in
        if (!$this->currentUserId) {
            return false; 
        }

        // Allow if user is admin or owner
        return $this->isAdmin || $this->currentUserId == $userId;
    }

    /**
     * Get a date formatter for French locale.
     *
     * @param int $format IntlDateFormatter format constant.
     * @return IntlDateFormatter
     */
    protected function getFormatterForDate($format = IntlDateFormatter::FULL): IntlDateFormatter
    {
        // Create and return a new date formatter
        return new IntlDateFormatter('fr_FR', $format, IntlDateFormatter::NONE);
    }
}