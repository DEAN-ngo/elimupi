<?php
$pageTitle = "DEAN - ElimuPi - Document upload";
require_once ("folders.inc.php");
require_once ("settings.inc.php");

class Upload extends Folder {
    
    public $upload_dir;
    public $tmp;
    public $file_name;
    public $type;
    public $target_file;
    public $message;
    public $uploadsuccessful;
    public $allowed_file_types = array();
    public $file_type;
    public $dropdownsubfolders;
    public $formselectfolders;
    public $new_folder_name;

    
    function __construct(){
        $settings = new Settings;
        $this->allowedfiletypes = $settings->file_types;
        $this->uploadsuccessful = false;
        $this->setFolders($settings->base_dir);
        $this->dropDownSubFolders($this->folders);
        
        if(isset($_FILES['fileToUpload'])){
            $this -> upload_dir = $this->setUploadDir($settings->base_dir, $settings->min_folder_name_length, $settings->max_folder_name_length);
            $this -> file_name = $_FILES["fileToUpload"]["name"];
            $this -> tmp_file = $_FILES["fileToUpload"]["tmp_name"];
            $this -> target_file = $this->upload_dir .'/'. basename($this->file_name);
            $this->uploadprocedure($settings->max_file_size,$settings->file_types);
        }
    }
 
    function setFolders($base_dir){
        $this->folders = $this->getSubFolders($base_dir, null);
    }
  
    public function setUploadDir($base_dir,$min_folder_name_length,$max_folder_name_length){;
        if ($_POST["uploadfolder"]=="newFolder" && $this->checkNewFolderName($_POST["newFolderName"],$min_folder_name_length, $max_folder_name_length)==true){
            $this->upload_dir = $base_dir.'/'.$this->prepareNewFolderName($_POST["newFolderName"]);
            $this->createNewFolder($this->upload_dir);
        } elseif ($_POST["uploadfolder"]=="mainFolder") {
            $this->upload_dir = $base_dir;
        } elseif (!empty($_POST["uploadfolder"]) && $_POST["uploadfolder"]!="newFolder"){
            $this->upload_dir = $base_dir.'/'.$_POST["uploadfolder"];
        }
        return $this->upload_dir;
    }
    

    public function dropDownSubFolders($folders){
        
        $this->formselectfolders .= '<div class="radio">';
        $this->formselectfolders .= '<input type="radio" id="main" name="uploadfolder" value="mainFolder">';
        $this->formselectfolders .= '<label for="mainFolder">in the main folder</label>';
        $this->formselectfolders .= '</div>';
        
        foreach ($folders as $key => $value){
            $this->formselectfolders .= '<div class="radio">';
            $this->formselectfolders .= '<input type="radio" id="'.$value.'" name="uploadfolder" value="'.$value.'">';
            $this->formselectfolders .= '<label for="'.$value.'">sub folder: '.$value.'</label>';
            $this->formselectfolders .= '</div>';
        }
        
        $this->formselectfolders .= '<div class="radio">';
        $this->formselectfolders .= '<input type="radio" id="newFolder" name="uploadfolder" value="newFolder">';
        $this->formselectfolders .= '<label for="newFolder">Create a new folder: </label>';
        $this->formselectfolders .= '<input id="input-text" type="text" id="newFolderName" name="newFolderName">';
        $this->formselectfolders .= '</div>';
        
    }
       
    public function uploadprocedure($max_file_size, $allowed_file_types){
        if ($this->checkFileExists()== true){
            if ($this->checkFileSize($max_file_size)== true){
                if ($this->checkFileType($allowed_file_types)== true){
                    if ($this->uploadFile()== true){
                        $this->message = "Upload successful";
                        $this->uploadsuccessful = true;
                    } else {
                        $this->message = "Upload failed";
                    }
                } else {
                    $this->message = "Filetype is not allowed";
                }
            } else {
                $this->message = "File is too large";
            }
        } else {
            $this->message = "File already exists.";
        }
    }
    
    public function uploadFile(){
        if(move_uploaded_file($this->tmp_file, $this->target_file)){
            return true;
        }
    }
    
    public function checkFileExists(){
        if (!file_exists($this->target_file)) {
            return true; 
        }
    }
    
    public function checkFileSize($max_file_size){
        if ($_FILES["fileToUpload"]["size"] < $max_file_size) {
            return true;
        }
    }
    
    public function checkFileType($allowed_file_types){
        $this->file_type = strtolower(pathinfo($this->target_file,PATHINFO_EXTENSION));
        if (array_key_exists($this->file_type, $allowed_file_types)){
            return true;
        }
    }    
}
?>