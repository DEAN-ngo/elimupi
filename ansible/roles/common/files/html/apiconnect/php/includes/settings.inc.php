<?php

class Settings {

public $file_types = array();
public $base_dir;
public $doc_list_limit;
public $max_file_size;
public $min_folder_name_length;
public $max_folder_name_length;

    function __construct(){    
        $this->file_types['ppt'] = 'icon_presentation.png';
        $this->file_types['pptx'] = 'icon_presentation.png';
        $this->file_types['docx'] = 'icon_document.png';
        $this->file_types['doc'] = 'icon_document.png';
        $this->file_types['zip'] = 'icon_archive.png';
        $this->file_types['rar'] = 'icon_archive.png';
        $this->file_types['xlsx'] = 'icon_spreadsheet.png';
        $this->file_types['xlx'] = 'icon_spreadsheet.png';
        $this->file_types['jpg'] = 'icon_picture.png';
        $this->file_types['jpeg'] = 'icon_picture.png';
        $this->file_types['gif'] = 'icon_picture.png';
        $this->file_types['png'] = 'icon_picture.png';
        $this->file_types['mpg'] = 'icon_movie.png';
        $this->file_types['mpeg'] = 'icon_movie.png';
        $this->file_types['mp4'] = 'icon_movie.png';
        $this->file_types['avi'] = 'icon_movie.png';
        $this->file_types['mov'] = 'icon_movie.png';
        $this->file_types['pdf'] = 'icon_pdf.png';
        $this->file_types['mp3'] = 'icon_sound.png';
        
        $this->base_dir = 'documents/public';
        $this->doc_list_limit = 10; 
        $this->max_file_size = 100000; 
        $this->min_folder_name_length = 4;
        $this->max_folder_name_length = 16;     
    } 
}

?>