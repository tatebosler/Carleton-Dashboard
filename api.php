<?php
  $open = array(); // this will be returned at the end
  date_default_timezone_set("America/Anchorage"); // Alaska time is being used. because REASONS.
  
  include_once('db.php'); // see README for details
  
  $dayArray = array("sun", "mon", "tue", "wed", "thu", "fri", "sat", "sun"); // includes extra sunday for next day lookup
  
  $now = time();
  $today = date("w", $now);
  $todayName = $dayArray[$today];
  $tomorrow = $today + 1;
  $tomorrowName = $dayArray[$tomorrow];
  
  if(!empty($_POST["buildings"])) {
    $buildings = $_POST["buildings"];
  } else {
    $buildings = array("Sayles-Hill Campus Center" => array("sayles"));
  }
  
  foreach($buildings as $category => $officeList) {
    $open[$category] = array();
    foreach($officeList as $facility) {
      $query_sanitized_office_name = mysqli_real_escape_string($db, $facility);
      $query = @mysqli_query($db, "SELECT * FROM ldc_hours WHERE facilityID = '$query_sanitized_office_name'");
      
      if(mysqli_num_rows($query) >= 1) {
        $results = mysqli_fetch_array($query);
        $open[$category][$facility] = array();
        
        $open[$category][$facility]["name"] = $results["facilityLongName"];
        
        if($results["term_24h"] == 1) {
          $open[$category][$facility]["status"] = "24h";
          $open[$category][$facility]["24h"] = true;
          if($results["restriction_rules"] == "always") {
            $open[$category][$facility]["status"] .= "-onecard";
          }
          continue;
        }
        
        // process hours      
        $today_hours = json_decode($results["term_".$todayName."_hours"], true);
        $tomorrow_hours = json_decode($results["term_".$tomorrowName."_hours"], true);
        $open[$category][$facility]["status"] = "closed";
        if($today_hours["closed"] == true) {
          $open[$category][$facility]["status"] = "closed";
        } else {     
          foreach($today_hours["hours"] as $hourBlock) {
            $openTime = strtotime($hourBlock["open"]);
            $close = strtotime($hourBlock["close"]);
            if ($now >= $openTime and $now < $close) {
              if(($close - $now) <= 3600) {
                $open[$category][$facility]["status"] = "warning";
              } else {
                $open[$category][$facility]["status"] = "open";
              }
              $open[$category][$facility]["closingTime"] = date("g:i a", $close + 3*60*60);
              switch($open[$category][$facility]["closingTime"]) {
                case "12:00 am":
                  $open[$category][$facility]["closingTime"] = "midnight";
                  break;
                case "12:00 pm":
                  $open[$category][$facility]["closingTime"] = "noon";
                  break;
                default: break;
              }
              break;
            }
            $open[$category][$facility]["status"] = "closed";
          }
        }
        
        // process if OneCard is required
        if(!empty($results["restriction_rules"]) and $open[$category][$facility]["status"] != "closed") {
          if($results["restriction_rules"] == "always") {
            $open[$category][$facility]["status"] .= "-onecard";
          }
        }
        
        // process next opening
        $nextOpen = $now;
        foreach($today_hours["hours"] as $hourBlock) {
          $openTime = strtotime($hourBlock["open"]);
          if ($now < $openTime) {
            $nextOpen = $openTime;
            $nextOpenDay = "today";
            break;
          }
        }
        
        if($nextOpen == $now) {
          if($tomorrow_hours["closed"] == true) {
            $nextOpen = 0;
          } else {
            $nextOpen = strtotime($tomorrow_hours["hours"][0]["open"]);
            $nextOpenDay = "tomorrow";
          }
        }
        
        if($nextOpen == 0) {
          $open[$category][$facility]["next"] = "closed tomorrow";
        } else {
          $open[$category][$facility]["next"] = "next open ".date("g:i a", $nextOpen + 3*60*60)." ".$nextOpenDay;
        }
      }
    }
  }
  
  echo json_encode($open);
?>
