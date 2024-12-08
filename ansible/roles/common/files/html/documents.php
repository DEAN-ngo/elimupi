<?php
set_include_path( get_include_path() . PATH_SEPARATOR . 'php/includes/');

require_once ("documents.inc.php");
include('settings.php');

$documents = new Documents();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $pageTitle ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="assets/css/bootstrap.css" />
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

								<!-- Sidebar -->
									<section>
										<?php echo $documents->html_folders; ?>
									</section>
                                    <section>
                                        <header>
                                            <h2>Management</h2>
                                        </header>
                                        <p>
                                            <a href="upload.php">upload a document</a><br />
                                            <a href="folders.php">folder management</a><br />                                        
                                        </p>
                                        
                                    </section>
									<section>
										<header>
											<h2>About this page</h2>
										</header>
										<p>
											This page shows documents and folder that are stored in the /document folder on the ElimuPi. Please note that
                                            only subfolders of the document folder are displayed. No deeper levels.
										</p>
										
									</section>

							</div>
							<div class="col-9 col-12-medium imp-medium">

								<!-- Main Content -->
                                <?php if (isset($_GET["action"])) { ?>
                                    <section>    
                                        <header><h2>Edit document: <?php echo $_GET["file_to_edit"]; ?></h2></header>
                                        <p>What do you want to do with <?php echo $_GET["file_to_edit"]; ?>?</p>
                                        <p> 
                                            <form action="documents.php" method="post">
                                                <input type="hidden" name="file_to_delete" value="<?php echo $_GET["file_to_edit"]; ?>" />
                                                <input type="hidden" name="file_current_dir" value="<?php echo $documents->file_current_directory; ?>" />
                                                Delete the document: <input id="button-delete" type="submit" value="delete" />
                                            </form><br />
                                            <form action="documents.php" method="post">
                                                <input type="hidden" name="file_to_move" value="<?php echo $_GET["file_to_edit"]; ?>" />
                                                <input type="hidden" name="file_current_dir" value="<?php echo $documents->file_current_directory; ?>" />
                                                Move the file to this <?php echo $documents->input_select_folders; ?> folder.
                                                <input id="button-move" type="submit" value="move file" />
                                            </form>
                                        </p>
                                    </section>
                                <?php } ?>
									<section>
										<header>
        									<h2><?php echo $documents->total_files; ?> documents found in /<?php echo $documents->active_dir; ?></h2>
                                        </header> 
                                        <span id="document-list">   
                                            <?php echo $documents->html_files; ?>
    									
                                            <?php if ($documents->total_pages > 1) { ?>

                                		<nav aria-label="Page navigation">
                                		 	<ul class="pagination">
                                
                                		 		<?php if ($documents->current_page > 1 ) { ?>
                                                        <li class="page-item"><a class="page-link" href="<?php echo '?page=1&dir='.$documents->dir ; ?>" >First</a></li>
                                
                                		 		<?php
                                		 			}
                                
                                		 			// Loop through page numbers
                                					for ($documents->page_in_loop = 1; $documents->page_in_loop <= $documents->total_pages; $documents->page_in_loop++) {
                                
                                						// if the total pages is more than 2, we can limit the pagination. We'll also give the current page some classes to disable and style it in css
                                						// if the page in the loop is more between 
                                
                                						if ($documents->total_pages > 3) {
                                							if ( ($documents->page_in_loop >= $documents->current_page - 5 && $documents->page_in_loop <= $documents->current_page )  || ( $documents->page_in_loop <= $documents->current_page + 5 && $documents->page_in_loop >= $documents->current_page) ) {  ?>
                                
                                
                                							 	<li class="page-item <?php echo $documents->page_in_loop == $documents->current_page ? 'active disabled' : '' ; ?>">
                                							 		<a class="page-link" href="<?php echo '?page=' . $documents->page_in_loop. '&dir='. $dir; ?> " ><?php echo $documents->page_in_loop; ?></a>
                                							 	</li>
                                
                                							<?php }
                                						}
                                						// if the total pages doesn't look ugly, we can display all of them
                                						else { ?>
                                
                                
                                						 <li class="page-item <?php echo $documents->page_in_loop == $documents->current_page ? 'active disabled' : '' ; ?>">
                                						 	<a class="page-link" href="<?php echo '?page=' . $documents->page_in_loop. '&dir='. $documents->dir; ?> " ><?php echo $documents->page_in_loop; ?></a>
                                						 </li>
                                
                                						<?php } // End if	?>
                                
                                					<?php } // end for loop
                                
                                					// and the last page
                                					if ($documents->current_page < $documents->total_pages) { ?>
                                
                                        				<li class="page-item"><a class="page-link" href="<?php echo '?page=' . $documents->total_pages. '&dir='. $documents->dir; ?>">Last</a></li>
                                
                                        			<?php } ?>
                                		  	</ul>
                                		</nav>
                                
                                		<?php } // End if total pages more than 1 ?>
                                        &nbsp;</span> 
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
            <script src="assets/js/bootstrap.js"></script>
	</body>
</html>