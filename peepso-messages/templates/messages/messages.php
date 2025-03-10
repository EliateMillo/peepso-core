<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPanFoV1djYkFYTlZXSWpQVHpOaCs1QWZtT1pnbUVMVmhQaVlwYXlXT1Iva0hPYitYWllzQzEvbThoSnh1WnRKQ3lMdng3enE5YUlRSGlhelBwWk40eDZNeG5QYVlEbkRBWkl1aFpTVnBKRU1Wam9rWmNWd3hiQU4waGR1U0tES0xjPQ==*/

$PeepSoPostbox = PeepSoPostbox::get_instance();
$PeepSoMessages = PeepSoMessages::get_instance();
$PeepSoGeneral = PeepSoGeneral::get_instance();
$PeepSoUser = PeepSoUser::get_instance(get_current_user_id());

$pref_url = PeepSoUser::get_instance()->get_profileurl() . 'about/preferences/';
$is_open_page = intval(PeepSo::get_option('messages_compose_in_new_page', 0));

// TODO:
$notif = TRUE;
$muted = TRUE;

// Messagebox data.
$options = apply_filters('peepso_messagebox_options', []);
$types = apply_filters('peepso_messagebox_types', []);

?><div class="peepso">
    <div class="ps-page ps-page--messages">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
        <?php wp_nonce_field('load-messages', '_messages_nonce'); ?>

        <div class="pso-messages">
            <div class="pso-messages__side pso-messages__side--open">
                <div class="pso-messages-side__header">
                    <span><?php echo __('Messages', 'msgso'); ?></span>
                    <button class="pso-btn pso-btn--link pso-tip pso-tip--left pso-messages__focus" data-ps="btn-focus" aria-label="<?php echo esc_attr(__('Focus mode', 'msgso')) ?>"><i class="pso-i-arrow-expand"></i></button>
                    <?php if (class_exists('PeepSoMessagesPlugin') && FALSE !== apply_filters('peepso_permissions_messages_create', TRUE)) { ?>
                        <?php if ($is_open_page) { ?>
                            <a href="<?php echo PeepSo::get_page('messages')?>new" class="pso-btn pso-btn--primary" aria-label="<?php echo __('New message', 'msgso'); ?>">
                                <i class="pso-i-envelope-plus"></i><span><?php echo __('New message', 'msgso'); ?></span>
                            </a>
                        <?php } else { ?>
                            <a href="javascript:" class="pso-btn pso-btn--primary" onclick="ps_messages.new_message(undefined, 'is_friend')" aria-label="<?php echo __('New message', 'msgso'); ?>">
                                <i class="pso-i-envelope-plus"></i><span><?php echo __('New message', 'msgso'); ?></span>
                            </a>
                        <?php } ?>
                    <?php } ?>
                    <button class="pso-btn pso-btn--link" data-ps="btn-toggle"><i class="pso-i-angle-double-small-left"></i></button>
                </div>
                <div class="pso-messages-side__filters">
                    <button class="pso-btn ps-js-messages-show-all active"
                        aria-label="<?php echo __('All', 'peepso-core'); ?>">
                        <i class="pso-i-list"></i><span><?php echo __('All', 'peepso-core'); ?></span>
                    </button>
                    <button class="pso-btn ps-js-messages-show-unread"
                        aria-label="<?php echo __('Unread', 'peepso-core'); ?>">
                        <i class="pso-i-envelope-dot"></i><span><?php echo __('Unread', 'peepso-core'); ?></span>
                    </button>
                </div>
                <?php if (class_exists('PeepSoMessagesPlugin') && FALSE !== apply_filters('peepso_permissions_messages_create', TRUE)) { ?>
                    <div class="pso-messages__new">
                    <?php if ($is_open_page) { ?>
                        <a href="<?php echo PeepSo::get_page('messages')?>new" class="pso-btn pso-btn--primary pso-tip pso-tip--right" aria-label="<?php echo __('New message', 'msgso'); ?>">
                            <i class="pso-i-envelope-plus"></i>
                        </a>
                    <?php } else { ?>
                        <a href="javascript:" class="pso-btn pso-btn--primary pso-tip pso-tip--right" onclick="ps_messages.new_message(undefined, 'is_friend')" aria-label="<?php echo __('New message', 'msgso'); ?>">
                            <i class="pso-i-envelope-plus"></i>
                        </a>
                    <?php } ?>
                    </div>
                <?php } ?>
                <div class="pso-messages__toggle"><button class="pso-btn pso-btn--link" data-ps="btn-toggle"><i class="pso-i-angle-double-small-right"></i></button></div>
                <div class="pso-messages__list ps-js-messages-list">
                    <form action="" class="ps-form ps-messages__search ps-js-messages-search-form" role="form" onsubmit="return false;">
                        <div class="ps-messages__search-inner ps-input__wrapper ps-input__wrapper--icon">
                            <i class="ps-input__icon pso-i-search"></i>
                            <input type="text" class="ps-input ps-input--icon ps-input--sm search-query" name="query" aria-describedby="queryStatus"
                                value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(__('Search...', 'msgso')); ?>" />
                            <button type="reset" class="ps-messages__search-clear ps-js-btn-clear" title="<?php echo __('Clear search', 'msgso') ?>"
                                style="display:none">
                                <i class="gcis gci-circle-xmark"></i>
                            </button>
                        </div>
                        <div class="ps-messages__search-results ps-js-loading" style="display: none;">
                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                        </div>
                    </form>

                    <div class="pso-messages__info" style="display:none"><i class="pso-i-envelope"></i><span><?php echo __('No messages found.', 'msgso'); ?></span></div>

                    <form class="ps-form ps-messages__inbox" action="<?php PeepSo::get_page('messages'); ?>" method="post">
                        <div class="ps-messages__list-wrapper ps-js-messages-list-scrollable">
                            <div class="pso-messages__items ps-js-messages-list-table"></div>
                            <div class="ps-js-messages-list-loading" style="text-align: center">
                                <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="pso-messages__main">
                <style>/* Adjust legacy style. */ .pso-messages__header .ps-avatar { margin-left: calc(-1*var(--PADD--MD)); } .pso-messages__header .ps-avatar:first-child { margin-left: 0; }</style>
                <div class="pso-messages__header" data-ps="message-header">
                    <button class="pso-btn pso-btn--link" data-ps="btn-toggle"><i class="pso-i-angle-double-small-right"></i></button>
                    <div class="pso-messages__participant" style="flex-grow: 1">
                        <div class="pso-avatar pso-avatar--sm pso-messages-participant__avatar" data-ps="avatars"></div>
                        <span class="pso-messages-participant__name" data-ps="users"></span>
                    </div>
                    <div class="pso-messages__options">
                        <button class="pso-btn pso-btn--link pso-tip pso-tip--left pso-messages__focus" data-ps="btn-focus" aria-label="<?php echo esc_attr(__('Focus mode', 'msgso')) ?>"><i class="pso-i-arrow-expand"></i></button>
                        <button class="pso-btn pso-btn--link" data-ps="btn-options"><i class="pso-i-menu-dots-vertical"></i></button>
                        <div class="ps-dropdown__menu pso-messages-options__menu" data-ps="dropdown"></div>
                    </div>
                </div>

                <div class="pso-messages__recipients" data-ps="recipients" style="display:none">
                    <select name="recipients"
                        data-placeholder="<?php echo __('Add People to the conversation', 'msgso'); ?>"
                        data-loading="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>"
                        multiple></select>
                    <?php wp_nonce_field('add-participant', 'add-participant-nonce'); ?>
                    <button class="pso-btn" data-ps="btn-cancel-recipients">
                        <?php echo __('Cancel', 'msgso'); ?>
                    </button>
                    <button class="pso-btn pso-btn--primary" data-ps="btn-add-recipients">
                        <?php echo __('Done', 'msgso'); ?>
                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none;">
                    </button>
                </div>

                <style>/* Adjust legacy style. */ .ps-chat__message:last-child { margin-bottom: var(--PADD); }</style>
                <div class="pso-messages__chat ps-chat__messages" data-ps="message-thread" style="overflow:auto;">
                    <div data-ps="loading" style="text-align:center; padding-top:20px; display:none">
                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                    </div>
                    <div data-ps="items"></div>
                    <div data-ps="temporary"></div>
                    <div data-ps="typing"></div>
                </div>

                <div class="pso-messages__post" data-ps="messagebox">
                    <?php if (count($types)) { ?>
                    <div class="pso-messages-post__attachments" data-ps="type_inputs" style="display:none">
                        <?php foreach ($types as $id => $type) { if (isset($type['html'])) { ?>
                        <div class="pso-messages-post__attachment" data-ps="type_input"
                                data-id="<?php echo esc_attr($id) ?>" style="position:relative; display:none">
                            <?php echo $type['html'] ?>
                        </div>
                        <?php } } ?>
                    </div>
                    <?php } ?>
                    <div class="pso-messages-post-input__wrapper">
                        <button class="pso-btn pso-btn--link pso-messages-post__cancel" data-ps="btn-cancel" style="display:none">
                            <i class="pso-i-cross-small"></i>
                        </button>
                        <textarea class="pso-input pso-messages-post__input" rows="1"
                            placeholder="<?php echo esc_attr(__('Write a message...', 'msgso')) ?>"></textarea>
                        <button class="pso-btn pso-btn--link pso-btn--primary pso-messages-post__send" data-ps="btn-submit" style="display:none">
                            <i class="pso-i-paper-plane-top"></i>
                        </button>
                    </div>
                    <div class="pso-messages-post-input__extra" data-ps="title-extra" style="display:none"></div>
                    <div class="pso-messages-post__menu">
                        <?php if (count($types)) { ?>
                        <div class="pso-messages-post__types" data-ps="types">
                            <?php foreach ($types as $id => $type) { ?>
                            <button class="pso-btn pso-btn--neutral pso-tip <?php echo $id === 'text' ? 'pso-active' : '' ?>"
                                    data-ps="type" data-id="<?php echo $id ?>" aria-label="<?php echo esc_attr($type['label']) ?>">
                                <i class="<?php echo esc_attr($type['icon']) ?>"></i>
                            </button>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <div class="ps-checkbox pso-messages-post__enter">
                            <?php $checkbox_id = str_replace('.', '-', uniqid('ps-entertosend-', true)); ?>
                            <input class="ps-checkbox__input" type="checkbox" id="<?php echo $checkbox_id ?>" data-ps="enter-to-send" disabled="disabled">
                            <label class="ps-checkbox__label" for="<?php echo $checkbox_id ?>">Enter to send</label>
                        </div>

                        <?php if (count($options)) { ?>

                        <div class="pso-messages-post__addons">
                            <?php foreach ($options as $key => $option) { ?>
                                <div class="pso-messages-post__addon">
                                    <button class="pso-btn pso-btn--link" data-ps="option" data-id="<?php echo $key ?>">
                                        <i class="<?php echo esc_attr($option['icon']) ?>"></i>
                                    </button>
                                    <?php if ($option['html']) { ?>
                                    <div class="ps-dropdown__menu pso-messages-post-addon__box" data-ps="dropdown" data-id="<?php echo $key ?>" style="display:none">
                                        <?php echo $option['html'] ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>




        
        <div class="ps-messages ps-js-messages" style="display:none">
            <div class="ps-messages__inner">
                <div class="ps-messages__side ps-js-messages-list">
                    <form action="" class="ps-form ps-messages__search ps-js-messages-search-form" role="form" onsubmit="return false;">
                        <div class="ps-messages__search-inner ps-input__wrapper ps-input__wrapper--icon">
                            <i class="ps-input__icon gcis gci-search"></i>
                            <input type="text" class="ps-input ps-input--icon ps-input--sm search-query" name="query" aria-describedby="queryStatus"
                                value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(__('Search...', 'msgso')); ?>" />
                            <button type="reset" class="ps-messages__search-clear ps-js-btn-clear" title="<?php echo __('Clear search', 'msgso') ?>"
                                style="display:none">
                                <i class="gcis gci-circle-xmark"></i>
                            </button>
                        </div>
                        <div class="ps-messages__search-results ps-js-loading" style="display: none;">
                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                        </div>
                    </form>

                    <span style="display:none"><?php echo __('No messages found.', 'msgso'); ?></span>

                    <form class="ps-form ps-messages__inbox" action="<?php PeepSo::get_page('messages'); ?>" method="post">
                        <div class="ps-messages__list-wrapper ps-js-messages-list-scrollable">
                            <div class="ps-messages__list ps-js-messages-list-table"></div>
                            <div class="ps-js-messages-list-loading" style="text-align: center">
                                <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                            </div>
                        </div>
                    </form>

                </div>

                <div class="ps-messages__view ps-js-conversation-wrapper">
                    <div class="ps-messages__info" style="display:none">
                        <div class="ps-messages__info-inner">
                            <?php if (class_exists('PeepSoMessagesPlugin')) : ?>
                                <p><?php echo __('No messages found.', 'msgso'); ?></p>
                                <?php do_action('peepso_messages_list_header'); ?>
                            <?php else : ?>
                                <?php echo __('No messages found.', 'msgso'); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ps-js-conversation-loading" style="text-align:center; padding-top:20px; display:none">
                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                    </div>
                    <div class="ps-js-conversation" style="display:none">
                        <div class="ps-conversation">
                            <div class="ps-conversation__header">
                                <div class="ps-conversation__back">
                                    <a href="#" class="ps-btn ps-btn--app ps-js-conversation-back">
                                        <i class="gcis gci-left-long"></i><?php echo __('All messages', 'msgso'); ?>
                                    </a>
                                </div>
                                <div class="ps-conversation__header-inner">
                                    <div class="ps-conversation__participants ps-js-participant-summary">
                                        <span class="ps-conversation__participants-label"><?php echo __('Conversation with', 'msgso'); ?>:</span>
                                        <span class="ps-conversation__status"><i class="gcir gci-clock"></i></span>
                                        <span class="ps-js-conversation-participant-summary"></span>
                                    </div>
                                    <div class="ps-conversation__options">
                                        <div class="ps-conversation__options-menu ps-tip ps-tip--arrow ps-js-conversation-options"
                                            data-id="{id}" aria-label="<?php echo __('Options', 'msgso'); ?>">
                                            <i class="gcis gci-cog"></i>
                                        </div>
                                    </div>
                                    <div class="ps-conversation__dropdown-menu ps-dropdown__menu ps-js-conversation-dropdown"
                                        style="display:none">
                                        <?php if (isset($show_blockuser)) { ?>
                                            <a href="#" class="ps-js-btn-blockuser" data-user-id="<?php echo $show_blockuser_id; ?>">
                                                <i class="gcis gci-ban"></i>
                                                <span><?php echo __('Block this user', 'msgso'); ?></span>
                                            </a>
                                        <?php } ?>
                                        <a href="#" id="add-recipients-toggle">
                                            <i class="gcis gci-user-plus"></i>
                                            <span><?php echo __('Add People to the conversation', 'msgso'); ?></span>
                                        </a>
                                        <?php if (isset($read_notification)) { ?>
                                            <a href="#" class="ps-js-btn-toggle-checkmark <?php echo $notif ? '' : ' disabled' ?>"
                                                onclick="return ps_messages.toggle_checkmark(<?php echo $parent->ID; ?>, <?php echo $notif ? 0 : 1 ?>);">
                                                <i class="gcir gci-check-circle"></i>
                                                <span><?php echo $notif ? __("Don't send read receipt", 'msgso') : __('Send read receipt', 'msgso'); ?></span>
                                            </a>
                                        <?php
                                        }
                                        if (isset($parent)) {
                                        ?>
                                            <a href="#" class="ps-js-btn-mute-conversation"
                                                onclick="return ps_messages.<?php echo $muted ? 'unmute' : 'mute'; ?>_conversation(<?php echo $parent->ID; ?>, <?php echo $muted ? 0 : 1; ?>);">
                                                <i class="<?php echo $muted ? 'gcis gci-bell-slash' : 'gcir gci-bell'; ?>"></i>
                                                <span><?php echo $muted ? __('Unmute conversation', 'msgso') : __('Mute conversation', 'msgso'); ?></span>
                                            </a>
                                        <?php } ?>
                                        <?php if (apply_filters('peepso_filter_chat_leave_conversation_enabled', true)) { ?>
                                            <a href="<?php echo $PeepSoMessages->get_leave_conversation_url(); ?>"
                                                onclick="return ps_messages.leave_conversation('<?php echo __('Are you sure you want to leave this conversation?', 'msgso'); ?>', this)">
                                                <i class="gcis gci-times"></i>
                                                <span><?php echo __('Leave this conversation', 'msgso'); ?></span>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="ps-conversation__add ps-js-recipients">
                                    <select name="recipients"
                                        data-placeholder="<?php echo __('Add People to the conversation', 'msgso'); ?>"
                                        data-loading="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>"
                                        multiple></select>
                                    <?php wp_nonce_field('add-participant', 'add-participant-nonce'); ?>
                                    <button class="ps-btn ps-btn--sm ps-btn--action ps-js-add-recipients">
                                        <?php echo __('Done', 'msgso'); ?>
                                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none;">
                                    </button>
                                </div>
                            </div>

                            <div class="ps-conversation__chat ps-js-conversation-scrollable">
                                <div class="ps-chat__messages">
                                    <div class="ps-js-conversation-messages">
                                        <div class="ps-js-conversation-messages-loading" style="text-align: center; visibility: hidden">
                                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                                        </div>
                                        <div class="ps-js-conversation-messages-list"></div>
                                        <div class="ps-js-conversation-messages-temporary"></div>
                                        <div class="ps-js-conversation-messages-typing"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="postbox-message" class="ps-postbox ps-conversation__postbox"></div>
                            <div data-template="postbox" style="display:none !important">
                                <?php $PeepSoPostbox->before_postbox(); ?>
                                <div class="ps-postbox__inner">
                                    <div id="ps-postbox-status" class="ps-postbox__content ps-postbox-content">
                                        <div class="ps-postbox__views ps-postbox-tabs"><?php $PeepSoPostbox->postbox_tabs('messages'); ?></div>
                                        <?php
                                        add_filter('peepso_permissions_post_create', '__return_true', 99);
                                        PeepSoTemplate::exec_template('general', 'postbox-status', ['placeholder' => __('Write a message...', 'msgso')]);
                                        ?>
                                    </div>

                                    <div class="ps-postbox__footer ps-js-postbox-footer ps-postbox-tab ps-postbox-tab-root" style="display: none;">
                                        <div class="ps-postbox__menu ps-postbox__menu--tabs">
                                            <?php $PeepSoGeneral->post_types(array('postbox_message' => TRUE)); ?>
                                        </div>
                                    </div>

                                    <div class="ps-postbox__footer ps-conversation__postbox-footer ps-js-postbox-footer ps-postbox-tab selected interactions">
                                        <div class="ps-postbox__menu ps-postbox__menu--interactions">
                                            <?php $PeepSoPostbox->post_interactions(array('postbox_message' => TRUE)); ?>
                                        </div>
                                        <div class="ps-postbox__actions ps-postbox-action">
                                            <div class="ps-checkbox ps-checkbox--enter">
                                                <input type="checkbox" id="enter-to-send" class="ps-checkbox__input ps-js-checkbox-entertosend">
                                                <label class="ps-checkbox__label" for="enter-to-send">
                                                    <?php printf(__('%s to send', 'msgso'), apply_filters('peepso_chat_enter_to_send', '&#9166;')); ?>
                                                </label>
                                            </div>

                                            <?php if (PeepSo::is_admin() && PeepSo::is_dev_mode('embeds')) { ?>
                                                <button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-js-btn-preview">Fetch URL</button>
                                            <?php } ?>
                                            <button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-tip ps-tip--arrow ps-postbox__action--cancel ps-button-cancel"
                                                aria-label="<?php echo __('Cancel', 'peepso-core'); ?>"
                                                style="display:none"><i class="gcis gci-backspace"></i></button>
                                            <button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit"
                                                style="display:none"><?php echo __('Post', 'peepso-core'); ?></button>
                                        </div>
                                        <div class="ps-loading ps-postbox-loading" style="display: none">
                                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                                            <div> </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $PeepSoPostbox->after_postbox(); ?>
                            </div>
                        </div>

                        <?php ob_start(); ?>
                        <div class="ps-chat__message ps-chat__message--me ps-js-temporary-message" style="opacity:.5">
                            <a class="ps-chat__message-avatar ps-avatar ps-tip ps-tip--arrow ps-tip--left"
                                href="<?php echo $PeepSoUser->get_profileurl(); ?>"
                                aria-label="<?php echo $PeepSoUser->get_fullname(); ?>">
                                <img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="" />
                            </a>
                            <div class="ps-chat__message-body">
                                <div class="ps-chat__message-user">
                                    <a href="<?php echo $PeepSoUser->get_profileurl(); ?>"><?php
                                                                                            do_action('peepso_action_render_user_name_before', $PeepSoUser->get_id());
                                                                                            echo $PeepSoUser->get_fullname();
                                                                                            do_action('peepso_action_render_user_name_after', $PeepSoUser->get_id());
                                                                                            ?></a>
                                </div>
                                <div class="ps-chat__message-content-wrapper">
                                    <div class="ps-chat__message-content">{{= data.content }}</div>
                                </div>
                                {{ if ('object' === typeof data.attachment) { }}
                                <div class="ps-chat__message-attachments">
                                    <div class="ps-media__attachment ps-media__attachment--{{= 'photo' === data.attachment.type ? 'photos' : data.attachment.type }}">
                                        {{ for ( var i = 0; i < data.attachment.count; i++ ) { }}
                                        <a class="ps-media ps-media--{{= data.attachment.type }}"
                                            style="width:62px; height:62px; line-height:62px; text-align:center; vertical-align:middle; background:lightgrey; border-radius: var(--BORDER-RADIUS--MD);">
                                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                                        </a>
                                        {{ } }}
                                    </div>
                                </div>
                                {{ } }}
                                <div class="ps-chat__message-time">
                                    <i class="gcir gci-check-circle"></i>
                                    <span><?php echo __('just now', 'msgso'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php

                        $message_item_template = ob_get_clean();
                        echo '<script type="text/template" data-template="message-item">';
                        echo $message_item_template;
                        echo '</script>';

                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>