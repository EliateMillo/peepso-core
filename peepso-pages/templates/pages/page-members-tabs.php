<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaVJMY0MwQUhWd3VBd1gveU90cWc3d3B6NG55UmFkd29Bci9vbGNsa1lGMWxqT0lxREd1UWlJdkV3bEJCVGRFSWpGY1dya0JYOENOR0M0WnF4dFRUVnQ0NUR1T2RIZlY0dkdoSktLWUJ0a09SeDBDbUIrM1VpYlFiMW1SNEIrL01TdGNvWHVtVWI0MHhNa1grdzhNTDBE*/
$PeepSoPageUsers = new PeepSoPageUsers($page->id);
$PeepSoPageUsers->update_members_count('banned');
$PeepSoPageUsers->update_members_count('pending_user');
$PeepSoPageUsers->update_members_count('pending_admin');
?>

<div class="ps-tabs ps-members__tabs ps-page__members-tabs ps-tabs--arrows">
  <div class="ps-tabs__item ps-members__tab <?php if (!$tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $page->get_url() . 'members/'; ?>">
      <span><?php echo __('All Followers', 'pageso'); ?></span>
    </a>
  </div>

  <div class="ps-tabs__item ps-members__tab <?php if ('management'==$tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $page->get_url() . 'members/management'; ?>">
      <span><?php echo __('Management', 'pageso'); ?></span>
    </a>
  </div>

  <?php if($PeepSoPageUser->can('manage_users')) { ?>
  <div class="ps-tabs__item ps-members__tab <?php if ('invited' == $tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $page->get_url() . 'members/invited'; ?>">
      <?php echo sprintf(__('<span>Invited</span><span class="ps-tabs__count ps-js-invited-count" data-id="%d">%s</span>', 'pageso'), $page->id, $page->pending_user_members_count); ?>
    </a>
  </div>

  <div class="ps-tabs__item ps-members__tab <?php if ('banned' == $tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $page->get_url() . 'members/banned'; ?>">
      <?php echo sprintf(__('<span>Banned</span><span class="ps-tabs__count">%s</span>', 'pageso'), $page->banned_members_count); ?>
    </a>
  </div>
  <?php } ?>
</div>
