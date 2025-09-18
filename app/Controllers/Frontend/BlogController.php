<?php

namespace App\Controllers\Frontend;

class BlogController extends BaseFrontendController
{
    public function index()
    {
        // Get query parameters for order and author filter
        $order = $this->request->getGet('order') ?? 'DESC';
        $selectedAuthor = $this->request->getGet('author') ?? null;
        
        // Validate order parameter to ensure it's ASC or DESC
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        // Retrieve visible articles based on user permissions and filters
        $news = $this->newsModel->getVisibleArticles($this->currentUserId, $this->isAdmin, $order, $selectedAuthor);
        // Add author names to the news articles
        $this->addAuthorsToNews($news);
        
        // Get list of authors based on user login status
        $authors = $this->newsModel->getAuthors($this->currentUserId);
        
        // Determine message if no articles are found
        $noArticlesMessage = $this->getNoArticlesMessage($news, $selectedAuthor, $authors);
        
        // Pass data to the blog view
        return view('blog', [
            'news' => $news,
            'formatter' => $this->formatter,
            'currentOrder' => $order,
            'authors' => $authors,
            'selectedAuthor' => $selectedAuthor,
            'currentUser' => $this->currentUser,
            'noArticlesMessage' => $noArticlesMessage
        ]);
    }

    private function getNoArticlesMessage($news, $selectedAuthor, $authors)
    {
        // Return null if there are articles or no author is selected
        if (!empty($news) || !$selectedAuthor) {
            return null;
        }
        
        // Check if the selected author is the current user
        if ($this->currentUser && $selectedAuthor == $this->currentUser['id']) {
            return "Vous n'avez encore aucun article.";
        }
        
        // Create a map of authors for quick lookup
        $authorsMap = array_column($authors, 'name', 'user_id');
        $authorName = $authorsMap[$selectedAuthor] ?? '';
        // Return message based on whether author exists
        return $authorName ? "Cet utilisateur n'a pas encore d'articles." : "Auteur inconnu.";
    }
}