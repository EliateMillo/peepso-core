<div class="ps-photos" data-id="peepso-photos-shortcode">
    <script type="text/template" data-id="item-template">
        <div class="ps-photos__list-item" data-id="item">
            <div class="ps-photos__list-item-inner">
                <a href="#" data-id="{{= data.act_id }}" data-pho-id="{{= data.pho_id }}">
                    <img src="{{- data.pho_thumbs.m_s }}" title="{{- data.pho_orig_name }}" alt="" />
                </a>
            </div>
        </div>
    </script>
    <script type="text/template" data-id="block-editor-load-notice">
        <div><?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVczdXJBVlZra1NzeVNkSEF3eVRVTGhDWlNKdERSeFBJV05VRlIvdHNyZmxuTU1LMFlTYWpNNGdwR1VDY3M2UllVWmQ3NFdDUFRESThnMnJaclVkMW12eFR2QTdqUTFaUUViRm1sQ3RZdEEyVnJuL2RuT1BUelNoMXYramNEWkpaMUhoZWwxR1YrNU5OeFpIUS9LaWJz*/ echo __('Only a few photos are loaded here. To see all the photos, please open the page directly.', 'picso'); ?></div>
    </script>
    <div class="ps-photos__list ps-photos__list--photos ps-photos__list--small ps-js-photos-standalone" data-id="list"></div>
    <div class="ps-js-photos-standalone-triggerscroll">
        <img class="post-ajax-loader ps-js-photos-standalone-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
    </div>
</div>