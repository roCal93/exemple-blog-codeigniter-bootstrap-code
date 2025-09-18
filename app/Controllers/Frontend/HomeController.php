<?php

namespace App\Controllers\Frontend;

class HomeController extends BaseFrontendController
{
    // Constant for the number of articles to display on the home page
    private const HOME_ARTICLES_LIMIT = 2;

    public function index()
    {
        // Retrieve the latest published articles with the defined limit
        $news = $this->newsModel->news(self::HOME_ARTICLES_LIMIT, true);
        // Add author names to the news articles
        $this->addAuthorsToNews($news);
        
        // Pass data to the home view
        return view('home', [
            'news' => $news,
            'formatter' => $this->formatter,
            'currentUser' => $this->currentUser
        ]);
    }
}
