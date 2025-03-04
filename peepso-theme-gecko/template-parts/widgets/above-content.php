<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ3NXdGN1Q1N4cEdyQzI1NjR6c2RTMnJJTmZuVUplMXNKNWNjZ2JFODRxNWlTdlh1czFyYUJLcnlXejhPbnN5b3RIOXVNSThWK2FKUVR1MWRoM3BBMjRFRCtCTE9PNmo1RkdFZVltdmhIdHBKWUlEbnFKYU5UYVF4YlhZQURxK2xSemt5bW9weGxUdDJnWWZjdW9LcFZ4*/
$top_widgets_vis = 1;

//
// MobiLoud
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_above-content-widgets') ) {
  $top_widgets_vis = 0;
}
// end: Mobiloud

if ( is_active_sidebar( 'above-content-widgets' ) && $top_widgets_vis === 1) : ?>
<div class="gc-widgets gc-widgets--above-content">
  <?php dynamic_sidebar( 'above-content-widgets' ); ?>
</div>
<?php endif; ?>
