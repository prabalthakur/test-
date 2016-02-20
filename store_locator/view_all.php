
<?php
$customer_list = json_decode(file_get_contents($filename), 1);
foreach ($customer_list['customers'] as $key => $customer) {
    $zip = $customer['default_address']['zip'];
    if (!empty($customer['default_address']['address1'])) {
        $adress = ($customer['default_address']['address1']);
    } else {
        $adress = ($customer['default_address']['address2']);
    }
    $country = $customer['default_address']['country'];
    $full_name = ($customer['default_address']['name']);
}
?>
<html>
    <body>
        <ul class="nav" style="  max-height: 300px; overflow-y:scroll; ">
            <?php foreach ($result_array as $key => $customer) { ?>

                <li class="list-group-item disabled" data-value="<?php echo $key + 1; ?>" style="alignment:left;cursor: pointer;">  <div class="title"><?php echo ($customer['full_name']); ?></div>
                    <div class="address">
                        <?php echo ($customer['adress']); ?>
                        <br>
                        <?php echo ($customer['country']); ?>, <?php echo ($customer['zip']); ?>
                    </div></li>


            <?php } ?>
        </ul>
    </body>
</html>