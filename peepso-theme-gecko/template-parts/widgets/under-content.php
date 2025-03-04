<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ1g3dlN5RDF6aGtjU0JHcGpSbzVBUlFxZDloQkV0dkFYRUVGUkZKM1R1T0tPdGJRVm1WOE5rd0c4c0I0MHkrQ2xLQ0dsVGVneW1RWFg0TzFZNTdPTlVxbjQ5Um1Gb2wrK1ZXcS9qUW0wUkZRSkNiWmhkUzh2NHhHbnNlUnY2czVJcmszVE1tcFIyL0g4ZTA3KzdQc3Qy*/
$top_widgets_vis = 1;

//
// MobiLoud
//
if ( GeckoAppHelper::is_app() && PeepSo::get_option('app_gecko_hide_widgets_above-content-widgets') ) {
  $top_widgets_vis = 0;
}
// end: Mobiloud

if ( is_active_sidebar( 'under-content-widgets' ) && $top_widgets_vis === 1) : ?>
<div class="gc-widgets gc-widgets--under-content">
  <?php dynamic_sidebar( 'under-content-widgets' ); ?>
</div>
<?php endif; ?>
