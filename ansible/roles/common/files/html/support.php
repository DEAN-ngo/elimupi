<?php
set_include_path( get_include_path() . PATH_SEPARATOR . 'php/includes/');

include("support.inc.php");

include('settings.php');

?>

<!DOCTYPE HTML>

<html>
	<head>
		<title><?php echo $pageTitle ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
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
							<div class="col-3 col-12-medium">

								<!-- Left Sidebar -->
									<section>
										<header>
											<h2>DEAN</h2>
										</header>
										<p>
											<?php echo _('The ElimuPi is developed by Digital Education Africa Network (DEAN).
                                            In case you need support, have wishes for additional functionality,
                                            or want to report a bug. Please feel free to contact us.');?>

										</p>
										<p>
                                            <?php echo _('Email'); ?>: <a href="mailto:info@dean.ngo">info@dean.ngo</a><br />
                                            <?php echo _('Telephone'); ?> NL: <a href="tel:+31207073377">+31 20 7073377</a><br />
                                            <?php echo _('Telephone'); ?> KE: <a href="tel:+254723653227">+254 72 3653227</a><br />
                                            <?php echo _('Website'); ?>: <a href="https://www.dean.ngo" target="_blank">www.dean.ngo</a>
                                        </p>
									</section>
									<section>
										<header>
											<h2><?php echo _('Elimupi Admin');?><h2>
										</header>
										<a href='admin/#turnOff'><img src="assets/svg/turn-off.svg" width="25px" title="<?php echo _('turn off'); ?>" alt="<?php echo _('turn off'); ?>""> </a><?php echo _('turn off ElimuPi'); ?>
									</section>
									<section>
										<header>
											<h2><?php echo _('Good to know'); ?></h2>
										</header>
										<ul class="link-list">
											<li><?php echo _('wifi'); ?>: elimu</li>
											<li><?php echo _('password'); ?>: 1234567890</li>
										</ul>
									</section>

							</div>
							<div class="col-9 col-12-medium imp-medium">

								<!-- Main Content -->
									<section>
										<header>
                                            <img src="images/elimupibydean.png" id="elimupibydean" />
											<h2><?php echo _('The ElimuPi'); ?></h2>
											<h3><?php echo _('An offline educational server solution'); ?></h3>
										</header>
										<p>
											<?php echo _('The ElimuPi containts a wide range of educational resources. These resources are made available
                                            by creating a wireless access point. Since you are reading this, you were able to connect to the
                                            elimu Wi-Fi network.'); ?>
										</p>
                                        <h2><?php echo _('WikiPedia'); ?></h2>
										<p>
                                            <?php echo _('Wikipedia is a free, open content online encyclopedia created through the collaborative effort of a community of users. The version on the ElimuPi is
                                            accessible without the need of having internet access.'); ?>
                                        </p>
                                        <h2><?php echo _('Curriculum content'); ?></h2>
                                        <p>
                                            <?php echo _('The curriculum content on the elimuPi comes from different educational publishers. We have organised this content by the national curriculum subjects of Kenya.
                                            Each subject contains different sources on different levels which complement and enrich the existing teaching methods.'); ?>
                                        </p>
                                        <h2><?php echo _('ElimuOnline'); ?></h2>
                                        <p>
                                            <?php echo _('ElimuOnline is a Learn Management System (LMS) where we share educational content for the Kenyan national curriculum.'); ?>
                                        </p>
									</section>

                                    <section id="Android">
                                        <header>
											<h2><?php echo _('Android Apps'); ?></h2>
											<h3><?php echo _('How to distribute Android apps?');?></h3>
										</header>
                                        <p>
                                            <?php echo _('The ElimuPi contains a range of educational Android apps. These apps can be installed on Android devices (tablets/phones) without the need of an
                                                internet connection.'); ?>
                                            <ol>
                                                <li><a href="assets/software/F-Droid.apk"><?php echo _('Download fdroid.apk to your device.'); ?></a></li>
                                                <li><?php echo _('Install fdroid.apk from your download folder by clicking it in a file manager on your device.
                                                    (opening it direct from the download folder results in a "cannot open file". Use a file manager instead.)'); ?></li>
                                                <li><?php echo _('In case you get a warning about "unknown sources", allow the installation from unknow sources.'); ?></li>
                                                <li><?php echo _('Start Fdroid, an empty screen will appear'); ?></li>
                                                <li><?php echo _('Go to <em>settings</em>'); ?></li>
                                                <li><?php echo _('Go to <em>Repositories</em>'); ?></li>
                                                <li><?php echo _('Click <em>+ new repository</em>'); ?></li>
                                                <li><?php echo _('Enter <strong>http://fdroid.local</strong> and <i>add</i>'); ?></li>
                                                <li><?php echo _('Go Back and click on <em>categories</em> to view the available apps'); ?></li>
                                            </ol>
                                        </p>
                                    </section>
									
									<section>
										<header>
											<h2><?php echo _("Create and delete pupils with a text file");?></h2>
											<h3><?php echo _('What are the requirements of the file?');?></h3>
										</header>
										<p>
											<?php echo _("In the Admin interface a text file can be selected to synchronize the accounts of all pupils. The text file should contain a username on each line and can be a CSV file 
											in which the lines end with one or more commas. All spaces will be removed. If the username doesn't exist yet it is created. If the username doesn't exist in the file and if the account 
											has been created before it is deleted. The synchronize functionality doesn't start when 25% or more of all accounts would be deleted by the action.");?>
										</p>
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
