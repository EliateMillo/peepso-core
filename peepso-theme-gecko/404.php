<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaXFNcXVkYTNHclcyZThoajRrT3JMTjU3bnVZWkdjZ1FvVWdnYmRIM3dGRmNWMFU1bnIwYzJicGFsTUtUczJHditYVVVmemNaZlpFK3JocUo2a2U3eEZGRWRRZUpJQ0tza2p5R0R3RE1Ybjh1anAyanVQZHJRMjRINzBuY3hUcXVJPQ==*/
get_header();

?>

<div id="main" class="main main--404">
  <div class="content content--404">
    <div class="e404">
      <header class="e404__title">
        <h1><?php _e( '404', 'peepso-theme-gecko' ); ?></h1>
      </header>
      <div class="e404__content">
        <h3><?php _e( 'It looks like nothing was found at this location.', 'peepso-theme-gecko' ); ?></h3>
      </div>
      <div class="e404__button">
      <a class="button" href="<?php echo esc_url(home_url()); ?>"><i class="gc-icon-angle-left"></i> <?php _e( 'Back to home', 'peepso-theme-gecko'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php

get_footer();
