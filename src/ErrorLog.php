<?php
namespace TymFrontiers;

class ErrorLog{
  use Helper\MySQLDatabaseObject,
      Helper\Pagination;

  protected static $_primary_key='id';
  protected static $_db_name;
  protected static $_table_name='error_log';
	protected static $_db_fields = ["id","rank","ref","info","file","line","url","_created"];

  public $id;
  public $rank;
  public $ref;
  public $info;
  public $file;
  public $line;
  public $url;

  protected $_created;

  public $errors = [];

  function __construct(int $rank=0, string $ref='',string $info='',string $file='', string $line='', string $to_file=''){
    $this->rank = $rank;
    if( !empty($ref) ) $this->ref = $ref;
    if( !empty($info) ) $this->info = $info;
    if( !empty($file) ) $this->file = $file;
    if( !empty($line) ) $this->line = $line;

    if( !empty($this->info) ) $this->record($to_file);
  }
  public function record(string $filename=''){
    $this->url = !empty($this->url) ? $this->url : (
      ( \defined('THIS_PAGE') ? THIS_PAGE : null )
    );
    if(
      empty($this->ref) OR
      empty($this->info) OR
      empty($this->file) OR
      empty($this->line)
    ){
      $this->error['record'][] = [256,3,"Required property not set, Following properties must be set.\r\n [rank]: Viewer rank,\r\n [ref]: reference,\r\n [info]: Error info/detail,\r\n [file]: Where error occurred,\r\n [line]: Where error occurred.",__FILE__,__LINE__];
      return false;
    }
    if( !empty($filename) && \file_exists( \pathinfo($filename,PATHINFO_DIRNAME) ) ){
      return $this->_write($filename);
    }else{
      // check for database definition
      if( !\defined('LOG_DB') ){
        $this->errors['record'][] = [3,256,"Log database name not defined! \r\n {$db_tbl_help}",__FILE__,__LINE__];
        return false;
      }
      self::$_db_name = LOG_DB;      // record to database
      $this->_create();
    }
    return true;
  }
  private function _write(string $filename){
    $msg = '#:';
    $msg .= BetaTym::MYSQL_DATETYM_STRING;
    $msg .= " #:{$this->rank}";
    $msg .= " #:{$this->ref}";
    $msg .= " #:{$this->info}";
    $msg .= " #:{$this->file}";
    $msg .= " #:{$this->line}";
    $msg .= " #:{$this->url}";
    $file = new File($filename);
    return $file->write(true);
  }

}
