<?php
// Directory path
$dir = '/opt/stream/dash';

// Base URL
$base_url = 'http://watch.exp.lan/dash/';

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
            $title = str_replace("_", " ", $title);
            $title = strtolower($title);
            $title = ucwords($title);
            $title = htmlspecialchars($title);

            // Construct the URL
            $url = $base_url . $folder . '/watch.mpd';

            $cover_art_url = htmlspecialchars("http://watch.exp.lan/favicon.png");
            //$cover_art_url = "";

            $summary = "";

            $dash_dir = $dir;
            $vod_dir = $dash_dir . "/" . $folder;

            //echo $title . "\n" . $url . "\n" . $cover_art_url . "\n\n";
            //echo $title . "\n" . $url . "\n" . $cover_art_url . "\n" . $dash_dir . "\n\n";

            // Add to the VOD list
            $vod_list[] = array('title' => $title, 'url' => $url, 'cover_art_url' => $cover_art_url, 'summary' => $summary, 'dash_dir' => $dash_dir, 'vod_dir' => $vod_dir);
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
    url TEXT UNIQUE, 
    cover_art_url TEXT, 
    summary TEXT, 
    dash_dir TEXT, 
    vod_dir TEXT
)");

// Insert VODs into the database
$stmt = $db->prepare("INSERT OR IGNORE INTO vod_urls (title, url, cover_art_url, summary, dash_dir, vod_dir) VALUES (:title, :url, :cover_art_url, :summary, :dash_dir, :vod_dir)");

foreach ($vod_list as $vod) {
    $stmt->bindParam(':title', $vod['title']);
    $stmt->bindParam(':url', $vod['url']);
    $stmt->bindParam(':cover_art_url', $vod['cover_art_url']);
    $stmt->bindParam(':summary', $vod['summary']);
    $stmt->bindParam(':dash_dir', $vod['dash_dir']);
    $stmt->bindParam(':vod_dir', $vod['vod_dir']);
    $stmt->execute();
    //echo $vod['title'] . "\n" . $vod['url'] . "\n" . $vod['cover_art_url'] . "\n\n";
}

//print_r($vod_list);
//echo "VODs stored in the database.";

?>
