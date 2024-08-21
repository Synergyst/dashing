<?php
// Directory path
$dir = '/opt/stream/dash';

// Base URL
$base_url = 'http://watch.exp.lan/dash/';
//$base_url = 'http://yoyo.synergyst.club/dash/';

// Array to store VOD data
$vod_list = array();

// Scan the directory for folders
$folders = scandir($dir);

// Loop through the folders
foreach ($folders as $folder) {
    // Only process folders starting with 'VOD_'
    if (strpos($folder, 'VOD_') === 0 && is_dir($dir . '/' . $folder)) {
        // Check if the watch.mpd file exists in the folder
        if (file_exists($dir . '/' . $folder . '/watch.mpd')) {
            // Extract title by removing 'VOD_'
            $title = substr($folder, 4);

            // Construct the URL
            $url = $base_url . $folder . '/watch.mpd';

            // Add to the VOD list
            $vod_list[] = array('title' => $title, 'url' => $url);
        }
    }
}

// Output the list of VODs (for debugging)
//print_r($vod_list);

// If you want to store these in a SQLite database
$db = new PDO('sqlite:/opt/stream/db/database.db');

// Create table if it doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS vod_urls (
    id INTEGER PRIMARY KEY, 
    title TEXT, 
    url TEXT UNIQUE
)");

// Insert VODs into the database
$stmt = $db->prepare("INSERT OR IGNORE INTO vod_urls (title, url) VALUES (:title, :url)");

foreach ($vod_list as $vod) {
    $stmt->bindParam(':title', $vod['title']);
    $stmt->bindParam(':url', $vod['url']);
    $stmt->execute();
}

//echo "VODs stored in the database.";
?>
