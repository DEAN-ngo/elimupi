<?php
require_once("folders.inc.php");
include('settings.php');

$folder = new Folder();
//print_r($folder);
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
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
							<div class="col-12">

								<!-- Main Content -->
									<section>
                                        <header>
											<h2>Folder management</h2>
										</header>
                                    </section>
                                    <section>
                                        <header>
											<h3>Remove a folder</h3>
										</header>
                                        <p>
                                        <?php if (!array_key_exists('delete_folder', $folder->error_message)) { ?>
                                        <form action="folders.php" method="post">
                                            <?php echo $folder->input_select_delete_folder; ?>
                                            <input type="submit" value="delete" />
                                        </form>
                                        <?php } else { echo '<span id="error">'. $folder->error_message['delete_folder'].'</span>'; } ?>
                                        </p>
                                    </section>
                                    <section>
                                        <header>
											<h3>Add a folder</h3>
										</header>
                                        <p>
                                        <?php 
                                        if (array_key_exists('add_folder', $folder->error_message)) {
                                            echo '<span id="error">'. $folder->error_message['add_folder'].'</span>';
                                        }
                                        ?>
                                        <form action="folders.php" method="post">
                                        Add the following folder: <input type="text" name="folder_to_add" />
                                        <input type="submit" value="add folder" />
                                        </form>
                                        
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