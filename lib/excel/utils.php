<?php

/**
****************************************************************************
* Some PHP Excel Utility Functions
****************************************************************************/

class ExcelUtils{

	/**
   * Returns the Julian date of the Windows Excel epoch (1 Jan 1900).
   *
 	 * @return integer  Julian date
   */
  static function _GetExcelEpoch() {
    return GregorianToJD(1,1,1900); // Windows Excel epoch
  }

  /**
   * Converts a MySQL Datetime field value to Excel datetime values.
   *
   * @param string  $datetime  MySQL datetime (dd-mm-yyyy hh:mm:ss)
   * @param float              Excel datetime value.
   */
  static function DatetimeToExcel($datetime) {
    $tmp = explode(" ", $datetime);
    $date = explode("-", $tmp[0]);
    if(isset($tmp[1])) $time = explode(":", $tmp[1]);
    $date1 = GregorianToJD($date[1],$date[2], intval( $date[0], 10 ) );
    $epoch = self :: _GetExcelEpoch();
    $frac = (($time[0] * 60 * 60) + ($time[1] * 60) + $time[2])/(24*60*60);
    
    return ($date1 - $epoch + 2 + $frac);
  }

  /**
   * Converts a UNIX timestamp value to Excel datetime values.
   *
   * @param int  $timestamp  UNIX timestamp
   * @param float            Excel datetime value.
   */
  static function TimestampToExcel($timestamp) {
    
  	return 
    	self :: MysqlDatetimeToExcel( date( "d-m-Y H:i:s", $timestamp ) );
    	
  }
	

}
?>