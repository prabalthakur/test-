<?php

function pr($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

$url = "https://277ae3422f5b0b4516b15ca707f11498:a8bb4e58ca4f68ba74d79375f74903f7@wee-gallery.myshopify.com/admin/customers/count.json";
$ch = curl_init($url); //set the url
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //specify return value as string
$server_output = curl_exec($ch); //get server output if you wish to error handle / debug
curl_close($ch); //close the connection
file_put_contents('manish.json', $server_output);
$count = json_decode(file_get_contents('manish.json'), 1);
$count = $count['count'];
$max_loop = ceil($count / 250);
$filename = 'customers.json';
for ($i = 0; $i <= $max_loop; $i++) {
    $url = "https://277ae3422f5b0b4516b15ca707f11498:a8bb4e58ca4f68ba74d79375f74903f7@wee-gallery.myshopify.com/admin/customers.json?fields=first_name,last_name,default_address,note&limit=250";
    $ch = curl_init($url); //set the url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //specify return value as string
    $server_output = curl_exec($ch); //get server output if you wish to error handle / debug
    curl_close($ch); //close the connection
$note = json_decode($server_output, 1);

    if (file_exists($filename)) {
        $old_data = file_get_contents($filename);
        if (!empty($old_data)) {
            $new_json_merged = json_encode(array_merge(json_decode($old_data, true), json_decode($server_output, true)));
            file_put_contents($filename, $new_json_merged);
        } else {
            file_put_contents($filename, $server_output);
        }
    } else {
        file_put_contents($filename, $server_output);
    }
}
 