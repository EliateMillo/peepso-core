<div class="ps-chat__message ps-chat__message--me ps-conversation-item my-message-files-{{= data.id }}">
  <div class="ps-chat__message-body ps-conversation-body">
    <div class="ps-chat__message-content ps-conversation-content"></div>
    <div class="ps-chat__message-attachments ps-conversation-attachment">
      <div class="ps-media__attachment ps-media__attachment--files">
        <div class="ps-file-item-wrapper ps-js-file">
          <div class="ps-file-item-content">
            <div class="ps-file-item-content__icon"></div>
            <div class="ps-file-item-content__details">
              <div class="ps-file-item-content__name" title="{{= data.name }}">{{= data.name }}</div>
              <div class="ps-file-item-content__size">{{= data.size }}</div>
            </div>
          </div>
          <div style="padding:0 20px">
            <img class="ps-loading__image" src="<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaEVnQzQ4TkxTdnhZajRVeDAxZEF5clhIWkpqL05IcFhPNWd4WE85YTlqcTg3cWVOc1BFRVBEUldJYUVRdWpGN1BnRTVRcU9mYUdGVGhTdUozSHBkbk9NMjIzTUhkWC9yV0hTeE5GeGt4RlVMaWpFckgwNXgzS0dzYlI3ZTRmVmd1d1M2bGRPbGxReEM1Nm4xa3d4NnRy*/ echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
          </div>
        </div>
      </div>
    </div>
    <div class="ps-chat__message-time ps-conversation-time">
      <i class="gcir gci-check-circle"></i>
      <span><?php echo __('just now', 'msgso'); ?></span>
    </div>
  </div>
</div>
