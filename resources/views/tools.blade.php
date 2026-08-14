<?php
set_time_limit(300);
ini_set('max_execution_time', 300);
?>

<!DOCTYPE html>
<html>
<head>

<title>Tools Page</title>
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow">

<style>

body{
font-family: Arial;
margin:40px;
}

input[type=text]{
width:500px;
padding:10px;
font-size:16px;
}

button{
padding:10px 20px;
font-size:16px;
background:green;
color:white;
border:none;
cursor:pointer;
}

table{
margin-top:20px;
border-collapse:collapse;
width:100%;
}

th,td{
border:1px solid black;
padding:10px;
text-align:left;
}

th{
background:#f2f2f2;
}

#loader{
display:none;
font-size:20px;
color:green;
margin-top:20px;
}

</style>

<script>

function showLoader(){
document.getElementById("loader").style.display="block";
}

</script>

</head>

<body>

<h1>Tools Page</h1>

<p>Search Businesses</p>

<form method="GET" onsubmit="showLoader()">

<input type="text"
name="query"
placeholder="mental health clinics in San Jose CA"
required>

<button type="submit">
Search
</button>

</form>

<div id="loader">
Loading data please wait...
</div>

<?php


// EMAIL SCRAPER
function getEmailFromWebsite($website){

if($website=="N/A") return "N/A";

$ch=curl_init();

curl_setopt($ch,CURLOPT_URL,$website);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_TIMEOUT,5);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);

$html=curl_exec($ch);

curl_close($ch);

if(!$html) return "N/A";

preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i",$html,$matches);

return $matches[0] ?? "N/A";

}



// WhatsApp Check
function whatsappCheck($phone){

if($phone=="N/A") return "Unknown";

/* Remove + space - etc */
$phone = preg_replace('/[^0-9]/','',$phone);

if(strlen($phone) < 10)
return "Unknown";


$url="https://api.whatsapp.com/send?phone=".$phone;

$ch=curl_init();

curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_NOBODY,true);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_TIMEOUT,5);

curl_exec($ch);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);


if($httpcode==200 || $httpcode==301 || $httpcode==302)
return "Yes";

return "No";

}



if(isset($_GET['query'])){

$query = $_GET['query'];

echo "<h3>Searching for: ".$query."</h3>";

$apiKey="AIzaSyB7P4M6WoEMzVLgJVGSVzfFpI_Uj17msAg";


$url="https://maps.googleapis.com/maps/api/place/textsearch/json?query="
.urlencode($query)
."&key=".$apiKey;


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

curl_close($ch);


$data=json_decode($response,true);


if(!isset($data["results"])){

echo "No results found";
exit;

}



echo "<table>";

echo "<tr>

<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>Website</th>
<th>WhatsApp</th>
<th>Rating</th>
<th>Reviews</th>
<th>Open Time</th>
<th>Close Time</th>
<th>Status</th>
<th>Address</th>

</tr>";



$results=array_slice($data["results"],0,50);



foreach($results as $place){

$placeId=$place["place_id"];



$detailsUrl="https://maps.googleapis.com/maps/api/place/details/json?place_id="
.$placeId
."&fields=name,international_phone_number,website,formatted_address,rating,user_ratings_total,opening_hours,business_status"
."&key=".$apiKey;



$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $detailsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$detailsResponse = curl_exec($ch);

curl_close($ch);



$detailsData=json_decode($detailsResponse,true);

$d=$detailsData["result"] ?? [];



$name=$d["name"] ?? "";

$phone=$d["international_phone_number"] ?? "N/A";

$website=$d["website"] ?? "N/A";

$address=$d["formatted_address"] ?? "";

$rating=$d["rating"] ?? "N/A";

$reviews=$d["user_ratings_total"] ?? "0";

$status=$d["business_status"] ?? "UNKNOWN";



$email=getEmailFromWebsite($website);

$whatsapp=whatsappCheck($phone);



$open="N/A";
$close="N/A";

if(isset($d["opening_hours"]["weekday_text"][0])){

$time=$d["opening_hours"]["weekday_text"][0];

$parts=explode("–",$time);

$open=$parts[0] ?? "N/A";

$close=$parts[1] ?? "N/A";

}



echo "<tr>";

echo "<td>".$name."</td>";

echo "<td>".$phone."</td>";

echo "<td>".$email."</td>";

echo "<td>".$website."</td>";

echo "<td>".$whatsapp."</td>";

echo "<td>".$rating."</td>";

echo "<td>".$reviews."</td>";

echo "<td>".$open."</td>";

echo "<td>".$close."</td>";

echo "<td>".$status."</td>";

echo "<td>".$address."</td>";

echo "</tr>";

}


echo "</table>";

}

?>

<br><br>

<a href="/">
Go Back Home
</a>

</body>
</html>