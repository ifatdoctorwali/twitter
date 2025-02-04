<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
        $stmt->execute([$user_id, $content]);
        header("Location: index.php");
        exit();
    }
}

$tweets = $conn->query("SELECT tweets.content, tweets.created_at, users.username 
                       FROM tweets 
                       JOIN users ON tweets.user_id = users.id 
                       ORDER BY tweets.created_at DESC")->fetchAll();

$current_user = $conn->query("SELECT username FROM users WHERE id = " . $user_id)->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #15202b;
            color: #ffffff;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            position: sticky;
            top: 0;
            background-color: rgba(21, 32, 43, 0.95);
            padding: 10px 0;
            border-bottom: 1px solid #38444d;
            margin-bottom: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #192734;
            border-radius: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #38444d;
            margin-right: 10px;
        }

        .username {
            color: #ffffff;
            font-weight: bold;
        }

        .tweet-form {
            background-color: #192734;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .tweet-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: none;
            border-radius: 10px;
            background-color: #253341;
            color: #ffffff;
            font-size: 16px;
            resize: none;
            margin-bottom: 10px;
        }

        .tweet-form textarea:focus {
            outline: none;
            background-color: #2c3c4c;
        }

        .tweet-form button {
            background-color: #1da1f2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 9999px;
            font-weight: bold;
            cursor: pointer;
            float: right;
        }

        .tweet-form button:hover {
            background-color: #1991db;
        }

        .tweet {
            background-color: #192734;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 15px;
            border: 1px solid #38444d;
        }

        .tweet:hover {
            background-color: #1e2c3a;
        }

        .tweet-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .tweet-username {
            font-weight: bold;
            margin-right: 10px;
        }

        .tweet-time {
            color: #8899a6;
            font-size: 14px;
        }

        .tweet-content {
            color: #ffffff;
            font-size: 15px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }

        .tweet-actions {
            display: flex;
            justify-content: space-around;
            padding-top: 10px;
            border-top: 1px solid #38444d;
        }

        .tweet-action {
            color: #8899a6;
            font-size: 14px;
            cursor: pointer;
        }

        .tweet-action:hover {
            color: #1da1f2;
        }

        .logout-btn {
            background-color: #192734;
            color: #1da1f2;
            border: 1px solid #1da1f2;
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 14px;
            cursor: pointer;
            float: right;
        }

        .logout-btn:hover {
            background-color: rgba(29, 161, 242, 0.1);
        }

        .character-count {
            color: #8899a6;
            font-size: 14px;
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Home</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="user-info">
            <div class="user-avatar"></div>
            <span class="username">@<?php echo htmlspecialchars($current_user['username']); ?></span>
        </div>

        <form method="POST" class="tweet-form">
            <textarea name="content" placeholder="What's happening?" maxlength="160" required 
                      oninput="updateCharCount(this)"></textarea>
            <div class="character-count">160 characters remaining</div>
            <button type="submit">Tweet</button>
        </form>

        <div class="timeline">
            <?php foreach ($tweets as $tweet): ?>
                <div class="tweet">
                    <div class="tweet-header">
                        <span class="tweet-username">@<?php echo htmlspecialchars($tweet['username']); ?></span>
                        <span class="tweet-time">
                            <?php echo date('M j', strtotime($tweet['created_at'])); ?>
                        </span>
                    </div>
                    <div class="tweet-content">
                        <?php echo htmlspecialchars($tweet['content']); ?>
                    </div>
                    <div class="tweet-actions">
                        <span class="tweet-action">💬 Reply</span>
                        <span class="tweet-action">🔄 Retweet</span>
                        <span class="tweet-action">❤️ Like</span>
                        <span class="tweet-action">📤 Share</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function updateCharCount(textarea) {
            const maxLength = 160;
            const remaining = maxLength - textarea.value.length;
            const countDisplay = textarea.parentElement.querySelector('.character-count');
            countDisplay.textContent = `${remaining} characters remaining`;
            
            if (remaining <= 20) {
                countDisplay.style.color = '#f4212e';
            } else {
                countDisplay.style.color = '#8899a6';
            }
        }
    </script>
</body>
</html>
