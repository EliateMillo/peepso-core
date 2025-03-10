<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0JERGljdlpjUGhVd3dSNHI2eGZCcC85OUMwNDNOOXJKZlJLcTFib1AwM2VSekx4a0hMdW1GcEVkK2xrV0dJUzQxL2p4NXV1VDFlVVhPYmJ1WTlQSzMyWkNwUm4wM0Q0YWlmY1RteTlDTWVkY0x0R3V1eDRZRXRJTDJRUXQxNGpFPQ==*/
$gecko_settings = GeckoConfigSettings::get_instance();
$bottom_widgets_vis = 1;

//
// MobiLoud
//
if (GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_bottom-widgets')) {
  $bottom_widgets_vis = 0;
}

if ( GeckoAppHelper::is_app() && is_active_sidebar( 'mobi-bottom-widgets' ) && $gecko_settings->get_option( 'opt_app_widget_positions', 0 ) ) {
echo '<div class="gc-widgets gc-widgets--app gc-widgets--app-bottom">
    <div class="gc-widgets__inner">
      <div class="gc-widgets__grid">';
        dynamic_sidebar( 'mobi-bottom-widgets' );
echo '</div>
    </div>
</div>';
}
// end: Mobiloud
?>

<?php
if ( is_active_sidebar( 'bottom-widgets' ) && $bottom_widgets_vis === 1) : ?>
<div class="gc-widgets gc-widgets--bottom">
    <div class="gc-widgets__inner">
      <div class="gc-widgets__grid">
        <?php dynamic_sidebar( 'bottom-widgets' ); ?>
      </div>
    </div>
</div>
<?php endif; ?>
