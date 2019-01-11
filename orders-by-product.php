<?php

    $prefix = $wpdb->prefix; // Database Prefix
    $excluded_products_list = "'Registration for Dreamwalking','CA-CAD TAX (5%)-1','registration: saltspring island 2018 dreaming retreat - Registration Shared Room','TAX (5%)-1','Registration: Dreamwalking Online Course - Tuition','US-TAX-1','Registration: Saltspring Island 2018 Dreaming Retreat - DEPOSIT Registration Private Room','Registration: Saltspring Island 2018 Dreaming Retreat - DEPOSIT Registration Shared Room','Registration: Saltspring Island 2018 Dreaming Retreat - Registration Private Room','US-TAX (5%)-1','CA-CAD (5%)-1','TAX-1','Registration: Dreamwalking Online Course - Tuition + $10 Donation to Scholarship Fund','CA-5%-1','Registration: Dreamwalking Online Course - Tuition + $20 Donation to Scholarship Fund','US-0%-1','sable','liselle','alicia','women','Payment #2 for Registration: Saltspring Island 2018 Dreaming Retreat','women2','joyce','adriane','Registration: Dreamwalking Online Course May 2018 - Tuition + $10 Donation to Scholarship Fund','Registration: Dreamwalking Online Course May 2018 - Tuition','Registration: Dreamwalking Online Course May 2018 - Tuition + $20 Donation to Scholarship Fund','jody','Registration: Dreamwalking Online Course August 2018 - Tuition','Registration: Dreamwalking Online Course August 2018 - Tuition + $10 Donation to Scholarship Fund','Registration: Dreamwalking Online Course August 2018 - Tuition + $20 Donation to Scholarship Fund','In box with bubble wrap','Bubble mailer','Bubble mailer with stiffener','Regular Parcel - approx. 4–5 days','Regular Parcel - approx. 2–3 days','Handling','Regular Parcel - approx. 5–6 days','Regular Parcel - approx. 3–4 days','Regular Parcel - approx. 7–8 days','Registration: Saltspring Island 2018 Dreaming Retreat','Registration: Dreamwalking Online Course November 2018 - Tuition + $20 Donation to Scholarship Fund','Registration: Dreamwalking Online Course November 2018 - Tuition','Registration: Dreamwalking Online Course November 2018 - Tuition + $10 Donation to Scholarship Fund','Registration: Dreamwalking Online Course November 2018','Regular Parcel - approx. 6–7 days','Registration: Dreamwalking Online Course August 2018','retreat','Regular Parcel - approx. 2–3 days','Regular Parcel - approx. 3–4 days','Shipping within the US','Regular Parcel - approx. 12–13 days','Regular Parcel','Regular Parcel - approx. 7&ndash;8 days','Regular Parcel - approx. 12&ndash;13 days','Regular Parcel - approx. 4&ndash;5 days','Regular Parcel - approx. 2&ndash;3 days', 'Regular Parcel - approx. 3&ndash;4 days'";

    $query = "
        SELECT DISTINCT order_item_name as name
        FROM {$prefix}woocommerce_order_items 
        WHERE TRIM(order_item_name) NOT IN ({$excluded_products_list})
    ";

    $products = $wpdb->get_results($query, "ARRAY_A");

?>

<h1>Orders by Product</h1>

<form method="post">
    <p>Select the products to view:</p>

    <p>
        <?php
            $count = 0;
            foreach ($products as $product) {
                $name = $product["name"];
                $id = "product_" . ++$count;

                ?> 
                    <input 
                        type="checkbox" 
                        id="<?php echo $id; ?>" 
                        name="<?php echo $id; ?>" 
                        value="<?php echo $name; ?>" 
                        <?php echo isset($_POST[$id]) ? "checked=checked" : ""; ?>
                    /> 
                    <label for="<?php echo $id; ?>"><?php echo $name; ?></label> <br />
        <?php } ?>
    </p>

    <p><button type="submit">View Orders</button></p>
</form>

<?php

function single_quote_list( $arr ){

    $return_arr = [];

    foreach($arr as $i){
        $str = "'" . $i . "'";
        array_push($return_arr, $str);
    }

    return implode(", ", $return_arr);
}

if(!empty($_POST)){

$products_str = single_quote_list($_POST);

$query = "
select
    p.ID as order_id,
    p.post_date,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
    max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
    max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
    max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
    max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
    max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
    max( CASE WHEN pm.meta_key = '_shipping_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_first_name,
    max( CASE WHEN pm.meta_key = '_shipping_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_last_name,
    max( CASE WHEN pm.meta_key = '_shipping_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_1,
    max( CASE WHEN pm.meta_key = '_shipping_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_2,
    max( CASE WHEN pm.meta_key = '_shipping_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_city,
    max( CASE WHEN pm.meta_key = '_shipping_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_state,
    max( CASE WHEN pm.meta_key = '_shipping_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_postcode,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
    max( CASE WHEN pm.meta_key = '_order_tax' and p.ID = pm.post_id THEN pm.meta_value END ) as order_tax,
    max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
    ( select group_concat( order_item_name separator '|' ) from {$prefix}woocommerce_order_items where order_id = p.ID ) as order_items,
    post_status
from
    {$prefix}posts p 
    join {$prefix}postmeta pm on p.ID = pm.post_id
    join {$prefix}woocommerce_order_items oi on p.ID = oi.order_id
where
    post_type = 'shop_order' and
    post_status IN ('wc-completed','wc-processing') and
    order_item_name IN ({$products_str})
group by
    p.ID
ORDER BY post_date
";

/* Sample Data 
$query = "
    select
        '8663' as order_id,
        '2018-11-01 12:28:25' as post_date,
        'Jesse' as _billing_first_name,
        'Burton' as _billing_last_name,
        'jesse@burtonmediainc.com' as billing_email,
        '12661 Nanell Ln.' as _billing_address_1,
        '' as _billing_address_2,
        'St. Louis' as _billing_city,
        'MO' as _billing_state,
        '63127' as _billing_postcode,
        '180' as order_total,
        '9' as order_tax,
        'wc-completed' as post_status
";*/
 
$result = $wpdb->get_results($query, "ARRAY_A");

$dir = plugin_dir_path( __FILE__ );
$file_path = $dir . 'output/orders-by-product.csv';

$fp = fopen($file_path, 'w');

foreach ($result as $order) {
    fputcsv($fp, $order);
}

fclose($fp);

?>

<h2>Showing Orders That Include: <span style="font-weight: normal;"><?php echo implode(", ", $_POST); ?></span></h2>
<p><a href="<?php echo plugins_url("bm-woo-reports") . "/output/orders-by-product.csv"; ?>" target="_blank">Export CSV</a></p>

<table class="widefat fixed" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 60px;">Order #</th>
            <th style="width: 120px;">Order Date</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Address</th>
            <th style="width: 75px;">Amount</th>
            <th>Status</th>
            <th>Products</th>
        </tr>
    </thead>
    <tbody>
<?php 
    $count = 0;
    foreach($result as $order){
        ?> 
            <tr class="<?php echo ++$count%2 ? "" : "alternate"; ?>">
                <td><a href="/wp-admin/post.php?post=<?php echo $order["order_id"]; ?>&action=edit"><?php echo $order["order_id"]; ?></a></td> 
                <td>
                    <?php 
                        $date=date_create($order["post_date"]);
                        echo date_format($date,"F, d Y");
                    ?>
                </td> 
                <td><?php echo $order["_billing_first_name"]; ?></td> 
                <td><?php echo $order["_billing_last_name"]; ?></td> 
                <td><a href="mailto:<?php echo $order["billing_email"]; ?>"><?php echo $order["billing_email"]; ?></a></td> 
                <td>
                    <?php echo $order["_billing_address_1"]; ?> <?php echo $order["_billing_address_2"]; ?><br />
                    <?php echo $order["_billing_city"]; ?>, <?php echo $order["_billing_state"]; ?><br />
                    <?php echo $order["_billing_postcode"]; ?><br />                
                </td>
                <td><?php echo "$ ".number_format(($order["order_total"] + $order["order_tax"]), 2); ?></td> 
                <td><?php echo $order["post_status"]; ?></td>
                <td><?php echo $order["order_items"]; ?></td>
            </tr>
        <?php
    }
?>
    </tbody>
</table>

<?php }