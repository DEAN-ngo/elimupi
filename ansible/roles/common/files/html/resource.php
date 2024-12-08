<?php
set_include_path( get_include_path() . PATH_SEPARATOR . 'php/includes/');

include("resource.inc.php");
include('settings.php');

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $pageTitle ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png" />
	</head>
	<body class="subpage">
		<div id="page-wrapper">

			<!-- Header -->
				<section id="header">
					<div class="container">
						<div class="row">
							<?php include ("navigation.php"); ?>
						</div>
					</div>
				</section>

			<!-- Content -->
				<section id="content">
					<div class="container">
						<div class="row">
							<div class="col-12">

								<!-- Main Content -->
									<section>
                                        <header>
											<h2><?php echo $resourceTitle;?></h2>
										</header>
										<iframe src="<?php 
											echo $resourceURL . "?lang=" . $_GET['lang'] . (isset($_GET['admin'])? '#login' : '') ;?>" 
											title="<?php echo $resourceTitle;?>"></iframe>
									</section>

							</div>
						</div>
					</div>
				</section>

			<?php // include ("footer.php"); ?>
            <?php include ("copyright.php"); ?>
			
		</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>