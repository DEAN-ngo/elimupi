<?php
$pageTitle = "DEAN - ElimuPi - Documents";

require_once ("settings.inc.php");
require_once ("folders.inc.php");

class Documents extends Folder {
    

    public $total_files;
    public $current_page;
    public $total_pages;
    public $first_file_displayed;
    public $last_file_displayed;
    public $range;
    public $offset;
    public $active_dir;
    public $folders = array();
    public $html_files;
    public $html_folders = array();    
    public $documents = array();
    public $dir_to_scan;
    public $file_to_delete;
    public $file_to_move;
    public $file_new_location;
    public $file;
    public $documentList;
    public $subFolders;
    public $base_dir;
    
    function __construct(){
        $settings = new Settings;
        $this->getActiveDir();
        $this->setFolders($settings->base_dir, $this->active_dir);
        
               
        if (isset($_GET["action"])=="edit"){
            if (!empty($_GET["file_current_directory"])){
                $current_directory = $_GET["file_current_directory"];    
            } else {
                $current_directory = '/';
            }

            $this->inputSelectFolders($this->folders, 'folder_to_move_to', 'Select a new folder location', $current_directory ,'/','main folder');
            $this->file_current_directory = $_GET["file_current_directory"];
        }
        
        if (isset($_POST["file_to_delete"]) && !empty($_POST["file_to_delete"])){
            $this->file_to_delete = $this->setDocumentPath($settings->base_dir,$_POST["file_current_dir"],$_POST["file_to_delete"]);
            $this->deleteDocument($this->file_to_delete);
        }
        
        if (isset($_POST["file_to_move"]) && !empty($_POST["file_to_move"])){
            $this->file_to_move = $this->setDocumentPath($settings->base_dir,$_POST["file_current_dir"],$_POST["file_to_move"]);
            $this->file_new_location = $this->setDocumentPath($settings->base_dir,$_POST["folder_to_move_to"],$_POST["file_to_move"]); 
            $this->moveDocument($this->file_to_move,$this->file_new_location);
        }
        
        
        $this->getAllDocuments($settings->base_dir);
        
                
        // Declare a variable for our current page. If no page is set, the default is page 1
        $this->current_page = isset($_GET['page']) ? $_GET['page'] : 1;
       
       // Declare an offset based on our current page (if we're not on page 1).
        if ( !empty($this->current_page) && $this->current_page > 1 ) {
        	$this->offset = ($this->current_page * $settings->doc_list_limit) - $settings->doc_list_limit;
        } else {
        	$this->offset = 0;
        }
        
        $this -> html_files = $this->createHTMLfileList($this->documents, $settings->file_types, $settings->doc_list_limit, $this->offset, $settings->base_dir);
        $this -> html_folders = $this->createHTMLfolderList($settings->base_dir);
        
        //Get the total products based on filter must come AFTER the loop above
        $this->total_files = count($this->documents);
        
        //Get the total pages rounded up the nearest whole number
        $this->total_pages = ceil( $this->total_files / $settings->doc_list_limit );
        
        //When we filter, we want to know the range of products we're viewing
        $this->first_file_displayed = $this->offset + 1;
        
        // if the total products is more than the current offset x 2 + 2 then our last product is the offset + 2 or else it should be the total
        $this->last_file_displayed = $this->total_files >= ($this->offset * $settings->doc_list_limit) + $settings->doc_list_limit ? $this->offset + $settings->doc_list_limit : $this->total_files;
        
        // Display the current range in view
        if ($this->first_file_displayed === $this->last_file_displayed) {
        	$this->range = 'the Last of ' . $this->total_files . ' documents';
        } else {
        	$this->range = $this->first_file_displayed . ' - ' . $this->last_file_displayed . ' of ' . $this->total_files . ' documents';

        }
    }
    
    function setDocumentPath($base_dir, $current_dir, $file_name){
        $this->file = $base_dir.'/';
            if (!empty($current_dir)){
               $this->file .= $current_dir.'/'; 
            }
        $this->file .= $file_name;
        return $this->file; 
    }
    
    function deleteDocument($file_to_delete){
        if ($this->checkDocumentExists($file_to_delete)==true){
            unlink($file_to_delete);
        } 
    }
    
    function checkDocumentExists($file_to_check){
        if (file_exists($file_to_check)){
            return true;
        } else {
            return false;
        }    
    }
    
    function moveDocument($document_to_move, $document_new_location){
        rename($document_to_move, $document_new_location);
    }
    
    
    function setFolders($base_dir, $active_dir){
        $this->folders = $this->getSubFolders($base_dir, $active_dir);
    }
     
    function getActiveDir(){
        if (isset($_GET['file_current_directory'])) {
            $this->active_dir = $_GET['file_current_directory'];    
        }
        if (isset($_GET['dir'])) {
            $this->active_dir = $_GET['dir'];    
        }
        if (isset($_POST["file_current_dir"])) {
            $this->active_dir = $_POST["file_current_dir"];
        } 
    }
    
    
    function getAllDocuments($base_dir){
        $this->dir_to_scan = $base_dir.'/'.$this->active_dir;
        $this->documents = scandir($this->dir_to_scan);
        $this->documents = $this->delEmptyDirs($this->documents);
        $this->documents = $this->delDirs($this->documents, $base_dir);
   
    }
    
    function delEmptyDirs($documents) {
        foreach ($documents as $key => $value) {
            if ('.' == $value || '..' == $value){
                unset($documents[$key]);
            } 
        }
        return $documents;
    }
    
    function delDirs($documents, $base_dir) {
        foreach ($documents as $key => $value) { 
            if (is_dir($base_dir.'/'.$value)){
                unset($documents[$key]);
            }
        }
        return $documents;
    }
       
     
    function createHTMLfileList($files, $file_types,$doc_list_limit,$offset,$base_dir){
        $files = array_slice($files, $offset, $doc_list_limit);
        foreach ($files as $key => $value) { 
            $this->path_parts = pathinfo($value);
            $this->ext = $this->path_parts['extension'];

            if (array_key_exists($this->ext, $file_types)) {
                $this->documentList .= '<span id="document-row"><img src="images/'. $file_types[$this->ext] . '" id="icon" />';
            } else {
                $this->documentList .= '<span id="document-row"><img src="images/icon_general.png" id="icon" />';
            }
            $this->documentList .= '<span id="document-title"><a href="'. $base_dir .'/'. $this->active_dir . '/' . $value . '" id="documentLink">';
            $this->documentList .=  $value;
            $this->documentList .=  '</a></span>';
            
            $this->documentList .= '<span id="icon_edit"><a href="documents.php?file_to_edit='.$value.'&action=edit&file_current_directory='.$this->active_dir.'"><img src="images/icon_edit.png"></a></span>';
            $this->documentList .= '</span>';   
        }
            
        return $this->documentList; 
    }
    

    function createHTMLfolderList($base_dir){
        if (empty($this->active_dir)){
            $this->html_folders = $this->getSubFolders($base_dir, null);
            $this->subFolders .= "<header><h2>Subfolders</h2></header>";
            foreach ($this->html_folders as $key => $value) { 
                $this->subFolders .=  '<h3><a href="documents.php?dir='.$value.'">'.$value.'</a></h3>';
            }
        } else {
            $this->subFolders = '<header><h2>No subfolders</h2><h3><a href="documents.php">Return to parent folder</a></h3></header>';
        }
        return $this->subFolders;
    }
}



?>