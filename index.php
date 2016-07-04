<?php
define('APP', 'ots');

require_once('inc/config.inc.php');
require_once('inc/Message.class.php');
require_once('inc/SafePDO.class.php');

if (isset($_GET['show']) && isset($_GET['id']) && isset($_GET['key'])) {
	$msg = new Message(new SafePDO($db_dsn, $db_user, $db_pass));
	$text = $msg->LoadMessage($_GET['id'], $_GET['key']);
	$msg = null;
} else if (isset($_POST['text'])) {
	$msg = new Message(new SafePDO($db_dsn, $db_user, $db_pass));
	list($id, $key) = $msg->CreateMessage($_POST['text']);
	$msg = null;
}
?>

<?php if (isset($_GET['show']) && isset($_GET['key'])) { ?>

	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title"><?php echo (isset($text) ? "Your message" : "Unknown message"); ?></h4>
	</div>
	<div class="modal-body">
		<?php if (isset($text) && $text !== false) { ?>
			<div class="message"><textarea readonly="readonly" class="input-block-level" rows="7"><?php echo $text ?></textarea></div>
		<?php } else { ?>
			<div class="alert">
				This message has never existed or has already been viewed.
			</div>
		<?php } ?>
	</div>
	<div class="modal-footer">
		<a class="btn btn-large btn-block btn-danger" href="/">Dismiss</a>
	</div>

<?php } else { ?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>One-Time Secret</title>

		<!-- Bootstrap -->
		<!-- original: https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css -->
		<link rel="stylesheet" href="/css/bootstrap.min.css">

		<!-- Custom styles for this template -->
		<link rel="stylesheet" href="/css/ots.css">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!-- original: https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js -->
		<!-- original: https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js -->
		<!--[if lt IE 9]>
		<script src="/inc/html5shiv.js"></script>
		<script src="/inc/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

	<div class="container-narrow">
		<div class="header"><h3 class="text-muted">One-Time Secret</h3></div>

		<div class="jumbotron">

			<?php if (isset($_GET['id']) && isset($_GET['key'])) { ?>

				<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">Your message</h4>
							</div>
							<div class="modal-body">
								<div class="message"><textarea readonly="readonly" class="input-block-level" rows="7">loading ...</textarea></div>
							</div>
							<div class="modal-footer">
								<a class="btn btn-large btn-block btn-danger" href="/">Dismiss</a>
							</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->

				<h1>View your message</h1>
				<div class="alert">
					To view your message click the button below.<br />
					Your message will be deleted immediately. Once you leave this page it cannot be viewed again.<br />
					In case no message is displayed, it has either never existed or already been viewed.
				</div>
				<a class="btn btn-large btn-block btn-success" data-toggle="modal" href="<?php echo '/otl/' . $_GET['id'] . '/' . $_GET['key'] ?>" data-target="#myModal">View Message</a>

			<?php } else if (isset($_POST['text'])) { ?>

				<div class="alert">
					Your message has been created. Send the link below to the recipient.<br />
					The message can only be viewed once with this url!<br />
					To verify the content of your message please inspect the textfield below.
				</div>
				<div class="uri"><span class="pretext">Link</span><br /><input id="messageuri" class="selectable" readonly="readonly" value="<?php echo ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/ots/' . $id . '/' . $key; ?>" /></div>
				<div class="message"><span class="pretext">Message</span><br /><textarea class="input-block-level" readonly="readonly"><?php echo $_POST['text']; ?></textarea></div>

			<?php } else { ?>

				<h1>Paste your message below.</h1>
				<form id="createOTS" method="post" autocomplete="off" class="form-horizontal" action="/">
					<textarea rows="7" class="btn-block" placeholder autocomplete="off" name="text"></textarea>
					<button class="btn btn-large btn-block btn-success" type="submit">Create Link</button>
				</form>

			<?php } ?>

		</div>

		<div class="footer">
			<p>&copy; Jesse Schlueter 2015</p>
		</div>
	</div>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<!-- original: https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js -->
	<script src="/inc/jquery.min.js"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<!-- original: https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js -->
	<script src="/inc/bootstrap.min.js"></script>

	</body>
	</html>
<?php } ?>
