<?php
set_include_path( get_include_path() . PATH_SEPARATOR . 'php/includes/');

require_once("uploads.inc.php");
include('settings.php');

$upload = new Upload();
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
											<h2>Document upload</h2>
										</header>
                                        <?php if(isset($upload->message)){ echo '<p><span id="error">'. $upload->message . '</span></p>'; } ?>
                                        <?php if ($upload->uploadsuccessful == false) { ?>
										<form action="upload.php" method="post" enctype="multipart/form-data">
                                          <p>Select a document to upload:
                                            <input id="upload" type="file" name="fileToUpload" /> 
                                         </p>
                                          <p>Select a folder to upload to: 
                                          <?php echo  $upload->formselectfolders; ?> </p>
                                          <input id="button-submit" type="submit" value="Upload document" name="submit" />
                                        </form>
                                        <?php } else { ?>
                                            <p><a href="documents.php">View documents</a></p>
                                            <p><a href="upload.php">Upload another document</a></p>
                                        <?php } ?>

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