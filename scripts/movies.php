<?php
// Connect to the SQLite database
$db = new PDO('sqlite:/opt/stream/db/database.db');
// Initialize search term
$search = '';
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}
// Check if the search form was submitted
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $db->prepare("SELECT * FROM vod_urls WHERE title LIKE :search OR url LIKE :search");
    $stmt->bindValue(':search', '%' . $search . '%');
} else {
    $stmt = $db->prepare("SELECT * FROM vod_urls");
}
// Execute the query
$stmt->execute();
$vod_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOD Library</title>
    <link href="https://unpkg.com/video.js/dist/video-js.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            margin-top: 0px;
            margin-bottom: 20px;
            padding: 20px;
            padding-top: 0px;
            padding-bottom: 20px;
            background-color: #1e1f22;
        }
        h1 {
            text-align: center;
        }
        .site-header-title h1 {
            text-align: center;
            color: #dce3ec;
        }
        .site-header-title h3 {
            text-align: center;
            color: #dce3ec;
        }
        .site-header-title a {
            text-decoration: none;
            color: #7e6edd;
            cursor: pointer;
        }
        .search-container {
            text-align: center;
            //margin-bottom: 20px;
            margin-bottom: 0px;
            padding-bottom: 0px;
        }
        .search-container input[type="text"] {
            padding: 10px;
            width: 300px;
            font-size: 16px;
        }
        .search-container input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #383a40;
            color: #b5bac1;
            border: none;
            cursor: pointer;
        }
        .vod-list {
            max-width: 60%;
            margin: 0 auto;
            padding: 0;
            list-style-type: none;
        }
        .vod-list li {
            background-color: #313338;
            margin-bottom: 10px;
            padding: 20px;
            border: 1px solid #2b2d31;
            border-radius: 5px;
            cursor: pointer; // Add this line to indicate clickability
        }
        .vod-list a {
            text-decoration: none;
            color: #babdc1;
            cursor: pointer;
        }
        .vod-list strong {
            text-decoration: none;
            color: #dce3ec;
            cursor: pointer;
            font-size: 14pt;
        }
        .vod-list div {
            text-decoration: none;
            color: #d0e0e5;
            cursor: pointer;
            font-weight: normal;
        }
        #video-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: black;
            z-index: 1000;
        }
        #video-container video {
            width: 100%;
            height: 100%;
            //object-fit: cover; /* Ensures the video covers the entire area */
        }
        header {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        main {
            padding-top: 0px;
            padding-bottom: 20px;
            padding-left: 20px;
            padding-right: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
            margin-left: 20px;
            margin-right: 20px;
        }
        .tiles-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;

            position: relative;

            padding-left: 0px;
            padding-right: 0px;
            padding-top: 20px;
            padding-bottom: 20px;
            margin-left: 0px;
            margin-right: 0px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .tile {
            max-width: 139px;
            max-height: 209px;
            flex: 1 1 calc(139px - 10px);
            background-color: #313338;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
            //padding-bottom: 85px;
            border-radius: 10px;
            transition: transform 0.66s ease-in-out, width 0.66s ease-in-out; /* Add width transition */
            margin-bottom: 10px;
            border: 1px solid #2b2d31;
            cursor: pointer;
            text-align: center;
            word-wrap: break-word;
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 50%;
            background-position: 0% 100%;
            background-repeat: no-repeat;
            //overflow: hidden; /* Ensure content doesn't overflow the tile */
            //position: relative; /* Make sure the summary stays within the tile */
        }
        .tile:hover {
            transform: scale(1.75);

            max-width: 800px;
            width: 800px;
            padding-right: 400px;
        }
        .vod-summary {
            display: none; /* Hide the summary by default */
            position: absolute;
            top: 10px;
            left: 170px; /* Position it to the right of the image */
            color: #d0e0e5;
            font-size: 12pt;
            //width: 140px;
            //width: 340px;
            width: 440px;
            text-align: left;
        }
        .tile:hover .vod-summary {
            display: block; /* Show the summary on hover */
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
        }
        .vod-list-img {
            background-position: 100% 100%;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            vertical-align: bottom;
            display: flex;
            align-items: flex-end;
            margin-bottom: 10px;
            border-radius: 4px;
            padding-left: 0px;
            padding-right: 0px;
            margin-left: 0px;
            margin-right: 0px;
            height: 239px;
            width: 139px;
        }
        /*.vod-list-img:hover {
            height: 256px;
            width: 256px;
        }*/
    </style>
</head>
<body>

    <div class="site-header-title">
        <h1><a href="/watch">VOD Library</a></h1>
        <h3>(VLC playlist URL: <a id="playlist-link" href="/playlist">/playlist</a>)</h3>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get the current protocol, host, and pathname
            const protocol = window.location.protocol;
            const host = window.location.host;
            // Construct the full URL
            const fullURL = `${protocol}//${host}/playlist`;
            // Set the href attribute and the link text
            const linkElement = document.getElementById("playlist-link");
                linkElement.href = "/playlist";
                linkElement.textContent = fullURL;
            });
        </script>
    </div>

    <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search VODs..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="Search">
        </form>
    </div>


    <div class="vod-list">
        <main>
            <section class="tiles-container">
            <?php if (count($vod_list) > 0): ?>
                <?php foreach ($vod_list as $vod): ?>
                <div class="tile" onclick="playVideo('<?php echo htmlspecialchars($vod['url']); ?>')">
                    <div class="vod-list-img" style="background-image:url('<?php $cover_art_url = htmlspecialchars($vod['cover_art_url']); if ($cover_art_url != 'http://watch.exp.lan/favicon.png') { echo $cover_art_url . '\');height:209px;width:139px;'; } else { echo 'http://watch.exp.lan/favicon.ico' . '\'); height:139px;width:139px;'; } ?>"></div>
                    <div class="vod-summary">
                        <strong><?php echo htmlspecialchars($vod['title']); ?></strong>
                        <div><?php echo htmlspecialchars($vod['summary']); ?></div>
                    </div>
                    <!--<a><?php echo htmlspecialchars($vod['url']); ?></a>-->
                 </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tile">
                    <strong>No VODs found!</strong>
                </div>
            <?php endif; ?>
            </section>
        </main>
    </div>



    <div id="video-container">
        <video id="example-video" class="video-js vjs-default-skin" controls></video>
    </div>
    <script src="https://unpkg.com/video.js/dist/video.js"></script>
    <script src="https://unpkg.com/videojs-contrib-dash/dist/videojs-dash.js"></script>
    <script>
        var player = videojs('example-video');
        function playVideo(url) {
            // Set the video source
            player.src({
                src: url,
                type: 'application/dash+xml'
            });
            // Show the video container
            var videoContainer = document.getElementById('video-container');
            videoContainer.style.display = 'block';
            player.ready(function() {
                player.play();
            });
        }
    </script>

</body>
</html>
