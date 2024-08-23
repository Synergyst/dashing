<?php
// Connect to the SQLite database
$db = new PDO('sqlite:/opt/stream/db/database.db');

// Initialize search term
$search = '';
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

function successfulEditDB() {
  echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            background-color: #f4f4f4;
        }
        .message {
            font-size: 24px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="message">
        Success!<br>
        You will be redirected to the homepage in 3 seconds...
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get the current protocol and host (base URL)
            const protocol = window.location.protocol;
            const host = window.location.host;

            // Construct the base URL
            const baseURL = `${protocol}//${host}/`;

            // Redirect after 3 seconds
            setTimeout(function() {
                window.location.href = baseURL;
            }, 3000);
        });
    </script>
</body>
</html>
';
}

// Function to check and add missing columns
function addMissingColumn($db, $column_name, $column_type) {
    $result = $db->query("PRAGMA table_info(vod_urls)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array($column_name, $columns)) {
        $db->exec("ALTER TABLE vod_urls ADD COLUMN $column_name $column_type");
    }
}

// Get existing columns
$result = $db->query("PRAGMA table_info(vod_urls)");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

// Handle adding a new column
if (isset($_GET['action']) && $_GET['action'] == 'add_column') {
    if (!empty($_GET['new_column_name']) && !empty($_GET['new_column_type'])) {
        addMissingColumn($db, $_GET['new_column_name'], $_GET['new_column_type']);
        //header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); // Refresh page to update the form
        successfulEditDB();
        exit();
    }
}

// Handle adding or editing entries
if (isset($_GET['action']) && $_GET['action'] == 'save') {
    $query = "";
    $params = [];

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        // Update existing entry
        $query = "UPDATE vod_urls SET ";
        foreach ($columns as $column) {
            if ($column['name'] != 'id') {
                $query .= $column['name'] . " = :{$column['name']}, ";
                $params[":{$column['name']}"] = $_GET[$column['name']];
            }
        }
        $query = rtrim($query, ', ') . " WHERE id = :id";
        $params[':id'] = $_GET['id'];
    } else {
        // Insert new entry
        $query = "INSERT INTO vod_urls (";
        foreach ($columns as $column) {
            if ($column['name'] != 'id') {
                $query .= $column['name'] . ", ";
            }
        }
        $query = rtrim($query, ', ') . ") VALUES (";
        foreach ($columns as $column) {
            if ($column['name'] != 'id') {
                $query .= ":{$column['name']}, ";
                $params[":{$column['name']}"] = $_GET[$column['name']];
            }
        }
        $query = rtrim($query, ', ') . ")";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
}

// Handle deletion of an entry
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $db->prepare("DELETE FROM vod_urls WHERE id = :id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
}

// Blacklisted columns that cannot be deleted
$blacklisted_columns = ['id', 'title', 'url', 'cover_art_url', 'summary'];

// Handle deletion of a column
if (isset($_GET['action']) && $_GET['action'] == 'delete_column') {
    $column_to_delete = $_GET['column_name'];
    if (!in_array($column_to_delete, $blacklisted_columns)) {
        $db->exec("ALTER TABLE vod_urls DROP COLUMN $column_to_delete");
        successfulEditDB();
        exit();
        //echo 'Column deleted successfully';
        // Redirect or refresh the page if necessary
    } /*else {
        echo 'This column cannot be deleted';
    }*/
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

    <?php foreach ($columns as $column): ?>
        <?php if ($column['name'] != 'id'): ?>
            <label><?php echo htmlspecialchars($column['name']); ?>: 
                <?php if ($column['type'] == 'TEXT'): ?>
                    <input type="text" name="<?php echo htmlspecialchars($column['name']); ?>" value="<?php echo isset($_GET[$column['name']]) ? htmlspecialchars($_GET[$column['name']]) : ''; ?>">
                <?php elseif ($column['type'] == 'INTEGER'): ?>
                    <input type="number" name="<?php echo htmlspecialchars($column['name']); ?>" value="<?php echo isset($_GET[$column['name']]) ? htmlspecialchars($_GET[$column['name']]) : ''; ?>">
                <?php endif; ?>
            </label><br><br>
        <?php endif; ?>
    <?php endforeach; ?>

    <input type="submit" value="Save">
</form>

<label>
    <input type="checkbox" id="expert_mode_toggle" checked="checked" />
    Expert mode
</label>

<div id="expert_mode_section">
<h2>Add New Column</h2>
<form method="get" action="">
    <input type="hidden" name="action" value="add_column">
    <label>Column Name: <input type="text" name="new_column_name" required></label><br><br>
    <label>Column Type: 
        <select name="new_column_type" required>
            <option value="TEXT">TEXT</option>
            <option value="INTEGER">INTEGER</option>
            <option value="REAL">REAL</option>
            <option value="BLOB">BLOB</option>
        </select>
    </label><br><br>
    <input type="submit" value="Add Column">
</form>

<h2>Delete a Column</h2>
<form method="get" action="">
    <input type="hidden" name="action" value="delete_column">
    <label>Select Column to Delete:
        <select name="column_name" required>
            <?php foreach ($columns as $column): ?>
                <?php if (!in_array($column['name'], $blacklisted_columns)): ?>
                    <option value="<?php echo htmlspecialchars($column['name']); ?>"><?php echo htmlspecialchars($column['name']); ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <input type="submit" value="Delete Column">
</form>
</div>

<script>
    var elem = document.getElementById('expert_mode_section'),
    checkBox = document.getElementById('expert_mode_toggle');
    checkBox.checked = false;
    checkBox.onchange = function doruc() {
        elem.style.display = this.checked ? 'block' : 'none';
    };
    checkBox.onchange();
</script>

<h2>Current VOD Entries</h2>
<table>
    <thead>
        <tr>
            <?php foreach ($columns as $column): ?>
                <th><?php echo htmlspecialchars($column['name']); ?></th>
            <?php endforeach; ?>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($vod_list as $index => $vod): ?>
            <tr style="background-color: <?php echo $index % 2 == 0 ? '#d9d9d9' : '#ffffff'; ?>;">
                <?php foreach ($columns as $column): ?>
                    <td><?php echo htmlspecialchars($vod[$column['name']]); ?></td>
                <?php endforeach; ?>
                <td>
                    <a href="?action=edit&id=<?php echo $vod['id']; ?><?php foreach ($columns as $column) { if ($column['name'] != 'id') { echo '&' . urlencode($column['name']) . '=' . urlencode($vod[$column['name']]); } } ?>">Edit</a> |
                    <a href="?action=delete&id=<?php echo $vod['id']; ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
