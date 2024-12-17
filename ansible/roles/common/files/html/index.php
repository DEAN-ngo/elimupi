<?php
set_include_path( get_include_path() . PATH_SEPARATOR . 'php/includes/');

include("index.inc.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $pageTitle ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png" />
        <link rel="manifest" href="/site.webmanifest" />
        <link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#5bbad5" />
        <meta name="msapplication-TileColor" content="#da532c" />
        <meta name="theme-color" content="#ffffff" />
		<link rel="stylesheet" href="assets/css/main.css" />
	</head>
	<body>
		<div id="page-wrapper">

			<!-- Header -->
				<section id="header">
					<div class="container">
						<div class="row">
							<?php include ("navigation.php"); ?>
						</div>
					</div>
					<div id="banner">
						<div class="container">
							<div class="row">
								<div class="col-6 col-12-medium">

									<!-- Banner Copy -->
										<p><?php echo _("Welcome to the ElimuPi; the largest offline resource for education.");?></p>
								</div>
								<div class="col-6 col-12-medium imp-medium">

									<!-- Banner Image -->
										<img src="images/banner.png" alt="" />

								</div>
							</div>
						</div>
					</div>
				</section>

			<!-- Features -->
				<section id="features">
					<div class="container">
						<div class="row">
							<div class="col-3 col-6-medium col-12-small">

									<!-- Feature #1 -->
									<section>
											<a href="http://wiki.elimupi.online" target="_blank" class="bordered-feature-image"><img src="images/wikipedia.svg" alt="" /></a>
											<h2><?php echo ("Wikipedia");?></h2>
											<p>
													<?php echo _("Find information in this vast encyclopedia");?>
											</p>
									</section>

							</div>
							<div class="col-3 col-6-medium col-12-small">

									<!-- Feature #2 -->
									<section>
											<a href="http://kolibri.elimupi.online:8080" target="_blank" class="bordered-feature-image"><img src="images/khan-academy.svg" alt="" /></a>
											<h2><?php echo _("Curriculum content");?></h2>
											<p>
													<?php echo _("Interactive educational resources per curriculum subject");?>
											</p>
									</section>

							</div>

							<div class="col-3 col-6-medium col-12-small">

								<!-- Feature #1 -->
									<section>
										<a href="http://softwares.elimupi.online" target="_blank" class="bordered-feature-image"><img src="images/wikipedia.jpg" alt="" /></a>
										<h2><?php echo ("App repository");?></h2>
										<p>
											<?php echo _("Find the app you want to install");?>
										</p>
									</section>

							</div>
							<div class="col-3 col-6-medium col-12-small">

								<!-- Feature #2 -->
									<section>
										<a href="documents.php?resource=elimuPi&lang=<?php echo $lang?>" class="bordered-feature-image"><img src="images/documents.jpg" alt="" /></a>
										<h2><?php echo _("Educational documents");?></h2>
										<p>
											<?php echo ("Your own collection of educational documents");?>
										</p>
									</section>

							</div>
							<div class="col-3 col-6-medium col-12-small">

								<!-- Feature #3 -->
									<section>
										<a href="#" style="cursor: default;" class="bordered-feature-image"><img src="images/elimuOnline.jpg" alt="" /></a>
										<h2><?php echo _("Elimu.online");?></h2>
										<p>
											<?php echo _("Coming soon: electronic learning environment for teachers");?>
										</p>
									</section>

							</div>
							<div class="col-3 col-6-medium col-12-small">

								<!-- Feature #3 -->
									<section>
										<a href="#" style="cursor: default;" class="bordered-feature-image"><img src="images/elimuOnline.jpg" alt="" /></a>
										<h2><?php echo _("Elimu.online");?></h2>
										<p>
											<?php echo _("Coming soon: electronic learning environment for teachers");?>
										</p>
									</section>

							</div>
							<div class="col-3 col-6-medium col-12-small">

								<!-- Feature #4 -->
									<section>
										<a href="http://registration.elimupi.online" style="cursor: default;" class="bordered-feature-image"><img src="images/check-list.png" alt="" /></a>
										<h2><?php echo _("Register School");?></h2>
										<p>
											<?php echo _("Register schools and update and set dynamic dns for their elimupi connect");?>
										</p>
									</section>

							</div>
						</div>
					</div>
				</section>	
            <?php include ("copyright.php"); ?>

		<!-- Scripts -->

			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
        </div>
	</body>
</html>