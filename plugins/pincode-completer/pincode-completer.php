<?php
/**
* Plugin Name: Pincode Completer
* Plugin URI: https://www.pincodecompleter.com/
* Description: Pincode completer helps in filling city, state, country from pincode.
* Version: 0.9
* Author: Arpit Srivastava
* Author URI: http://twitter.com/arpitswall
**/

if (!function_exists('write_log')) {
    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

function pincode_completer_load()
{
    $pin_array;
    $currenttime = date("Y-m-d h:m:sa");
    if (($handle = fopen(__DIR__ . "/pin.csv", "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $pin = $data[0];
            $district = $data[1];
            $state = $data[2];
            $pin_array[$pin] = ["state"=>$state, "district"=>$district];
        }
        fclose($handle);
    }
    return $pin_array;
}


function get_pins()
{
    static $pin_array;

    // Function has already run
    if ($pin_array !== null) {
        return $pin_array;
    }

    // Lot of work here to determine $result
    $pin_array = pincode_completer_load();

    return $pin_array;
}

register_activation_hook(__FILE__, 'get_pins');

// Our hooked in function - $address_fields is passed via the filter!


function pw_load_scripts()
{
    write_log("filepathakdn;fjd;jfa;ljdflla--------------------------------------");
    write_log(plugin_dir_path(__FILE__) . 'pincode.js');
    // Register the script
    wp_register_script('checkoutjs_load_handle', plugin_dir_url(__FILE__) . 'pincode.js');
 
    wp_localize_script('checkoutjs_load_handle', 'php_vars', get_pins());
 
    // Enqueued script with localized data.
    wp_enqueue_script('checkoutjs_load_handle');
}
add_action('wp_enqueue_scripts', 'pw_load_scripts');


function custom_override_default_address_fields($address_fields)
{
    $address_fields['address_1']['label'] = 'Full Address';
    $address_fields['address_1']['type'] = 'textarea';
    $address_fields['postcode']['label'] = 'Pincode';
    $address_fields['postcode']['priority'] = 6;
    $address_fields['city']['label'] = 'District/City';
    $address_fields['city']['priority'] = 7;
    $address_fields['state']['label'] = 'State';
    $address_fields['state']['priority'] = 8;
    return $address_fields;
}


// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields($fields)
{

    // Shipping fields
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_phone']);
    unset($fields['shipping']['shipping_state']);
    unset($fields['shipping']['shipping_first_name']);
    unset($fields['shipping']['shipping_last_name']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_address_2']);
    unset($fields['shipping']['shipping_city']);
    unset($fields['shipping']['shipping_postcode']);

    // Billing fields
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_address_2']);

    unset($fields['order']['order_comments']);

    $fields['billing']['billing_first_name'] = array(
        'label'=>'Your Name',
        'class' => array('form-row-wide'),
        'required' => true,
        'priority' => 3,
    );

    $fields['billing']['billing_phone']['priority'] = 4;
    $fields['billing']['billing_email']['priority'] = 5;

    return $fields;
}


function change_default_checkout_country($country)
{
    // If the user already exists, don't override country
    if (WC()->customer->get_is_paying_customer()) {
        return $country;
    }

    return 'IN'; // Override default to Germany (an example)
}

// Hook in
add_filter('woocommerce_default_address_fields', 'custom_override_default_address_fields');
add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
add_filter('default_checkout_billing_country', 'change_default_checkout_country', 10, 1);

// add_filter('woocommerce_checkout_create_order', 'pincode_to_city_state', 99999, 2);
// function pincode_to_city_state($order, $data)
// {
//     $currenttime = date("Y-m-d h:m:sa");
//     $pin_array = get_pins();

//     $billing_postcode   = $data['billing_postcode'];
//     $district = $pin_array[$billing_postcode]["district"];
//     $state = $pin_array[$billing_postcode]["state"];

//     $order->set_billing_city($district);
//     $order->set_billing_state($state);

//     return $order;
// }
