<?php
  session_start();
  require('dbconnect.php');
  if ($_SESSION['id']) {
    $_SESSION['time'] = time();
    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
  } else {
	header('Location: login.php');
      exit();	
  } 

  $page = $_REQUEST['page'];
  if(empty($page)) {
    $page = 1;
  }
  $page = max($page, 1);
  $counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
  $cnt = $counts->fetch();
  $maxPage = ceil($cnt['cnt'] / 5);
  $page = min($page, $maxPage);
  $start = 5 * ($page - 1);
  if(empty($_REQUEST['res'])) {
  	$_REQUEST['res'] = 0;
  }


  if(!empty($_POST)) {
    if(!empty($_POST['message'])){
      $message = $db->prepare('INSERT INTO posts SET message=?, member_id=?, reply_message_id=?, created_at=NOW()');
      $message->execute(array(
        $_POST['message'],
        $member['id'],
        $_POST['reply_post_id']
      ));

      header('Location: index.php');
      exit();
    }
  }


  $posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created_at DESC LIMIT ?, 5');
  $posts->bindParam(1, $start, PDO::PARAM_INT);
  $posts->execute();

  if (isset($_REQUEST['res']) && $_REQUEST['res'] != 0) {
    //返信の処理
    $responce = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ');
    $responce->execute(array(
      $_REQUEST['res']
    ));
    $table = $responce->fetch();
    $message = $table['name'] . 'さんの[' . mb_substr($table['message'], 0, 10, "UTF-8" ) . "]に返信";
  }


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
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php print(htmlspecialchars(($member['name']), ENT_QUOTES)); ?>さんメッセージをどうぞ</dt>
        <?php if(isset($_REQUEST['res']) && $_REQUEST['res'] != 0): ?>
        <p><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></p>
        <?php endif; ?>
        <dd>
          <textarea name="message" cols="50" rows="5"></textarea>
          <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" value="投稿する" />
        </p>
        <?php if(isset($_REQUEST['res']) && $_REQUEST['res'] != 0): ?>
        <p><a href="index.php">返信をやめる</a></p>
        <?php endif ?>
      </div>
    </form>
<?php foreach($posts as $post): ?>
    <div class="msg">
    <?php if(strlen($post['picture']) > 14): ?>
    <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>"
     width="48"  alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
    <?php else: ?>
    <img src="member_picture/default.jpg"
     width="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
    <?php endif ?>
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?>
    <span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span><?php if($_SESSION['id'] != $post['member_id']): ?><a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a><?php endif; ?></p>
    <p class="day">
	<a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($post['created_at'], ENT_QUOTES)); ?></a>
   <?php if($post['reply_message_id'] > 0 ): ?>
    <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">
      返信元のメッセージ</a>
    <?php endif; ?>
    <?php if($_SESSION['id'] == $post['member_id']): ?>
      [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"
      style="color: #F33;">削除</a>]
    <?php endif; ?>
    </p>
    </div>
<?php endforeach; ?>

<ul class="paging">
<?php if($page > 1):?>
<li><a href="index.php?page=<?php print($page-1); ?>">前のページへ</a></li>
<?php else: ?>
<li>前のページへ</li>
<?php endif; 
?>
<?php if($page < $maxPage): ?>
<li><a href="index.php?page=<?php print($page+1); ?>">次のページへ</a></li>
<?php else: ?>
<li>次のページへ</li>
<?php endif; ?>
</ul>
  </div>
</div>
</body>
</html>
