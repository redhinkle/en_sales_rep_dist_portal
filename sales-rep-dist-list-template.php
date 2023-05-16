<?php

/*
Template Name: Sales Rep Page Template
*/
get_header();

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );

?>

<div id="main-content">

<?php if ( ! $is_page_builder_used ) : ?>

	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">

<?php endif; ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( ! $is_page_builder_used ) : ?>

					<h1 class="entry-title main_title"><?php the_title(); ?></h1>
				<?php
					$thumb = '';

					$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

					$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
					$classtext = 'et_featured_image';
					$titletext = get_the_title();
					$alttext = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );
					$thumbnail = get_thumbnail( $width, $height, $classtext, $alttext, $titletext, false, 'Blogimage' );
					$thumb = $thumbnail["thumb"];

					if ( 'on' === et_get_option( 'divi_page_thumbnails', 'false' ) && '' !== $thumb )
						print_thumbnail( $thumb, $thumbnail["use_timthumb"], $alttext, $width, $height );
				?>

				<?php endif; ?>

					<div class="entry-content">
					<?php
						the_content();


// Check if the current user is a sales rep
if (in_array('sales_rep', wp_get_current_user()->roles)) {
    // Get the distributor IDs associated with the current sales rep
    $distributor_ids = get_distributor_ids(get_current_user_id());

    // Query the distributors based on their IDs
    $distributors = get_users(array(
        'role' => 'distributor',
        'include' => $distributor_ids,
    ));

    // Output the distributor data
    foreach ($distributors as $distributor) {
        // Display distributor information

        echo 'Distributor: ' . $distributor->display_name . '<br>';

        echo '<br>';
    }
} else {
    // Redirect users who are not sales reps to another page or display an error message
    wp_redirect(home_url());
    exit;
}

if ( ! $is_page_builder_used )
wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
?>
</div>

<?php
if ( ! $is_page_builder_used && comments_open() && 'on' === et_get_option( 'divi_show_pagescomments', 'false' ) ) comments_template( '', true );
?>

</article>

<?php endwhile; ?>

<?php if ( ! $is_page_builder_used ) : ?>

</div>

<?php get_sidebar(); ?>
</div>
</div>

<?php endif; ?>

</div>

<?php

get_footer();

