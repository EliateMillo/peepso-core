<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3NJTzJtUUdib0pZOGwrZTRCT3E2b05uUXVPTWt4TFM0bndGNllMUTZUeFl5c1dDTUJGbVpVd0twL3AvMmx3d0ZhMFdvQnFFTVhaTGVpOEtOenRlYTQ4bHJkdVU0M1hzd2VUOFRXcFF6b2JqSnM3ZUF4MkJoM0JBWFpnUDlyYWRnWXhTRWZ3NDBzZVBYemZUenZDWlZN*/
/*
  Template Name: Builder friendly
  Template Post Type: page, download
*/

get_header();

?>

<?php if ( have_posts() ) : ?>
  <?php
    // Start the loop.
    while ( have_posts() ) : the_post();

      // Load content
      the_content();

    // End the loop.
    endwhile;
  endif;
?>

<?php

get_footer();
