<?php

global $wpdb;

$results = $wpdb->get_results("
    SELECT p.ID, p.post_title FROM {$wpdb->prefix}posts p
    INNER JOIN {$wpdb->prefix}postmeta pm
    ON p.ID = pm.post_id
    WHERE p.post_type = 'product'
    AND pm.meta_key = '_price'
    AND pm.meta_value > 100
");

// SELECT * FROM wp_users WHERE email LIKE '%@gmail.com'
// SELECT SUM(meta_value) as total_sales FROM wp_postmeta WHERE meta_key = 'order_total' AND meta_value >100
//SELECT post_author, COUNT(*) as total_posts FROM wp_posts WHERE post_type = 'post' GROUP BY post_author

?>