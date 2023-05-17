<?php

/*
Template Name: Sales Rep Single Distributor Order Template
*/

get_header();

$is_page_builder_used = et_pb_is_pagebuilder_used(get_the_ID());

?>

<div id="main-content">

    <?php if (!$is_page_builder_used) : ?>

        <div class="container">
            <div id="content-area" class="clearfix">
                <div id="left-area">

    <?php endif; ?>

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if (!$is_page_builder_used) : ?>

                <h1 class="entry-title main_title"><?php the_title(); ?></h1>
                <?php
                $thumb = '';

                $width = (int) apply_filters('et_pb_index_blog_image_width', 1080);

                $height = (int) apply_filters('et_pb_index_blog_image_height', 675);
                $classtext = 'et_featured_image';
                $titletext = get_the_title();
                $alttext = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true);
                $thumbnail = get_thumbnail($width, $height, $classtext, $alttext, $titletext, false, 'Blogimage');
                $thumb = $thumbnail["thumb"];

                if ('on' === et_get_option('divi_page_thumbnails', 'false') && '' !== $thumb)
                    print_thumbnail($thumb, $thumbnail["use_timthumb"], $alttext, $width, $height);
                ?>

            <?php endif; ?>

            <div class="entry-content">
                <?php the_content(); ?>

                <?php
                // Check if the current user is a sales rep
                if (in_array('sales_rep', wp_get_current_user()->roles)) {
                    // Check if the distributor_id parameter exists in the URL
                    if (isset($_GET['distributor_id'])) {
                        $distributor_id = intval($_GET['distributor_id']);

                        // Get the distributor data based on the distributor ID
                        $distributor = get_user_by('ID', $distributor_id);

                        // Display distributor information
                        if ($distributor) {
                            echo 'Distributor: ' . $distributor->display_name . '<br>';

                            // Get the distributor's order history
                            $order_history = get_distributor_order_history($distributor_id);

                            // Display the order history
                            foreach ($order_history as $order_id => $order_data) {
                                ?>
                                <div class="distributor_order_card" style="background-color: #aaa; border-radius: 25px; text-align: left; padding: 25px; color: white; width: 50%;">
                                <?php
                                echo 'Order ID: ' . $order_id . '<br>';
                                echo 'Customer ID: ' . $order_data['customer_id'] . '<br>';
                                echo 'Name: ' . $order_data['first_name'] . ' ' . $order_data['last_name'] . '<br>';
                                echo 'Email: ' . $order_data['email'] . '<br>';
                                echo 'City: ' . $order_data['city'] . '<br>';

                                // Display the order items
                                foreach ($order_data['items'] as $item) {
                                    echo 'Order Item: ' . $item['order_item_name'] . '<br>';
                                    echo 'SKU: ' . $item['sku'] . '<br>';
                                    echo 'Product Price: ' . $item['product_price'] . '<br>';
                                    echo '<br>';
                                }

                                echo '</div>';
                            }
                        } else {
                        echo 'Invalid distributor ID.';
                        }
                        } else {
                        echo 'No distributor ID specified.';
                        }
                    
                } else {
                    // Redirect users who are not sales reps to another page or display an error message
                    wp_redirect(home_url());
                    exit;
                }
                ?>

            </div>

        </article>

    <?php endwhile; ?>

    <?php if (!$is_page_builder_used) : ?>

        </div>

        <?php get_sidebar(); ?>
    </div>
    </div>

<?php endif; ?>

</div>

<?php

get_footer();