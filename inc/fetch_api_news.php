<?php
// Ensure the main autoloader and configuration are loaded
require_once __DIR__ . '/requires.php';

function get_api_news() {
    $cache_file = __DIR__ . '/../cache/api_news.json';
    $cache_life = 3600; // Cache for 1 hour (3600 seconds)
    $api_news = [];

    // Create cache directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../cache')) {
        mkdir(__DIR__ . '/../cache', 0777, true);
    }

    // Check if cache file exists and is recent
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_life) {
        // Load from cache
        $api_news = json_decode(file_get_contents($cache_file), true);
    } else {
        // --- API FETCHING LOGIC ---
        
        $api_key = $_ENV['API_NEWS'] ?? null;

        if (!$api_key) {
            // Silently fail if no API key is present
            return [];
        }
        
        $api_url = "https://newsapi.org/v2/everything?q=bollywood&language=en&sortBy=publishedAt&apiKey=" . $api_key;

        // --- Use cURL for a more robust API request ---
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyFirstMovie Website');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error || $response === false) {
            // If cURL fails, return empty array
            return [];
        }

        $data = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['status']) && $data['status'] === 'ok' && isset($data['articles'])) {
            foreach ($data['articles'] as $article) {
                $api_news[] = [
                    'headline' => $article['title'],
                    'content' => $article['description'],
                    'image' => $article['urlToImage'],
                    'url' => $article['url']
                ];
            }
        }

        // Save the formatted news to the cache file
        file_put_contents($cache_file, json_encode($api_news));
    }

    return $api_news;
}
?>