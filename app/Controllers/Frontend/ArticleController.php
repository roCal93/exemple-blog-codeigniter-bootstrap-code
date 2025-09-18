<?php

namespace App\Controllers\Frontend;

class ArticleController extends BaseFrontendController
{
    public function article($slug)
    {
        // Secure the slug
        $slug = esc($slug);
        // Find the article by its slug
        $article = $this->newsModel->where('slug', $slug)->first();
        if (!$article) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        // Check permissions
        if (!$this->checkArticlePermissions($article)) {
            if (!$this->currentUserId) {
                return redirect()->to('/login')->with('error', 'You must be logged in to view this article.');
            }
            return redirect()->route('blogIndex')->with('error', 'This article is not yet published.');
        }
        // Determine if the current user is the owner
        $isOwner = $this->currentUserId == $article['user_id'];
        // Get the author's name
        $author = $this->userModel->find($article['user_id']);
        $authorName = $author ? $author['name'] : 'Unknown author';
        // Prepare data for the view
        $data = [
            'new' => $article,
            'formatter' => $this->getFormatterForDate(\IntlDateFormatter::LONG),
            'author' => $authorName,
            'currentUser' => $this->currentUser,
            'isOwner' => $isOwner,
            'isAdmin' => $this->isAdmin
        ];
        return view('article', $data);
    }
}