<?php
define('ONPATH', '');
error_reporting(0);
include '../../../config.php';

//mysql_connect($App['db']['host'],$App['db']['user'],$App['db']['pass']);
//mysql_select_db($App['db']['name']);

/**
 * mb_stripos all occurences
 * based on http://www.php.net/manual/en/function.strpos.php#87061
 *
 * Find all occurrences of a needle in a haystack
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
 */
function mb_stripos_all($haystack, $needle) {
 
  $s = 0;
  $i = 0;
 
  while(is_integer($i)) {
 
    $i = mb_stripos($haystack, $needle, $s);
 
    if(is_integer($i)) {
      $aStrPos[] = $i;
      $s = $i + mb_strlen($needle);
    }
  }
 
  if(isset($aStrPos)) {
    return $aStrPos;
  } else {
    return false;
  }
}
 
/**
 * Apply highlight to row label
 *
 * @param string $a_json json data
 * @param array $parts strings to search
 * @return array
 */
function apply_highlight($a_json, $parts) {
 
  $p = count($parts);
  $rows = count($a_json);
 
  for($row = 0; $row < $rows; $row++) {
 
    $label = $a_json[$row]["label"];
    $a_label_match = array();
 
    for($i = 0; $i < $p; $i++) {
 
      $part_len = mb_strlen($parts[$i]);
      $a_match_start = mb_stripos_all($label, $parts[$i]);
 
      foreach($a_match_start as $part_pos) {
 
        $overlap = false;
        foreach($a_label_match as $pos => $len) {
          if($part_pos - $pos >= 0 && $part_pos - $pos < $len) {
            $overlap = true;
            break;
          }
        }
        if(!$overlap) {
          $a_label_match[$part_pos] = $part_len;
        }
 
      }
 
    }
 
    if(count($a_label_match) > 0) {
      ksort($a_label_match);
 
      $label_highlight = '';
      $start = 0;
      $label_len = mb_strlen($label);
 
      foreach($a_label_match as $pos => $len) {
        if($pos - $start > 0) {
          $no_highlight = mb_substr($label, $start, $pos - $start);
          $label_highlight .= $no_highlight;
        }
        $highlight = '<span class="hl_results">' . mb_substr($label, $pos, $len) . '</span>';
        $label_highlight .= $highlight;
        $start = $pos + $len;
      }
 
      if($label_len - $start > 0) {
        $no_highlight = mb_substr($label, $start);
        $label_highlight .= $no_highlight;
      }
 
      $a_json[$row]["label"] = $label_highlight;
    }
 
  }
 
  return $a_json;
 
}

	function escape_str($str, $dbstatus)	
	{
		if (is_array($str))
    	{
    		foreach($str as $key => $val)
    		{
    			$str[$key] = escape_str($val);
    		}
    		
    		return $str;
    	}
	
		if (function_exists('mysql_real_escape_string') AND is_resource($dbstatus))
		{
			return mysql_real_escape_string($str, $dbstatus);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			return mysql_escape_string($str);
		}
		else
		{
			return ereg_replace("'","`",$str); //addslashes($str);
		}
	}

// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
 
// get what user typed in autocomplete input
$term = trim($_GET['term']);
 
$a_json = array();
$a_json_row = array();
 
$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Hanya teks yang di izinkan"));
$json_invalid = json_encode($a_json_invalid);
 
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
 
// SECURITY HOLE ***************************************************************
// allow space, any unicode letter and digit, underscore and dash
if(preg_match("/[^\040\pL\pN_-]/u", $term)) {
  print $json_invalid;
  exit;
}
// *****************************************************************************
 
// database connection

$conn = mysql_connect($App['db']['host'],$App['db']['user'],$App['db']['pass']);
if (!mysql_select_db($App['db']['name'])) {
    echo "Unable to select mydbname: " . mysql_error();
    exit;
}

if (!$conn) {
  echo 'Database connection failed...' . 'Error: ' .mysql_error();
  exit;
}

$sql = "SELECT * FROM cpmembers WHERE (vUsername LIKE '%".escape_str($term, $conn)."%') OR (vName LIKE '%".escape_str($term, $conn)."%')";
$sql .= " ORDER BY id ASC";

$result = mysql_query($sql);
while($row = mysql_fetch_assoc($result)) {
  $a_json_row["id"] = $row['vUsername'];
  $a_json_row["value"] = $row['vName']." (".$row['vUsername'].")"; //$row['idKecamatan'];
  $a_json_row["label"] = $row['vName']." (".$row['vUsername'].")";
  array_push($a_json, $a_json_row);
}

// highlight search results
$a_json = apply_highlight($a_json, $parts);
 
$json = json_encode($a_json);
print $json;

?>
