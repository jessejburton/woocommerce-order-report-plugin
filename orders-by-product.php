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

        <div class="product filter-group filter-group-full">
            <p><strong>Select the products to view:</strong></p>

            <select class="product__select" id="product" name="product[]" multiple size="8">
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

            <small>* hold down ctrl or shift to select multiple</small>
        </div>

        <!-- FILTERS -->
        <div class="filters">

            <h2 class="filters__heading">Filters</h2>

            <!-- DATES -->
            <div class="filter-group filters-date">
                <p><strong>Order Date:</strong></p>
                <p>
                    <label class="filters-date__label" for="start_date">Start Date</label><br />
                    <input
                        type="date"
                        name="start_date"
                        class="datepicker"
                        placeholder="Start Date"
                        value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>"
                    />
                </p>
                <p>
                    <label class="filters-date__label" for="end_date">End Date</label><br />
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
                <p><strong>Order Status:</strong></p>
                <select class="filter-status__select" id="status" name="status[]" multiple size="7">
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
                <p><strong>Country:</strong></p>
                <select class="filter-country__select" id="country" name="country[]" multiple size="7">
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
                                                sizeof($_POST) === 0 // Select all by default
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
            <input type="submit" id="submit" value="View Orders" class="button-primary" name="Submit" disabled>
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
    <p><a href="<?php echo plugins_url("bm-woo-reports") . "/output/orders-by-product.csv?" . uniqid(); ?>" target="_blank">Export CSV</a></p>

    <table class="report-table widefat fixed" cellspacing="0">
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

$orders = [];
foreach($filtered_result as $order_result){
  // Get an instance of the WC_Order object
  $order = wc_get_order($order_result["order_id"]);

  $order_details = [];
  array_push($order_details, $order_result["order_id"]);
  array_push($order_details, $order->get_date_completed());
  array_push($order_details, $order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
  array_push($order_details, $order->get_billing_email());
  array_push($order_details, $order->get_billing_country());
  array_push($order_details, $order->get_status());

  // Iterating through each WC_Order_Item_Product objects
  foreach ($order->get_items() as $item_key => $item ):

      ## Using WC_Order_Item methods ##

      // Item ID is directly accessible from the $item_key in the foreach loop or
      $item_id = $item->get_id();

      ## Using WC_Order_Item_Product methods ##

      $product      = $item->get_product(); // Get the WC_Product object

      $product_id   = $item->get_product_id(); // the Product id
      $variation_id = $item->get_variation_id(); // the Variation id

      $item_type    = $item->get_type(); // Type of the order item ("line_item")

      $item_name    = $item->get_name(); // Name of the product
      $quantity     = $item->get_quantity();
      $tax_class    = $item->get_tax_class();
      $line_subtotal     = $item->get_subtotal(); // Line subtotal (non discounted)
      $line_subtotal_tax = $item->get_subtotal_tax(); // Line subtotal tax (non discounted)
      $line_total        = $item->get_total(); // Line total (discounted)
      $line_total_tax    = $item->get_total_tax(); // Line total tax (discounted)

      ## Access Order Items data properties (in an array of values) ##
      $item_data    = $item->get_data();

      $product_name = $item_data['name'];
      $product_id   = $item_data['product_id'];
/*       $variation_id = $item_data['variation_id'];
      $quantity     = $item_data['quantity'];
      $tax_class    = $item_data['tax_class'];
      $line_subtotal     = $item_data['subtotal'];
      $line_subtotal_tax = $item_data['subtotal_tax'];
      $line_total        = $item_data['total'];
      $line_total_tax    = $item_data['total_tax']; */

      // Get data from The WC_product object using methods (examples)
      $product        = $item->get_product(); // Get the WC_Product object

      $product_type   = $product->get_type();
      $product_sku    = $product->get_sku();
      $product_price  = $product->get_price();
      $stock_quantity = $product->get_stock_quantity();

      array_push($order_details, $product_name);
      array_push($order_details, $quantity);
      array_push($order_details, number_format($line_total, 2, '.', ''));
      array_push($order_details, number_format($line_total_tax, 2, '.', ''));
      array_push($order_details, number_format(($line_total + $line_total_tax), 2, '.', ''));

  endforeach;

  array_push($orders, $order_details);

}
?>

<?php

    $headings = [];
    array_push($headings, "Order ID");
    array_push($headings, "Date");
    array_push($headings, "Name");
    array_push($headings, "Email");
    array_push($headings, "Country");
    array_push($headings, "Status");
    array_push($headings, "Product");
    array_push($headings, "Quantity");
    array_push($headings, "Sub Total");
    array_push($headings, "Tax");
    array_push($headings, "Total");

    /* Output the data to CSV */
    $dir = plugin_dir_path( __FILE__ );
    $file_path = $dir . 'output/orders-by-product.csv';

    $fp = fopen($file_path, 'w');

    // Add the headings
    fputcsv($fp, $headings);

    // Add the rows
    foreach ($orders as $order) {
      fputcsv($fp, $order);
    }

    fclose($fp);

}

