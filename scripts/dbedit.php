<?php
// Connect to the SQLite database
$db = new PDO('sqlite:/opt/stream/db/database.db');

// Initialize search term
$search = '';
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

// Function to check and add missing columns
function addMissingColumn($db, $column_name, $column_type) {
    $result = $db->query("PRAGMA table_info(vod_urls)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array($column_name, $columns)) {
        $db->exec("ALTER TABLE vod_urls ADD COLUMN $column_name $column_type");
    }
}

// Check and add missing columns
addMissingColumn($db, 'cover_art', 'TEXT');
addMissingColumn($db, 'summary', 'TEXT');

// Handle adding or editing entries
if (isset($_GET['action']) && $_GET['action'] == 'save') {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        // Update existing entry
        $stmt = $db->prepare("UPDATE vod_urls SET title = :title, url = :url, cover_art = :cover_art, summary = :summary WHERE id = :id");
        $stmt->bindParam(':id', $_GET['id']);
    } else {
        // Insert new entry
        $stmt = $db->prepare("INSERT INTO vod_urls (title, url, cover_art, summary) VALUES (:title, :url, :cover_art, :summary)");
    }
    
    $stmt->bindParam(':title', $_GET['title']);
    $stmt->bindParam(':url', $_GET['url']);
    $stmt->bindParam(':cover_art', $_GET['cover_art']);
    $stmt->bindParam(':summary', $_GET['summary']);
    $stmt->execute();
}

// Handle deletion of an entry
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $db->prepare("DELETE FROM vod_urls WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
}

// Fetch all entries
$stmt = $db->query("SELECT * FROM vod_urls");
$vod_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit VOD Database</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>

<h1><a href="/">Edit VOD Database</a></h1>

<h2>Add/Edit VOD Entry</h2>
<form method="get" action="">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
    <label>Title: <input type="text" name="title" required value="<?php echo isset($_GET['title']) ? htmlspecialchars($_GET['title']) : ''; ?>"></label><br><br>
    <label>URL: <input type="text" name="url" required value="<?php echo isset($_GET['url']) ? htmlspecialchars($_GET['url']) : ''; ?>"></label><br><br>
    <label>Cover Art URL: <input type="text" name="cover_art" value="<?php echo isset($_GET['cover_art']) ? htmlspecialchars($_GET['cover_art']) : ''; ?>"></label><br><br>
    <label>Summary: <textarea name="summary"><?php echo isset($_GET['summary']) ? htmlspecialchars($_GET['summary']) : ''; ?></textarea></label><br><br>
    <input type="submit" value="Save">
</form>

<h2>Current VOD Entries</h2>
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>URL</th>
            <th>Cover Art URL</th>
            <th>Summary</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vod_list as $vod): ?>
            <tr>
                <td><?php echo htmlspecialchars($vod['title']); ?></td>
                <td><?php echo htmlspecialchars($vod['url']); ?></td>
                <td><?php echo htmlspecialchars($vod['cover_art']); ?></td>
                <td><?php echo htmlspecialchars($vod['summary']); ?></td>
                <td>
                    <a href="?action=edit&id=<?php echo $vod['id']; ?>&title=<?php echo urlencode($vod['title']); ?>&url=<?php echo urlencode($vod['url']); ?>&cover_art=<?php echo urlencode($vod['cover_art']); ?>&summary=<?php echo urlencode($vod['summary']); ?>">Edit</a> |
                    <a href="?action=delete&id=<?php echo $vod['id']; ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
