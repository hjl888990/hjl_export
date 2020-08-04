<?php
namespace Export\Storage\Csv;

class Csv
{
    protected $filename;

    protected $mode;

    public function __construct($filename = 'php://output', $mode = 'w')
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }

    public function __get($name)
    {
        if ($name == 'handle') {
            $this->handle = fopen($this->filename, $this->mode);
            return $this->handle;
        }
        throw new \UnexpectedValueException("$name not found on class");
    }

    // 写入BOM头
    public function writeBom() {
        fprintf($this->handle, chr(0xEF).chr(0xBB).chr(0xBF));
    }
    
    public function write($data)
    {
        try {
            foreach ($data as &$value) {
                if ( is_numeric( $value ) && ( strlen( floor( $value ) ) > 10 ) ) {
                    $value = "\t".$value;
                }
                $value = iconv('utf-8', 'gbk//IGNORE', $value);
            }
            fputcsv($this->handle, $data);
        } catch (\Exception $e) {}
        return $this;
    }

    public function getContents()
    {
        $contents = '';
        try {
            fseek($this->handle, 0);
            $contents = stream_get_contents($this->handle);
        } catch (\Exception $e) {}
        return $contents;
    }
    
    public function output($filename)
    {
        $filename = iconv( 'utf-8', 'gb2312', $filename );
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        flush();
    }
    
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    public function delete()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function getFilename()
    {
        return $this->filename;
    }

     public static function buildCustomCsvDownLoad ($excel_text, $excel_data, $excel_name) {
         if ( empty( $excel_data ) || empty( $excel_text ) || empty( $excel_name ) ) {
             return;
         }
         
         $excel_name = iconv( 'utf-8', 'gb2312', $excel_name );
         header("Content-type: text/csv"); 
         header("Content-Disposition: attachment; filename=".$excel_name.".csv"); 
         header("Pragma: no-cache"); 
         header("Expires: 0"); 
 
         $csv_data = array();
         $csv_write = fopen( "php://output", 'w' ); 
         foreach( $excel_text as $key => $value ) { 
             $excel_text[$key] = iconv( 'utf-8', 'gbk', $value );
         } 
         fputcsv( $csv_write, $excel_text );
         
         foreach($excel_data as $key => $value) {
             foreach( $value as $item_key => $item_value ) {
                 if ( is_numeric( $item_value ) && ( strlen( floor( $item_value ) ) > 10 ) ) {
                     $item_value = "\t".$item_value;
                 }
                 
                 $value[$item_key] = iconv( 'utf-8', 'gbk', $item_value );
             }
             fputcsv( $csv_write, $value );
         }
        
         fclose($csv_write); 
         flush();
         exit;
     }
     
     static public function filter($input_text, $flag = '')
     {
        $clean_text = "";
        //  Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $input_text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        //过滤苹果表情符
        $regexIos = '/[\x{e001}-\x{e537}]/u';
        $clean_text = preg_replace($regexIos, '', $clean_text);
        
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        //ab00 - abff #abed
        $regexDingbats = '/[\x{ab00}-\x{abFF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        // 过滤空白符和逗号
        if ($flag != 'record_remark') {
            $regexBlank = '/(,|\s|　|\xc2\xa0)/';
            $clean_text = preg_replace($regexBlank, ' ', $clean_text);
        }

        return $clean_text;
     }
}
