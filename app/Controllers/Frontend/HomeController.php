<?php

namespace App\Controllers\Frontend;

class HomeController extends BaseFrontendController
{
    // Constant for the number of articles to display on the home page
    private const HOME_ARTICLES_LIMIT = 2;

    public function index()
    {
        // Retrieve the latest published articles with the defined limit
        $news = $this->getNewsModel()->news(self::HOME_ARTICLES_LIMIT, true);
        // Add author names to the news articles
        $this->addAuthorsToNews($news);

        // Format dates for each article
        foreach ($news as &$new) {
            $new['formattedDate'] = ucfirst($this->formatter->format(strtotime($new['created_at'])));
        }

        //Show Page with Datas using Twig
        $data = [
            'news' => $news,
            'currentUser' => $this->currentUser
        ];

        return twig(true, true, false)->render('home.twig', $data);
    }
}
