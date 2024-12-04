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
							<?php include ("navigation-admin.php"); ?>
						</div>
					</div>
                </section>
                <section id="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-3 col-12-medium">

                            <!-- Sidebar -->
                                <section>
                                    <header>
                                        <h2>ElimuPi packages</h2>
                                    </header>
                                    <p>
                                        <a href="admin/?forceReload5#show-packages" target='content'>Show packages</a><br />
                                        <a href="admin/?forceReload6#download-packages" target='content'>Download from elimu.online</a><br />                                       
                                    </p>
                                    
                                </section>    
                                <section>
                                    <header>
                                        <h2>User menu</h2>
                                    </header>
                                    <p>
                                        <a href="admin/?forceReload1#list-users" target='content'>List Users</a><br />
                                        <a href="admin/?forceReload2#create-account" target='content'>Add User</a><br />
                                        <a href="admin/?forceReload3#create-users" target='content'>Add Users</a><br /> 
                                        <a href="admin/?forceReload4#reset-password" target='content'>Change a password</a><br />
                                    </p>
                                    
                                </section>
                                <section>
                                    <header>
                                        <h2>Info menu</h2>
                                    </header>
                                    <p>
                                        <a href="admin/?forceReload7#disk-usage" target='content'>Disk Usage</a><br />
                                        <a href="#">Version Information</a><br />
                                    </p>
                                    
                                </section>
                                <section>
                                    <header>
                                        <h2>Log files</h2>
                                    </header>
                                    <p>
                                        <a href="#">Generate logfiles</a><br />
                                        <a href="#">Download logfiles</a><br />                                      
                                    </p>
                                    
                                </section>

                                <section>
                                    <header>
                                        <h2>Account information</h2>
                                    </header>
                                    <p>
                                        <a href="admin/?forceReload10#update-password" target='content'>Change password</a><br />
                                        <a href="#">Log off</a><br />
                                    </p>
                                    
                                </section>

                            </div>
                            <div class="col-9 col-12-medium imp-medium">
                            <!-- Main Content -->
                                <iframe src='admin/?lang=<?php echo $lang; ?>' name='content'/>
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