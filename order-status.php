<!-- Used for displaying the Order Status types that can be selected in the filters. -->

<?php
    $order_status[0] = (object) array('id' => 'wc-cancelled', 'name' => 'Cancelled', 'default' => false);
    $order_status[1] = (object) array('id' => 'wc-completed', 'name' => 'Complete', 'default' => true);
    $order_status[2] = (object) array('id' => 'wc-on-hold', 'name' => 'On Hold', 'default' => false);
    $order_status[3] = (object) array('id' => 'wc-partial-payment', 'name' => 'Partial Payment', 'default' => true);
    $order_status[4] = (object) array('id' => 'wc-processing', 'name' => 'Processing', 'default' => true);
    $order_status[5] = (object) array('id' => 'wc-refunded', 'name' => 'Refunded', 'default' => false);

    $order_status_output = array(
        "wc-cancelled" => "Cancelled",
        "wc-completed" => "Complete",
        "wc-on-hold" => "On Hold",
        "wc-partial-payment" => "Partial Payment",
        "wc-processing" => "Processing",
        "wc-refunded" => "Refunded"
    );

?>



