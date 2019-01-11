<?php

    $prefix = $wpdb->prefix; // Database Prefix
    $today = date("Y-m-d"); // Todays Date

    require_once('excludes.php'); // Excluded products
    require_once('order-status.php'); // Available Order Status

    // Get Products
    $query = "
        SELECT DISTINCT order_item_name as name
        FROM {$prefix}woocommerce_order_items
        WHERE TRIM(order_item_name) NOT IN ({$excluded_products_list})
        AND order_item_type = 'line_item'
    ";

    $products = $wpdb->get_results($query, "ARRAY_A");

    // Get Countries
    $query = "
        select DISTINCT meta_value as code
        FROM {$prefix}postmeta
        WHERE meta_key = '_billing_country'
    ";

    $countries = $wpdb->get_results($query, "ARRAY_A");

?>

<div class="admin-wrapper">
    <h1>Orders by Product</h1>

    <form method="post">
        <p>
            <strong>Select the products to view:</strong> <br />
            <small>* hold down ctrl or shift to select multiple</small>
        </p>

        <p>
            <select class="product-select" id="product" name="product[]" multiple size="8">
                <?php
                    $count = 0;
                    foreach ($products as $product) {
                        $name = $product["name"];

                        ?>
                            <option
                                value="<?php echo $name; ?>"
                                <?php
                                    if(
                                        isset($_POST['product']) &&
                                        in_array($name, $_POST["product"])
                                    ) echo "selected=selected";
                                ?> >
                                <?php echo $name; ?>
                            </option>
                <?php } ?>
            </select>
        </p>

        <!-- FILTERS -->
        <h2 class="filters__heading">Filters</h2>

        <div class="filters">

            <!-- DATES -->
            <div class="filter-group filters__date">
                <strong>Order Date:</strong><br />
                <p>
                    <label for="start_date">Start Date</label>
                    <input
                        type="date"
                        name="start_date"
                        class="datepicker"
                        placeholder="Start Date"
                        value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>"
                    />
                </p>
                <p>
                    <label for="end_date">End Date</label>
                    <input
                        type="date"
                        name="end_date"
                        class="datepicker"
                        placeholder="End Date"
                        value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : $today; ?>"
                    />
                </p>
            </div>

            <!-- STATUS -->
            <div class="filter-group filters__status">
                <strong>Order Status:</strong><br />
                <select class="status-select" id="status" name="status[]" multiple size="7">
                    <?php
                        foreach ($order_status as $status) { ?>
                            <option
                                value="<?php echo $status->id; ?>"
                                <?php
                                    if(
                                        $status->default ||
                                        (isset($_POST['status']) &&
                                        in_array($status->id, $_POST["status"]))
                                    ) echo "selected=selected";
                                ?> >
                                <?php echo $status->name; ?>
                            </option>
                        <?php } ?>
                </select>
            </div>

            <!-- COUNTRY -->
            <div class="filter-group filters__country">
                <strong>Country:</strong><br />
                <select class="status-select" id="country" name="country[]" multiple size="7">
                    <?php
                        $count = 0;
                        foreach ($countries as $country) {
                            $id = $country["code"];

                            ?>
                                <option
                                    value="<?php echo $id; ?>"
                                    <?php
                                        if(
                                            (
                                                sizeof($_POST) === 0 &&
                                                ($id === 'CA' || $id === 'US')
                                            ) || (
                                                isset($_POST['country']) &&
                                                in_array($id, $_POST["country"])
                                            )
                                        ) echo "selected=selected";
                                    ?> >
                                    <?php echo $id; ?>
                                </option>
                    <?php } ?>
                </select>
            </div>

        </div>

        <div class="submit admin-buttons">
            <input type="submit" value="View Orders" class="button-primary" name="Submit">
        </div>
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

/*
    IF THE FORM HAS BEEN SUBMITTED
*/
if(sizeof($_POST) !== 0 && sizeof($_POST['product']) > 0){

    $products_str = single_quote_list($_POST['product']);
    $status_str = single_quote_list($_POST['status']);

    // Dates
    $start_date = date("Y-m-d", strtotime($_POST['start_date']));
    $end_date = date("Y-m-d", strtotime($_POST['end_date']));
    $date_query = "
        AND p.post_date BETWEEN '{$start_date}' AND '{$end_date}'
    ";

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
        max( CASE WHEN pm.meta_key = '_billing_country' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_country,
        max( CASE WHEN pm.meta_key = '_shipping_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_first_name,
        max( CASE WHEN pm.meta_key = '_shipping_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_last_name,
        max( CASE WHEN pm.meta_key = '_shipping_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_1,
        max( CASE WHEN pm.meta_key = '_shipping_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_address_2,
        max( CASE WHEN pm.meta_key = '_shipping_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_city,
        max( CASE WHEN pm.meta_key = '_shipping_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_state,
        max( CASE WHEN pm.meta_key = '_shipping_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_postcode,
        max( CASE WHEN pm.meta_key = '_shipping_country' and p.ID = pm.post_id THEN pm.meta_value END ) as _shipping_country,
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
        order_item_name IN ({$products_str}) and
        post_status IN ({$status_str})
        {$date_query}
    group by
        p.ID
    ORDER BY post_date
    ";

    $result = $wpdb->get_results($query, "ARRAY_A");

    /* Filter results for country */
    $filtered_result = [];
    foreach($result as $order){
        if(in_array(TRIM($order['_billing_country']), $_POST['country'])){
            array_push($filtered_result, $order);
        }
    }

    ?>

    <h2>Showing <?php echo sizeof($filtered_result); ?> Order<?php echo sizeof($filtered_result) === 1 ? '' : 's'; ?>:</h2>
    <p><a href="<?php echo plugins_url("bm-woo-reports") . "/output/orders-by-product.csv"; ?>" target="_blank">Export CSV</a></p>

    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 60px;">Order #</th>
                <th style="width: 120px;">Order Date</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Country</th>
                <th style="width: 75px;">Amount</th>
                <th>Status</th>
                <th>Products</th>
            </tr>
        </thead>
        <tbody>
    <?php
        $count = 0;
        foreach($filtered_result as $order){
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
                    <td><?php echo $order["_billing_country"]; ?></td>
                    <td><?php echo "$ ".number_format(($order["order_total"] + $order["order_tax"]), 2); ?></td>
                    <td><?php echo $order_status_output[$order["post_status"]]; ?></td>
                    <td><?php echo $order["order_items"]; ?></td>
                </tr>
            <?php
        }
    ?>
        </tbody>
    </table>
</div>

<?php

    /* Output the data to CSV */
    $dir = plugin_dir_path( __FILE__ );
    $file_path = $dir . 'output/orders-by-product.csv';

    $fp = fopen($file_path, 'w');

    foreach ($filtered_result as $order) {
        fputcsv($fp, $order);
    }

    fclose($fp);

}

