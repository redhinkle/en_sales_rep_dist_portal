
// Add Sales Rep and Distributor roles

function add_custom_roles() {
    add_role(
        'sales_rep',
        'Sales Rep',
        array(
            // Add capabilities for the Sales Rep role
            'read'              => false,
            'edit_posts'        => true,
            'publish_posts'     => true,
            // Add additional capabilities as needed
        )
    );

    add_role(
        'distributor',
        'Distributor',
        array(
            // Add capabilities for the Distributor role
            'read'              => false,
            'edit_posts'        => false,
            'publish_posts'     => false,
            // Add additional capabilities as needed
        )
    );
}
add_action('init', 'add_custom_roles');

// Function to save parent ID as user meta for child users
function save_parent_id($user_id, $role) {
    if ($role === 'distributor') {
        $parent_id = get_current_user_id(); // Assuming the current user is the parent
        update_user_meta($user_id, 'parent_id', $parent_id);
    }
}
add_action('user_register', 'save_parent_id', 10, 2);
add_action('profile_update', 'save_parent_id', 10, 2);

// Function to save distributor IDs as user meta for sales rep users
function save_distributor_ids($user_id, $role) {
    if ($role === 'sales_rep') {
        // Get the distributor IDs associated with the sales rep
        $distributor_ids = get_users(array(
            'role'         => 'distributor',
            'meta_key'     => 'parent_id',
            'meta_value'   => $user_id,
            'fields'       => 'ids',
        ));

        // Save the distributor IDs as user meta for the sales rep
        update_user_meta($user_id, 'distributor_ids', $distributor_ids);
    }
}
add_action('user_register', 'save_distributor_ids', 10, 2);
add_action('profile_update', 'save_distributor_ids', 10, 2);


// Function to retrieve distributor IDs associated with a sales rep
function get_distributor_ids($sales_rep_id) {
    return get_user_meta($sales_rep_id, 'distributor_ids', true);
}

// Add custom meta box to user edit screen for sales rep role
function add_custom_user_meta_box() {
    $sales_rep_role = 'sales_rep';

    if (current_user_can('edit_users')) {
        add_action('edit_user_profile', 'render_sales_rep_distributors_meta_box');
    }
}
add_action('admin_init', 'add_custom_user_meta_box');


// Render content of the sales rep distributors meta box
function render_sales_rep_distributors_meta_box($user) {
    // Check if the user is a sales rep
    $is_sales_rep = false;
    if (is_array($user->roles) && in_array('sales_rep', $user->roles)) {
        $is_sales_rep = true;
    } else {
        $old_roles = get_user_meta($user->ID, 'wp_capabilities', true);
        if (is_array($old_roles) && isset($old_roles['sales_rep']) && $old_roles['sales_rep'] === true) {
            $is_sales_rep = true;
        }
    }

    if ($is_sales_rep) {
        // Retrieve all distributor users
        $distributors = get_users(array(
            'role' => 'distributor',
        ));

        // Retrieve the selected distributor IDs from user meta
        $distributor_ids = get_user_meta($user->ID, 'distributor_ids', true);
        $distributor_ids = is_array($distributor_ids) ? $distributor_ids : array(); // Ensure $distributor_ids is an array
        
        // Output the select element for assigning distributors
        ?>

<h2>Enerlites Sales Portal</h2>
<div>
    <label><strong>Assign Distributors:</strong></label>
    <br>
    <?php foreach ($distributors as $distributor) : ?>
        <label>
            <input type="checkbox" name="assigned_distributors[]" value="<?php echo $distributor->ID; ?>" <?php checked(in_array($distributor->ID, $distributor_ids)); ?>>
            <?php echo $distributor->display_name; ?>
        </label>
        <br>
    <?php endforeach; ?>
    <br>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user->ID; ?>" />
</div>

        <?php
    }
}

function save_sales_rep_distributors_meta($user_id) {
    // Check if the current user can edit the user profile
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }

    // Ensure the assigned_distributors data is received
    if (isset($_POST['assigned_distributors']) && is_array($_POST['assigned_distributors'])) {
        // Sanitize the received distributor IDs
        $assigned_distributors = array_map('intval', $_POST['assigned_distributors']);

        // Update the distributor_ids user meta with the selected distributor IDs
        update_user_meta($user_id, 'distributor_ids', $assigned_distributors);
    } else {
        // If no distributor IDs were selected, delete the distributor_ids user meta
        delete_user_meta($user_id, 'distributor_ids');
    }
}
add_action('personal_options_update', 'save_sales_rep_distributors_meta');
add_action('edit_user_profile_update', 'save_sales_rep_distributors_meta');

/**
 * Retrieve the order history for a distributor from the database.
 *
 * @param int $distributor_id The distributor ID.
 * @return array Order history data.
 */
function get_distributor_order_history($distributor_id) {
    global $wpdb;

    $customer_ids = $wpdb->get_col(
        $wpdb->prepare("
            SELECT customer_id
            FROM {$wpdb->prefix}wc_customer_lookup
            WHERE user_id = %d
        ", $distributor_id)
    );

    $order_history = array();

    foreach ($customer_ids as $customer_id) {
		
        $customer_data = $wpdb->get_row(
            $wpdb->prepare("
                SELECT customer_id, first_name, last_name, email, city
                FROM {$wpdb->prefix}wc_customer_lookup
                WHERE customer_id = %d
            ", $customer_id),
            ARRAY_A
        );

        if ($customer_data) {
            $order_data = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT order_id, product_id, variation_id, product_qty
                    FROM {$wpdb->prefix}wc_order_product_lookup
                    WHERE customer_id = %d
                ", $customer_id),
                ARRAY_A
            );
			
		

				foreach ($order_data as $order_item) {
					$order_id = $order_item['order_id'];

					$product_data = $wpdb->get_row(
						$wpdb->prepare("
							SELECT order_item_name
							FROM {$wpdb->prefix}woocommerce_order_items
							WHERE order_id = %d
						", $order_id),
						ARRAY_A
					);

					$sku = $wpdb->get_var(
						$wpdb->prepare("
							SELECT meta_value
							FROM {$wpdb->prefix}wc_product_meta_lookup pm
							INNER JOIN {$wpdb->prefix}postmeta pmf ON pm.product_id = pmf.post_id
							WHERE pm.product_id = %d
							AND pmf.meta_key = '_sku'
						", $order_item['product_id'])
					);

					$product_price = $wpdb->get_var(
						$wpdb->prepare("
							SELECT product_net_revenue
							FROM {$wpdb->prefix}wc_order_product_lookup
							WHERE product_id = %d
							AND order_id = '_regular_price'
						", $order_item['product_id'], $order_item['order_id'])
					);

					$order_history[$order_id]['customer_id'] = $customer_data['customer_id'];
					$order_history[$order_id]['first_name'] = $customer_data['first_name'];
					$order_history[$order_id]['last_name'] = $customer_data['last_name'];
					$order_history[$order_id]['email'] = $customer_data['email'];
					$order_history[$order_id]['city'] = $customer_data['city'];
					$order_history[$order_id]['items'][] = array(
						'order_item_name' => $product_data['order_item_name'],
						'sku' => $sku,
						'product_price' => $product_price,
					);
					
			}
        }
    }

    return $order_history;
}