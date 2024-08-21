<?php
// Path to save the playlist file
$playlist_file = '/opt/stream/dash/playlist.xspf';
// Connect to the SQLite database
$db = new PDO('sqlite:/opt/stream/db/database.db');
// Query to retrieve VOD entries from the database
$stmt = $db->query("SELECT title, url FROM vod_urls");
$vod_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Start the VLC playlist content
$playlist_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$playlist_content .= '<playlist version="1" xmlns="http://xspf.org/ns/0/">' . "\n";
$playlist_content .= '  <title>VOD Playlist</title>' . "\n";
$playlist_content .= '  <trackList>' . "\n";
// Loop through each VOD and add it to the playlist
foreach ($vod_list as $vod) {
    $playlist_content .= '    <track>' . "\n";
    $playlist_content .= '      <title>' . htmlspecialchars($vod['title']) . '</title>' . "\n";
    $playlist_content .= '      <location>' . htmlspecialchars($vod['url']) . '</location>' . "\n";
    $playlist_content .= '    </track>' . "\n";
}
$playlist_content .= '  </trackList>' . "\n";
$playlist_content .= '</playlist>' . "\n";
// Write the playlist content to the file
file_put_contents($playlist_file, $playlist_content);
?>
