<?php
function pr($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
function getLnt($zip,$adress) {
    if(!empty($zip)){
    $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($zip) . "&sensor=false";
    $result_string = file_get_contents($url);
    $result = json_decode($result_string, true);
    if (!isset($result['results'][0]) || empty($result['results'][0])) {
        return 0;
    } else {
        $result1[] = $result['results'][0];
        $result2[] = $result1[0]['geometry'];
        $result3[] = $result2[0]['location'];
        return $result3[0];
    }
}else{
     $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($adress) . "&sensor=false";
    $result_string = file_get_contents($url);
    $result = json_decode($result_string, true);
    if (!isset($result['results'][0]) || empty($result['results'][0])) {
        return 0;
    } else {
        $result1[] = $result['results'][0];
        $result2[] = $result1[0]['geometry'];
        $result3[] = $result2[0]['location'];
        return $result3[0];
    }
}
}
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


$filename = 'customers.json';

$customer_list = json_decode(file_get_contents($filename), 1);
$result_array = array();
if (!empty($_GET['search'])) {
    $search_post = $_GET['search'];
    $search_post = trim((strtolower($search_post)));
}

function trimandlowercase($string) {
    return trim(strtolower($string));
}
$not_found_counter = 0;
foreach ($customer_list['customers'] as $key => $customer) {
    $note = $customer['note'];
    $link = '';
    $image = '';
    $show = 0;
    if (isset($customer['note'])) {
        $notes = explode(";", $customer['note']);

        foreach ($notes as $note) {

            $note = explode("=", $note);
            if (!empty($note['0']) && (!empty($note[1]))) {
                $linkl = trim($note['0']);
                if ($linkl == 'store_link') {
                    $link = trim($note['1']);
                } elseif ($linkl == 'image') {
                    $image = trim($note['1']);
                } elseif ($linkl == 'include_on_map') {
                    $show = trim($note['1']);
                }
            }
        }
    }

    if (!$show) {
        $not_found_counter++;
        continue;
    }

    if (!isset($customer['default_address'])) {
        $not_found_counter++;
        continue;
    }
    if (empty($customer['default_address']['zip'])) {
        $not_found_counter++;
        continue;
    }
    $country_code = $customer['default_address']['country_code'];
    $province_code = $customer['default_address']['province_code'];
    $province = $customer['default_address']['province'];
    $country = $customer['default_address']['country'];
    $zip = $customer['default_address']['zip'];

    $latitude_longitude = getLnt($zip);
     
    if ($latitude_longitude != 0) {
        if (!empty($customer['default_address']['city'])) {
            $city = $customer['default_address']['city'];
        } else {
            $city = '';
        }

        

        $first_name = $customer['first_name'];

        $last_name = $customer['last_name'];

        if (!empty($customer['default_address']['address1'])) {
            $adress = ($customer['default_address']['address1']);
        } else {
            $adress = ($customer['default_address']['address2']);
        }
           $latitude_longitude = getLnt($adress);
        if (!empty($customer['default_address']['company'])) {
            $title = ($customer['default_address']['company']);
        } else {

            $title = ($customer['default_address']['name']);
        }
        $full_name = ($customer['default_address']['name']);
        $phone = ($customer['default_address']['phone']);

        $latitude = $latitude_longitude['lat'];
        $logitude = $latitude_longitude['lng'];
        $result_array[$key] = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'adress' => $adress,
            'full_name' => $full_name,
            'province' => $province,
            'zip' => $zip,
            'country' => $country,
            'latitude' => $latitude,
            'logitude' => $logitude,
            'title' => $title,
            'province_code' => $province_code,
            'country_code' => $country_code,
            'city' => $city,
            'phone' => $phone,
            'link' => $link,
            'image' => $image
        );
        if (isset($search_post) && !($search_post == trim(strtolower($zip)) || $search_post == trim(strtolower($province)) ||
                $search_post == trim(strtolower($country)) || $search_post == trim(strtolower($country_code)) ||
                $search_post == trim(strtolower($province_code)) || $search_post == trim(strtolower($city)))) {
            $not_found_counter++;
            continue;
                }
    }
}
if ($not_found_counter == count($customer_list['customers'])) {
    $error_not_found = TRUE;
}
$json_array = json_encode(array_values($result_array));
?>
<html>
    <head>
        <link  rel="stylesheet" href="assests/style.css" />
        <script src="assests/jquery.js"></script>
        <script src="http://maps.google.com/maps/api/js?sensor=false&libraries=geometry&v=3.22&key=AIzaSyAd_KEG3T1BoaJkirYLWu33ZWuWLweq2Zw" type="text/javascript">
        </script>
        <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="assests/maplace.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#search").on('click', function () {
                    $('form').submit();
                });

                var json_add = <?php echo $json_array; ?>;
                var lat = "";
                var lon = "";
                var i;
                var add_formatted = [];
                var html = "";
                if (json_add.length > 1) {
                    for (i = 0; i < json_add.length; i++) {
                        var title = json_add[i].title;

                        html = "<b>" + title + "</b> <br>" + json_add[i].adress + "<br>" + json_add[i].city + ","+ json_add[i].province + "," + json_add[i].zip + "<br>" + json_add[i].phone + "<br>" + "<a target='_blank' href=" + json_add[i].link + ">" + json_add[i].link + "</a>" + "<br>";
                        if (json_add[i].image != '') {
                            html +=  "<br><img class='store_image' src='" + json_add[i].image + "' />";
                        }
                        add_formatted.push({
                            'lat': json_add[i].latitude,
                            'lon': json_add[i].logitude,
                            'html': html,
                            'icon': 'http://maps.google.com/mapfiles/marker.png',
                        });
                    }
                } else {
                    var i = 0;
                    var title = json_add[i].title;
                    html = "<b>" + title + "</b> <br>" + json_add[i].adress + "<br>" + json_add[i].city + "," + json_add[i].province + "," + json_add[i].zip + "<br>" + json_add[i].phone + "<br><a target='_blank' href=" + json_add[i].link + ">" + json_add[i].link + "</a>" + "<br>";
                    if (json_add[i].image != '') {
                        html += "<br><img class='store_image' src='" + json_add[i].image + "' />";
                    }
                    add_formatted.push({
                        'lat': json_add[i].latitude,
                        'lon': json_add[i].logitude,
                        'title': json_add[i].title,
                        'html': html,
                        'icon': 'http://maps.google.com/mapfiles/marker.png',
                    });
                }

                var html_checks = {
                    //required: called by Maplace.js to activate the current voice on menu
                    activateCurrent: function (index) {
                        return;
                    },
                    //required: called by Maplace.js to get the html of the menu
                    getHtml: function () {
                        var self = this;
                        $('.list-group-item').on('click', function () {
                            self.ViewOnMap($(this).data('value'));
                        });
                        return;
                    }
                }
                var bigdata = new Maplace({
                    map_div: '#gmap',
                    show_infowindows: true,
                    locations: add_formatted,
                    shared: {
                        zoom: 8,
                        html: '%index'
                    }
                });
                bigdata.AddControl('checks', html_checks);
                var errornotfound = bigdata.Load({controls_type: 'checks'});



            });

        </script>
        <style>
            .dropdown-menu {
                max-height: 450px;
                overflow: hidden;
                overflow-y: auto;
            }
            .list-group-item:hover{
                background: #eee none repeat scroll 0 0;
            }
            .search_img{
                height:50px;
                width: 20%;
                margin-bottom: 1%;
                margin-left:1%; 
            }
            .right-inner-addon {
                position: relative;
            }
            input[type="submit"] {
                width:78%;
                height:50px;
            }
            .mj{
                background-image: url('plll.jpg');
                background-repeat: no-repeat;

            }
            .store_image{
                height: 120px;
                width: 160px;
            }
            .gm-style-iw{
                left:27px !important;
            }   
        </style>
    </head>
    <body style="background-color: white;">
        `   <div class="container col-ms-12">
            <div class="row ">
                <div class="col-md-4">
                    <img src='wee_logo.gif' alt='#'>

                    <div class="well sidebar-nav" style="margin-top: 10%;">
                        <h3 class="text-center">FIND A STORE</h3><br>
                        <form method="GET">
                            <div class="input-group">
                                <input class="form-control" style="top:1px;" type="search" placeholder="  Zip/Country/City"  name="search" value="<?php
                                if (isset($search_post)) {
                                    echo $_GET['search'];
                                }
                                ?>">
                                <span class="input-group-btn" id="search">
                                    <button class="btn btn-default glyphicon glyphicon-search" type="button"></button>
                                </span>
                            </div>
                                <!--<input style="width:16%; height:50px; position:absolute;  " type="submit" class="mj" value=".">-->
                        </form>
                        <?php if (!empty($_GET['search'])) { ?>
                            <a href="/store_locater" class="btn btn-info" style="margin-left:26%;">View All Records</a>
                        <?php } ?>
                        <br>
                        <?php if (isset($error_not_found)) { ?>
                        
                            <div class="alert alert-danger">
                            <strong>NO STORE FOUND! </strong>There is no store over here. 

                        </div> <?php } ?>
                        <br>
                        <ul class="nav" style="  max-height: 300px; overflow-y:scroll; ">
                            <?php foreach (array_values($result_array) as $key => $customer) { ?>
 

                                <li class="list-group-item" data-value="<?php echo $key+1 ; ?>" style="alignment:left;cursor: pointer;">  <div class="title"><?php
                                        if (!empty($customer['full_name'])) {
                                            echo ($customer['title']);
                                        } else {
                                            echo ($customer['full_name']);
                                        }
                                        ?></div>
                                    <div class="address">
                                        <?php echo ($customer['adress']); ?>
                                        <br>
                                        <?php echo ($customer['city']); ?>, <?php echo ($customer['province']); ?>, <?php echo ($customer['zip']); ?>
                                        <br>
                                        <?php echo ($customer['country']); ?>
                                        <br>
    <?php echo ($customer['phone']); ?>

                                    </div></li>

<?php } ?>
                        </ul>

                    </div>
                </div>
                <div class="col-md-8" >
                        <div id="gmap">

                        </div>
                     
                    
                </div>
            </div> 
        </div>
    </body>
</html>