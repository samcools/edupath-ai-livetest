<?php get_header(); ?>
<main style="max-width:1100px;margin:4rem auto;padding:1rem"><h1><?php bloginfo('name'); ?></h1><?php if(have_posts()):while(have_posts()):the_post(); ?><article><?php the_title('<h2>','</h2>'); the_content(); ?></article><?php endwhile;endif; ?></main>
<?php get_footer(); ?>
