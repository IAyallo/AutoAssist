<?php
function getServicePrice($service_name) {
    $prices = [
        'Breakdown Assistance' => 7000,
        'Tire Change' => 2500,
        'Battery Jumpstart' => 3000,
        'Fuel Delivery' => 3500,
        'Engine Repair' => 6000,
        'Oil Change' => 2200,
        'Lockout Service' => 4000,
        'Tow Service' => 6500,
        'Other' => 2000
    ];
    foreach ($prices as $key => $val) {
        if (stripos($service_name, $key) !== false) return $val;
    }
    return 2000; // default for unknown
} 