<?php
// CACHE FUNCTION
function saveForecast($reCreate = false) {

	if ($reCreate) {

	    $BASE_URL = "http://query.yahooapis.com/v1/public/yql";

		$yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text="Adapazari, Sakarya (Turkey)") and u="c"';
	    $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&_maxage=3600&format=json";

	    // Make call with cURL
	    $session = curl_init($yql_query_url);
	    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
	    $json = curl_exec($session);

		// Cache the data
		file_put_contents("cache.txt", print_r($json, true));

	}

	$cached_data = json_decode(file_get_contents('cache.txt'));

	// Update Needed?
	$lastBuildDate = $cached_data->query->results->channel->lastBuildDate;
	$secondsSinceLastBuild = time() - strtotime($lastBuildDate);

	// How many days have it been?
	$days = intval(intval($secondsSinceLastBuild) / (3600*24));

    if($days > 1) {

        echo "<b>Last was $days days ago - Updated:</b> $lastBuildDate";
        return saveForecast(true);

    } else {

		echo "<b>Created today:</b> $lastBuildDate";
        return $cached_data;

    }
}

// PRINTER FUNCTION
function printForecast($data) {

	$result = $data->query->results;
	$txt = "";

	if ($result != "") {
		$day = 0;
		foreach($result->channel->item->forecast as $dayName) {

			$day++;
			$txt .= $dayName->day." ";
			$txt .= $dayName->high."/";
			$txt .= $dayName->low." ";
			$txt .= $dayName->text;
			if ($day == 7) {

				$txt .= ".";
				break;

			} else {

				$txt .= ", ";

			}

		}


	} else
    	$txt = "No Data";

    return $txt;

}

$data = saveForecast();

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<title>Weather SMS</title>
	<script type="text/javascript" src="copy2clipboard.js"></script>
</head>
<body>

	<div id="wheather-sms"><?=printForecast($data)?></div>

	<button onclick="select_all_and_copy(document.getElementById('wheather-sms'))" style="width: 300px; height: 60px;">Copy to Clipboard</button>

</body>
</html>