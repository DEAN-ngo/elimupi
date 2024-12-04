<?php

class CSV{
    static function parseAsCSV( $text, $delimiter = null, $hasHeader = true ){
        $columns = [];
        $data = [];
        $lines = str_getcsv( $text, "\n" );
    
        foreach( $lines as $line ){
            if( empty( $columns ) && $hasHeader )
                $columns = str_getcsv( $line, $delimiter );
            else
                $data[] = str_getcsv( $line, $delimiter );
        }
        return array( 'columns' => $columns, 'data' => $data );
    }
}