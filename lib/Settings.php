<?php

class Settings {
	
	function assign(&$var) {
		$all = Settings::getAll ();
		
		if (is_array ( $all )) {
			if (is_object ( $var )) {
				foreach ( $all as $row ) {
					$normalized_name = Settings::_normalizeName ( $row['name'] );
					if (!is_array($row['value']))
						$var->$normalized_name = html_entity_decode($row['value']);
					else
						$var->$normalized_name = $row['value'];
				}
			} else {
				foreach ( $all as $row ) {
					$normalized_name = Settings::_normalizeName ( $row ['name'] );
					if (!is_array($row['value']))
						$var->$normalized_name = html_entity_decode($row['value']);
					else
						$var->$normalized_name = $row['value'];
				}
			}
		}
	}
	
	function get($name) {
		$result = db_query_array ( "SELECT value
								  FROM settings
								  WHERE name = '" . addslashes ( $name ) . "'", '', true );
		return $result ['value'];
	}
	
	function set($name, $value) {
		if (stristr($value,'array:')) {
			$v = str_ireplace('array:','',$value);
			$v1 = DB::serializeCommas($v,true);
			$value = $v1;
		}
		$affected_rows = db_update ( 'settings', $name, array ('value' => mysql_real_escape_string(htmlentities($value)) ), 'name' );
		
		if (! $affected_rows) {
			db_insert ( 'settings', array ('name' => $name, 'value' => mysql_real_escape_string(htmlentities($value)) ), '', true );
		}
		
		return true;
	}
	
	function getAll() {
		
		$sql = "SELECT *
		
		FROM settings";
		
		$result = db_query_array ( $sql );
		if ($result) {
			foreach ($result as $key => $row) {
				$result[$key]['value'] = String::checkSerialized(html_entity_decode($row['value']));
			}
		}
		return $result;
	}
	
	function getStructured() {
		$sql = "SELECT * FROM settings";
		$result = db_query_array($sql);
		
		if ($result) {
			foreach ($result as $row) {
				$name = $row['name'];
				$row['value'] = String::checkSerialized(html_entity_decode($row['value']));
				if (!is_array($row['value'])) {
					$ret[$name] = (stristr($row['value'],'"')) ? htmlentities($row['value']) : $row['value'];
				}
				else {
					$ret[$name] = String::fauxArray($row['value']);
				}
			}
		}
		return $ret;
	}
	
	function _normalizeName($str) {
		$str = strtolower ( $str );
		return $str;
		//return preg_replace ( '/[^a-z0-9_]/', '', $str );
	}
	
	function mysqlTimeDiff() {
		date_default_timezone_set($CFG->default_timezone);
		
		$sql = "SELECT NOW() AS ctime";
		$result = db_query_array($sql);
		$sqltime = strtotime($result[0]['ctime']);
		$phptime = time();
		return (($sqltime - $phptime)/3600);
	}
}

?>