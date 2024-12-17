<?php
$pageTitle = "DEAN - ElimuPi - Folder management";
require_once ("settings.inc.php");

class Folder {
    
    public $new_folder_name;
    public $base_dir;
    public $input_select_folders;
    public $input_select_name;
    public $input_select_empty_value;
    public $input_select_delete_folder;
    public $input_select_move_files_to_folder;
    public $all_folders = array();
    public $file_count;
    public $error_message = array();
    public $folder_to_add;        
    
    public function __construct(){
        $settings = new Settings;
        
        if (!empty($_POST["folder_to_delete"])){
            if ($this->checkIfEmptyFolder($settings->base_dir.'/'.$_POST["folder_to_delete"])==true){
                $this->deleteFolder($settings->base_dir.'/'.$_POST["folder_to_delete"]);
            }
        }
        
        if (!empty($_POST["folder_to_add"])){
            $this->folder_to_add = $this->prepareNewFolderName($_POST["folder_to_add"]);
            if ($this->checkIfFolderExists($settings->base_dir.'/'.$this->folder_to_add)==true){
                $this->createNewFolder($settings->base_dir.'/'.$this->folder_to_add);
            } else {
                $this->error_message['add_folder'] = 'This folder name already exists. Please choose a different folder name.';
                            
            }
        }
        
        $this->all_folders = $this->getSubFolders($settings->base_dir, null);
        $this->input_select_delete_folder = $this->inputSelectFolders($this->all_folders, 'folder_to_delete', 'Select a folder to delete','','','');
        $this->input_select_move_files_to_folder = $this->inputSelectFolders($this->all_folders, 'move_files_folder', 'Select a folder to move the files to','','','');
        
    }
    
    public function createNewFolder($new_folder_name){
        if (!file_exists($new_folder_name)) {
            mkdir($new_folder_name, 0777, true); 
        } 
    }
    
    public function deleteFolder($folder_to_delete){
        if (file_exists($folder_to_delete)) {
            rmdir($folder_to_delete); 
        }
    }
    
    public function checkNewFolderName($new_folder_name, $min_folder_name_length, $max_folder_name_length){
        if ($this->checkMinLenghtFolder($new_folder_name,$min_folder_name_length) == true && $this->checkMaxLenghtFolder($new_folder_name,$max_folder_name_length) == true){
            return true;
       }
    }
    
    public function checkMinLenghtFolder($new_folder_name,$min_folder_name_length){
        if (strlen($new_folder_name) >= $min_folder_name_length) {
            return true;
        } else {
            $this->message = "Folder name is too short. Should be at least 4 characters";
            return false;
        } 
    }
    
    public function checkMaxLenghtFolder($new_folder_name,$max_folder_name_length){
        if (strlen($new_folder_name) <= $max_folder_name_length) {
            return true;
        } else {
            $this->message = "Folder name is too long. Should be at most 16 characters";
            return false;
        } 
    }
    
    public function prepareNewFolderName($new_folder_name){
        $new_folder_name = strtolower($new_folder_name);
        $new_folder_name = preg_replace("/[^a-zA-Z0-9]/", "", $new_folder_name);
        $new_folder_name = trim($new_folder_name);
        return $new_folder_name;
    }
    
    function getSubFolders($base_dir,$active_dir) {
        $this->folders = scandir($base_dir);
        foreach ($this->folders as $key => $value) {
            if ('.' == $value || '..' == $value){
                unset($this->folders[$key]);
                continue;
            }
            if (!is_dir($base_dir.'/'.$value)){
                unset($this->folders[$key]);
                continue;
            }
            if ($value == $active_dir){
                unset($this->folders[$key]);    
            }
        }   
        return $this->folders;
    }
    
    public function checkIfFolderExists($folder_to_add){
        echo $folder_to_add;
        if (!file_exists($folder_to_add)) {
            return true;
        } else {
            return false;
        }
    }
     
    public function inputSelectFolders($folders, $input_select_name, $input_select_empty_value, $current_folder, $custom_key, $custom_value){
        print_r($folders);
        $this->input_select_folders .= '<select id="select-folders" name="'.$input_select_name.'">';
        $this->input_select_folders .= '<option id="" value="'.$input_select_empty_value.'">'.$input_select_empty_value.'</option>';
        if (!empty($custom_key) && !empty($custom_value) && $custom_value == $current_folder){
            $this->input_select_folders .= '<option id="" value="'.$custom_key.'">'.$custom_value.'</option>';    
        }
        foreach ($folders as $key => $value){
            if ($current_folder != $value){
                $this->input_select_folders .= '<option id="'.$value.'" value="'.$value.'">'.$value.'</option>';
            }
        }
        $this->input_select_folders .= '</select>';
        return $this->input_select_folders;
    }
   
    
    public function checkIfEmptyFolder($folder_to_check){
        $this->file_count = iterator_count(new FilesystemIterator($folder_to_check, FilesystemIterator::SKIP_DOTS));
        if ($this->file_count == 0){
            return true;
        } else {
            $this->error_message['delete_folder'] = 'The folder you want to delete is not empty. Move or delete the files in this folder before you continue.';
            return false;
        }
    } 
   
}

?>