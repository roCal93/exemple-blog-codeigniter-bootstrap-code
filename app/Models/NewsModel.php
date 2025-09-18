<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'content', 'state', 'user_id', 'slug'];

    /**
     * Retrieve news articles with optional limit and published filter.
     *
     * @param int|null $limit Number of articles to retrieve.
     * @param int|null $published Filter by state (1 for published, 0 for draft).
     * @return array
     */
    public function news($limit = null, $published = null)
    {
        // Start building the query
        $query = $this;
        
        // Apply limit if provided
        if ($limit !== null) {
            $query = $query->limit($limit);
        }
        
        // Apply published filter if provided
        if ($published !== null) {
            $query = $query->where('state', $published);
        }
        
        // Order by creation date descending and fetch all
        return $query->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get list of authors based on user connection status.
     *
     * @param int|null $currentUserId Current user ID or null if not logged in.
     * @return array
     */
    public function getAuthors($currentUserId = null)
    {
        // If user is not logged in, get authors who have articles
        if ($currentUserId === null) {
            return $this->select('news.user_id, users.name')
                        ->join('users', 'users.id = news.user_id')
                        ->distinct()
                        ->findAll();
        } else {
            // If logged in, get all users
            return $this->db->table('users')
                            ->select('id as user_id, name')
                            ->get()
                            ->getResultArray();
        }
    }

    /**
     * Get visible articles based on user permissions.
     *
     * @param int|null $userId Current user ID.
     * @param bool $isAdmin Whether the user is admin.
     * @param string $order Sort order (ASC or DESC).
     * @param int|null $selectedAuthor Filter by author ID.
     * @return array
     */
    public function getVisibleArticles($userId = null, $isAdmin = false, $order = 'DESC', $selectedAuthor = null)
    {
        // Validate order parameter
        $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';
        
        // Start building the query
        $query = $this;

        // Apply visibility filters if not admin
        if (!$isAdmin) {
            if ($userId) {
                // Show published articles or user's own articles
                $query = $query->groupStart()
                               ->where('state', 1)
                               ->orWhere('user_id', $userId)
                               ->groupEnd();
            } else {
                // Show only published articles
                $query = $query->where('state', 1);
            }
        }
        
        // Apply author filter if provided
        if ($selectedAuthor) {
            $query = $query->where('user_id', $selectedAuthor);
        }
        
        // Order by creation date and fetch all
        return $query->orderBy('created_at', $order)->findAll();
    }

    /**
     * Create a new news article.
     *
     * @param array $data Article data.
     * @return bool|int
     */
    public function createNews($data)
    {
        // Generate slug from title if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        return $this->insert($data);
    }

    /**
     * Generate a URL-friendly slug from a string (basic version)
     */
    protected function generateSlug($string)
    {
        // Convert to ASCII, lowercase, replace spaces and remove unwanted chars
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Update an existing news article.
     *
     * @param array $data Article data.
     * @param int $newsId Article ID.
     * @return bool
     */
    public function updateNews($data, $newsId)
    {
        // Filter data to only allowed fields
        $filteredData = array_intersect_key($data, array_flip($this->allowedFields));

        // If title is being updated, regenerate the slug
        if (!empty($filteredData['title'])) {
            $filteredData['slug'] = $this->generateSlug($filteredData['title']);
        }

        // Return false if no valid data
        if (empty($filteredData)) {
            return false;
        }
        // Update the article
        return $this->update($newsId, $filteredData);
    }

    /**
     * Delete a news article.
     *
     * @param int $newsId Article ID.
     * @return bool
     */
    public function deleteNews($newsId)
    {
        // Delete the article
        return $this->delete($newsId);
    }

    /**
     * Toggle the state of a news article.
     *
     * @param int $newsId Article ID.
     * @return bool
     */
    public function updateState($newsId)
    {
        // Find the article
        $article = $this->find($newsId);
        if (!$article) {
            // Log error if article not found
            log_message('error', 'Article not found for state update: ' . $newsId);
            return false;
        }
        
        // Toggle the state
        $newState = !$article['state'];
        // Update the article
        return $this->update($newsId, ['state' => $newState]);
    }
}