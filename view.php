<?php 
  session_start();
  require('dbconnect.php');

  if(empty($_REQUEST['id'])) {
    header('Location: index.php');
    exit();
  } 

  $posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ');
  $posts->execute(array(
    $_REQUEST['id']
  ));
?>


<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
    <?php if($post = $posts->fetch()): ?>
      <p>&laquo;<a href="index.php">一覧にもどる</a></p>

        <div class="msg">
        <?php if(strlen($post['picture']) > 14): ?>
    <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>"
     width="200"  alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
    <?php else: ?>
    <img src="member_picture/default.jpg"
     width="200" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
    <?php endif ?>
        <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?> <span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span></p>
        <p class="day"><?php print(htmlspecialchars($post['created_at'], ENT_QUOTES)); ?></p>
        </div>
    <?php else: ?>
    <p>その投稿は削除されたか、URLが間違えています</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
