<?php
$app_id = isset($_GET['id']) ? $_GET['id'] : exit('No ID');
curl_and_embed("https://script.google.com/macros/s/$app_id");

function curl_and_embed($url) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, "$url/exec");
    curl_setopt($curl, CURLOPT_HEADER, 0);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $base_url = $url;
    $result = curl_exec($curl);
    $result2 = str_replace("src='gwt", "src='$base_url/gwt", $result);
    $result3 = str_replace('</head>', '<style>.warning-panel { display: none; } div.userapp-root { height: auto; } body { height: auto; }</style></head>', $result2);

    curl_close($curl);

    echo $result3;
}
?>

