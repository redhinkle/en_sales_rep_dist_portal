<?php

/*
Template Name: Sales Rep Single Distributor Order Template
*/

get_header();

$is_page_builder_used = et_pb_is_pagebuilder_used(get_the_ID());

?>
<style>
    header{
        display: none;
    }
    /* Accordion styles */
.accordion {
    background-color: #eee;
    color: #222;
    cursor: pointer;
    padding: 18px;
    width: 100%;
    text-align: left;
    border: none;
    outline: none;
    transition: 0.4s;
}

.active, .accordion:hover {
    background-color: #e02b20;
    color: #ffffff;
}

.panel {
    padding: 0 18px;
    display: none;
    background-color: white;
    overflow: hidden;
}


</style>
<div id="main-content">

    <?php if (!$is_page_builder_used) : ?>

        <div class="container">
            <div id="content-area" class="clearfix">
                <div id="left-area">

    <?php endif; ?>

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if (!$is_page_builder_used) : ?>

                <!-- <h1 class="entry-title main_title"><?php //the_title(); ?></h1> -->
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
                            // echo '<h2>Distributor: </h2><p>' . $distributor->display_name . '</p><br>';

                            // Get the distributor's order history
                            $order_history = get_distributor_order_history($distributor_id);

                            // Display the order history
                            $total_price = 0;
                            foreach ($order_history as $order_id => $order_data) {
                            // $total_price += $order_data['items']['product_price'];
                                foreach($order_data['items'] as $item){
                                    $total_price = $item['total_price'];
                                }
                                ?>
                                <div class="distributor_order_card" style="margin: 10px auto; background-color: #707070; border-radius: 15px; text-align: left; padding: 10px; width: 90%;">
                                    <button class="accordion"><?php echo '<strong>Order Date:</strong> '. $order_data['date_created'] . '<br><strong>Order ID:</strong> ' . $order_id; ?><br><?php echo '<strong>Total Price:</strong> $'. $total_price;?><br><?php echo '<strong>Order Status:</strong> '. $order_data['status'];?></button>
                                    <div class="panel">
                                        <br>
                                        <!-- Order details -->
                                        <h2>Order Details:</h2><br>
                                        <h3>Name:</h3>
                                        <p><?php echo $order_data['first_name'] . ' ' . $order_data['last_name']; ?></p><hr/>
                                        <h3>Email:</h3>
                                        <p><?php echo $order_data['email']; ?></p><hr/>
                                        <h3>City:</h3>
                                        <p><?php echo $order_data['city']; ?></p><hr/>
                                        <h3>Date Created:</h3>
                                        <p><?php echo $order_data['date_created']; ?></p><hr/>
                                        <h3>Date Completed:</h3>
                                        <?php if(isset($order_data['date_completed'])){

                                            echo '<p>' . $order_data['date_completed'] . '</p><hr/>';

                                        }else{

                                            echo "<p>Not Complete</p><hr/>"; 
                                        }
                                            ?>

                                        <br><br>
                                        <!-- Order items -->
                                        <h2>Order Items:</h2><br>
                                        <?php foreach ($order_data['items'] as $item) : 
                                            ?>
                                            <div class="order-item">
                                                <h3>Order Item:</h3>
                                                <p><?php echo $item['order_item_name']; ?></p><hr/>
                                                <h3>SKU:</h3>
                                                <p><?php echo $item['sku']; ?></p><hr/>
                                                <h3>Product Price:</h3>
                                                <p>$<?php echo $item['product_price']; ?></p><hr/>
                                                <!-- Total pricing -->
                                                <br>
                                            </div>
                                        <?php endforeach; ?>
                                        <h3>Total Price:</h3>
                                                <p>$<?php echo $total_price; ?></p>
                                                <br>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                        echo '<h2>Invalid distributor ID.</h2>';
                        }
                        } else {
                        echo '<h2>No distributor ID specified.</h2>';
                        }
                    
                } else {
                    // Redirect users who are not sales reps to another page or display an error message
                    wp_redirect(home_url());
                    exit;
                }
                ?>

                <a href="https://enerlitesstg.wpengine.com/list-of-distributors/">Return to Distributor List</a>
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
<script>
    // Accordion functionality
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.display === "block") {
            panel.style.display = "none";
        } else {
            panel.style.display = "block";
        }
    });
}
</script>
<?php

// get_footer();