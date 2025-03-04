<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPak90eDhJbWdrTTRNZzhqMVVjbU9BenpOVjg0bXNlZlpVOHVRRVpja2M1ZmxxSy9ic1M0NW11UlUyUjh2OFVwWHIvVlh4bW93dGoxb1U1WUU5WmJqMUxGZ0xLSkxxTmkwNWo4RytJaVNadDg0Qm9SNGFUL0FBRHEweDB6ZHJmMlNnPQ==*/

/**
 * Class PeepSoPageAjax
 * Handle operations on a single page object only
 * Page ID is inside $this->_page_id
 * Interfaces with PeepSoPageModel via $this->_model
 */
class PeepSoPageAjax extends PeepSoPageAjaxAbstract
{
    protected function __construct()
    {
        parent::__construct();

        if($this->_page_id > 0) {
            $this->_model= new PeepSoPage($this->_page_id);
        }
    }

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return array(
            'page',
        );
    }

    /**
     * GET
     * @param PeepSoAjaxResponse $resp
     */
    public function page(PeepSoAjaxResponse $resp)
    {
        // SQL dsafe
        $keys = $this->_input->value('keys', 'id', FALSE);
        $page = PeepSoPageAjaxAbstract::format_response($this->_model, PeepSoPageAjaxAbstract::parse_keys('page', $keys), $this->_page_id);

        $resp->success(TRUE);
        $resp->set('page', $page);
    }

    /**
     * POST
     * Create a page
     * @param PeepSoAjaxResponse $resp
     * @return void
     */
    public function create(PeepSoAjaxResponse $resp)
    {
        $page_data = array(
            'name'			=> $this->_input->value('name', '', FALSE), // SQL safe
            'description'	=> $this->_input->value('description', '', FALSE), // SQL safe,

            'owner_id'		=> get_current_user_id(),
            'meta'			=> $this->_input->value('meta', array(), FALSE), // SQL safe,
        );

        if(PeepSo::get_option('pages_categories_enabled', FALSE)) {
            $page_data['category_id'] = $this->_input->value('category_id', array(), FALSE);
        }

        $errors = PeepSoPage::validate($page_data);

        if(count($errors)){
            $resp->error($errors);
            return( FALSE );
        }

        // respect line breaks
        $description = $this->_input->value('description', '', FALSE); // SQL Safe
        $description = htmlspecialchars($description);
        $page_data['description'] = trim(PeepSoSecurity::strip_content($description));

        $page = new PeepSoPage(NULL, $page_data);

        $resp->success(1);
        $resp->set('redirect', $page->get_url());
    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_property(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-property')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified', 'pageso'));
        } else {

            $prop_name = $this->_input->value('property_name', '', FALSE); // SQL Safe
            $prop_value = $this->_input->value('property_value', '', FALSE); // SQL Safe

            $page_data = array(
                $prop_name => $prop_value,
            );

            $errors = PeepSoPage::validate($page_data);

            if (count($errors)) {
                $resp->success(FALSE);
                foreach ($errors as $key => $error) {
                    $resp->error($error);
                }
                return;
            }

            $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

            if ($PeepSoPageUser->can('manage_page')) {
                $PeepSoPage = new PeepSoPage($this->_page_id);
                $PeepSoPage->update($page_data);

                $resp->success(TRUE);
                $resp->set('msg', __('Setting saved', 'pageso'));
            } else {
                $resp->success(FALSE);
                $resp->error(__('Insufficient permissions', 'pageso'));
            }
        }
    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_name(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-name')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {

            $name = $this->_input->value('name', '', FALSE); // SQL Safe

            $page_data = array(
                'id' => $this->_page_id,
                'name' => $name,
            );

            $errors = PeepSoPage::validate($page_data);

            if (count($errors)) {
                $resp->success(FALSE);
                foreach ($errors as $key => $error) {
                    $resp->error($error);
                }
                return;
            }

            $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

            if ($PeepSoPageUser->can('manage_page')) {
                $PeepSoPage = new PeepSoPage($this->_page_id);
                $PeepSoPage->update($page_data);

                if ($PeepSoPage->published) {
                    do_action('peepso_action_page_rename', $this->_page_id, get_current_user_id());
                }

                // if slug should update automatically
                if(1 == PeepSo::get_option('pages_slug_edit', 0)) {

                    // Set an empty slug...
                    $PeepSoPage->update(array('slug'=>''));

                    // ...and let the constructor figure it out
                    $PeepSoPage = new PeepSoPage($this->_page_id);

                    $resp->set('redirect', $PeepSoPage->get_url().'settings/');
                }

                $resp->success(TRUE);
                $resp->set('msg', __('Page name saved.', 'pageso'));
            } else {
                $resp->success(FALSE);
                $resp->error(__('Insufficient permissions', 'pageso'));
            }
        }
    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_slug(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-slug')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {

            $slug = $this->_input->value('slug', '', FALSE); // SQL Safe

            $slug = wp_unique_post_slug(sanitize_title_with_dashes($slug), $this->_page_id, 'any', 'peepso-page', 0);

            if(in_array($slug, PeepSoPagesPlugin::$page_slug_blocklist)) {
                $slug = 'page-'.$slug;
                $slug = wp_unique_post_slug(sanitize_title_with_dashes($slug), $this->_page_id, 'any', 'peepso-page', 0);
            }

            $page_data = array(
                'slug' => $slug,
            );

            $errors = PeepSoPage::validate($page_data);

            if (count($errors)) {
                $resp->success(FALSE);
                foreach ($errors as $key => $error) {
                    $resp->error($error);
                }
                return;
            }

            $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

            if ($PeepSoPageUser->can('manage_page')) {
                $PeepSoPage = new PeepSoPage($this->_page_id);
                $PeepSoPage->update($page_data);

                if ($PeepSoPage->published) {
                    do_action('peepso_action_page_slug_change', $this->_page_id, get_current_user_id());
                }

                // let the constructor double check everything
                $PeepSoPage = new PeepSoPage($this->_page_id);

                $resp->set('redirect', $PeepSoPage->get_url().'settings/');

                $resp->success(TRUE);
                $resp->set('msg', __('Page name saved.', 'pageso'));
            } else {
                $resp->success(FALSE);
                $resp->error(__('You are not authorized to manage this page\'s settings', 'pageso'));
            }
        }
    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_privacy(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-privacy')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
            return;
        }

        $privacy = $this->_input->int('privacy');

        $page_data = array(
            'privacy' => $privacy,
        );

        $PeepSoPagePrivacy = PeepSoPagePrivacy::_();


        if (!isset($PeepSoPagePrivacy[$privacy])) {
            $resp->success(FALSE);
            $resp->error(__('Invalid Page Privacy', 'pageso') . ": $privacy");
            return;
        }

        $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

        if ($PeepSoPageUser->can('manage_page')) {
            $PeepSoPage = new PeepSoPage($this->_page_id);
            $PeepSoPage->update($page_data);

            if ($PeepSoPage->published) {
                do_action('peepso_action_page_privacy_change', $this->_page_id, get_current_user_id());
            }

            $resp->success(TRUE);
            $resp->set('msg', __('Page privacy changed.', 'pageso'));
            $resp->set('new_privacy', PeepSoPagePrivacy::_($privacy));
        } else {
            $resp->success(FALSE);
            $resp->error(__('You are not authorized to change this page name.', 'pageso'));
        }

    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_description(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-description')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {
            $description = $this->_input->value('description', '', FALSE); // SQL Safe
            $description = htmlspecialchars($description);
            $description = trim(PeepSoSecurity::strip_content($description));

            $page_data = array(
                'description' => $description,
            );

            $errors = PeepSoPage::validate($page_data);

            if (count($errors)) {
                $resp->success(FALSE);
                foreach ($errors as $key => $error) {
                    $resp->error($error);
                }
                return;
            }

            $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

            if ($PeepSoPageUser->can('manage_page')) {

                $PeepSoPage = new PeepSoPage($this->_page_id);
                $PeepSoPage->update($page_data);

                $resp->success(TRUE);
                $resp->set('msg', __('Page description saved.', 'pageso'));
            } else {
                $resp->success(FALSE);
                $resp->error(__('You are not authorized to change this page description.', 'pageso'));
            }
        }
    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_categories(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-categories')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {
            $page_data = array(
                'category_id' => $this->_input->value('category_id', array(), FALSE),
            );

            $errors = PeepSoPage::validate($page_data);

            if (count($errors)) {
                $resp->success(FALSE);
                foreach ($errors as $key => $error) {
                    $resp->error($error);
                }
                return;
            }

            $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

            if ($PeepSoPageUser->can('manage_page')) {

                PeepSoPageCategoriesPages::add_page_to_categories($this->_page_id, $this->_input->value('category_id', array(), FALSE));

                $resp->success(TRUE);
                $resp->set('msg', __('Page categories saved.', 'pageso'));
                $resp->set('category_id', PeepSoPageCategoriesPages::get_categories_id_for_page($this->_page_id));

            } else {
                $resp->success(FALSE);
                $resp->error(__('You are not authorized to change this page categories.', 'pageso'));
            }
        }
    }

    /**
     * POST
     * @param PeepSoAjaxResponse $resp
     * return void
     */
    public function set_page_custom_input(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'set-page-custom-input')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {
            $PeepSoPageUser = new PeepSoPageUser($this->_page_id);

            if ($PeepSoPageUser->can('manage_page')) {
                $resp->success(TRUE);
                $resp->set('msg', __('Page custom input saved.', 'pageso'));
            } else {
                $resp->success(FALSE);
                $resp->error(__('You are not authorized to change this page categories.', 'pageso'));
            }
        }
    }

    /** GROUP IMAGES (COVER/AVATAR) **/

    /*
     * POST
     * Called from AjaxHandler when an image crop request is performed
     */
    public function avatar_crop(PeepSoAjaxResponse $resp)
    {
        $page_id = $this->_input->int('u');
        $page_user = new PeepSoPageUser($page_id);
        $page = new PeepSoPage($page_id);

        if (! (wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'page-avatar') && $page_user->can('manage_content')) ) {
            $resp->success(FALSE);
            $resp->error(__('Invalid access', 'pageso'));
            return;
        }

        $x = $this->_input->int('x');
        $y = $this->_input->int('y');
        $x2 = $this->_input->int('x2');
        $y2 = $this->_input->int('y2');
        $width = $this->_input->int('width');
        $height = $this->_input->int('height');
        $tmp = $this->_input->int('tmp');

        if ($tmp) {
            $src_orig = $page->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $page->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $page->get_image_dir() . 'avatar-tmp.jpg';
        } else {
            $avatar_hash = get_post_meta($page->id, 'peepso_page_avatar_hash', TRUE);
            $avatar_hash = $avatar_hash ? $avatar_hash . '-' : '';

            $src_orig = $page->get_image_dir() . $avatar_hash  . 'avatar-orig.jpg';
            copy($src_orig, $page->get_image_dir() . 'avatar-orig-tmp.jpg');

            $src_orig = $page->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $page->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $page->get_image_dir()  . 'avatar-tmp.jpg';
        }

        $si = new PeepSoSimpleImage();
        $si->load($src_orig);

        // Resize image as edited on the screen, we do this because getting x and y coordinates
        // are unreliable when we are cropping from the edit avatar page; the dimensions on the edit
        // avatar page is not the same as the original image dimensions.
        if (isset($width) && isset($height) && $width > 0 && $height > 0) {
            $si->resize($width, $height);
        }

        // Create full-size avatar.
        $new_image = imagecreatetruecolor(PeepSo::get_option('avatar_size', 250), PeepSo::get_option('avatar_size', 250));
        imagecopyresampled($new_image, $si->image,
            0, 0, $x, $y,
            PeepSo::get_option('avatar_size', 250), PeepSo::get_option('avatar_size', 250), $x2 - $x, $y2 - $y);
        imagejpeg($new_image, $src_full, 100);

        // Create thumbnail-size avatar.
        $new_image = imagecreatetruecolor(PeepSoUser::THUMB_WIDTH, PeepSoUser::THUMB_WIDTH);
        imagecopyresampled($new_image, $si->image, // Resize from cropeed image "$si"
            0, 0, $x, $y,
            PeepSoUser::THUMB_WIDTH, PeepSoUser::THUMB_WIDTH, $x2 - $x, $y2 - $y);
        imagejpeg($new_image, $src_thumb, 75);

        $resp->set('image_url', $page->get_tmp_avatar(TRUE));
        $resp->success(TRUE);
    }

    /**
     * Avatar rotate.
     * @param PeepSoAjaxResponse $resp
     */
    public function avatar_rotate(PeepSoAjaxResponse $resp)
    {
        $page_id = $this->_input->int('u');
        $page_user = new PeepSoPageUser($page_id);
        $page = new PeepSoPage($page_id);

        if (! (wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'page-avatar') && $page_user->can('manage_content')) ) {
            $resp->success(FALSE);
            $resp->error(__('Invalid access', 'pageso'));
            return;
        }

        $direction = $this->_input->value('direction', 'cw', FALSE);
        $tmp = $this->_input->int('tmp');

        if ($tmp) {
            $src_orig = $dest_orig = $page->get_image_dir() . 'avatar-orig-tmp.jpg';
            $src_full = $dest_full = $page->get_image_dir() . 'avatar-full-tmp.jpg';
            $src_thumb = $dest_thumb = $page->get_image_dir() . 'avatar-tmp.jpg';
        } else {
            $avatar_hash = get_post_meta($page->id, 'peepso_page_avatar_hash', TRUE);
            $avatar_hash = $avatar_hash ? $avatar_hash . '-' : '';

            $src_orig = $page->get_image_dir() . $avatar_hash  . 'avatar-orig.jpg';
            $src_full = $page->get_image_dir() . $avatar_hash  . 'avatar-full.jpg';
            $src_thumb = $page->get_image_dir() . $avatar_hash  . 'avatar.jpg';

            $dest_orig = $page->get_image_dir() . 'avatar-orig-tmp.jpg';
            $dest_full = $page->get_image_dir() . 'avatar-full-tmp.jpg';
            $dest_thumb = $page->get_image_dir() . 'avatar-tmp.jpg';
        }

        $angle = 'ccw' === $direction ? 90 : 270;

        foreach (['orig', 'full', 'thumb'] as $type) {
            $si = new PeepSoSimpleImage();
            $si->load(${'src_' . $type});
            $si->rotate($angle);
            $si->save(${'dest_' . $type}, $si->image_type, 100);
        }

        $image_url = $page->get_tmp_avatar(TRUE);
        $orig_image_url = str_replace('-full', '-orig', $image_url);

        $resp->set('image_url', $image_url);
        $resp->set('orig_image_url', $orig_image_url);
        $resp->success(TRUE);
    }

    /*
     * POST
     * Called from AjaxHandler when an avatar upload request is performed
     */
    public function avatar_upload(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'page-avatar')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {

            $page_id = $this->_input->int('page_id');

            $page_user = new PeepSoPageUser($page_id);
            $page = new PeepSoPage($page_id);

            // can-manage_content is called on PeepSoPageUser
            if(!$page_user->can('manage_content')) {
                $resp->error(__('You do not have enough permissions.', 'pageso'));
                $resp->success(FALSE);
                return;
            } else {

                if (isset($_FILES['filedata'])) {
                    $allowed_mime_types = apply_filters(
                        'peepso_page_avatar_mime_types',
                        array(
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        )
                    );

                    if (empty($_FILES['filedata']['tmp_name'])) {
                        $resp->error(__('The file you uploaded is either missing or too large.', 'pageso'));
                        $resp->success(FALSE);
                        return;
                    }

                    if (!in_array($_FILES['filedata']['type'], $allowed_mime_types)) {
                        $resp->error(__('The file type you uploaded is not allowed.', 'pageso'));
                        $resp->success(FALSE);
                        return;
                    }

                    $page->move_avatar_file($_FILES['filedata']['tmp_name']);

                    $image_url = $page->get_tmp_avatar();
                    $full_image_url = $page->get_tmp_avatar(TRUE);
                    $orig_image_url = str_replace('-full', '-orig', $full_image_url);

                    // check image dimension
                    $si = new PeepSoSimpleImage();
                    $orig_image_path = $page->get_image_dir() . 'avatar-orig-tmp.jpg';
                    $si->load($orig_image_path);
                    $width = $si->getWidth();
                    $height = $si->getHeight();
                    $avatar_size = PeepSo::get_option('avatar_size','100');

                    if (($width < $avatar_size) || ($height < $avatar_size)) {
                        $resp->success(FALSE);
                        $resp->set('width', $width);
                        $resp->set('height', $height);
                        $resp->error(sprintf(__('Minimum avatar resolution is %d x %d pixels.', 'pageso'), $avatar_size, $avatar_size));
                        return;
                    }

                    $resp->set('image_url', $image_url);
                    $resp->set('orig_image_url', $orig_image_url);
                    $resp->set('orig_image_path', $orig_image_path);
                    $resp->success(TRUE);

                    return;
                } else {
                    $resp->error(__('No file uploaded.', 'pageso'));
                    $resp->success(FALSE);
                    return;
                }
            }
        }
    }

    /*
     * POST
     * Called from AjaxHandler when an avatar upload is finalized
     */
    public function avatar_confirm(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'page-avatar')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {
            $page_id = $this->_input->int('page_id');

            $page_user = new PeepSoPageUser($page_id);
            $page = new PeepSoPage($page_id);

            // can-manage_content is called on PeepSoPageUser
            if($page_user->can('manage_content')) {
                $page->finalize_move_avatar_file();

                $resp->set('image_url', $page->get_avatar_url());
                $resp->success(TRUE);
            } else {
                $resp->error(__('You do not have enough permissions.', 'pageso'));
                $resp->success(FALSE);
            }
        }
    }

    /**
     * POST
     * Deletes a page's avatar
     */
    public function avatar_delete(PeepSoAjaxResponse $resp)
    {
        $page_id = $this->_input->int('page_id');

        $page_user = new PeepSoPageUser($page_id);
        $page = new PeepSoPage($page_id);

        // can-manage_content is called on PeepSoPageUser
        // SQL safe, WP sanitizes it
        if($page_user->can('manage_content') && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'page-avatar')) {
            $page->delete_avatar();
            $resp->success(TRUE);
        } else {
            $resp->success(FALSE);
        }
    }

    /*
     * POST
     * Called from AjaxHandler when a cover photo upload request is performed
     * @param object PeepSoAjaxResponse $resp
     */
    public function cover_upload(PeepSoAjaxResponse $resp)
    {
        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'page-cover')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {
            $page_id = $this->_input->int('page_id');

            $page_user = new PeepSoPageUser($page_id);
            $page = new PeepSoPage($page_id);

            // can-manage_content is called on PeepSoPageUser
            if($page_user->can('manage_content')) {

                if (isset($_FILES['filedata'])) {
                    $allowed_mime_types = apply_filters(
                        'peepso_page_cover_mime_types',
                        array(
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        )
                    );

                    if (!in_array($_FILES['filedata']['type'], $allowed_mime_types)) {
                        $resp->error(__('The file type you uploaded is not allowed.', 'pageso'));
                        $resp->success(FALSE);
                        return;
                    }

                    if (empty($_FILES['filedata']['tmp_name'])) {
                        $resp->error(__('The file you uploaded is either missing or too large.', 'pageso'));
                        $resp->success(FALSE);
                        return;
                    }

                    $add_to_stream = PeepSo::get_option('photos_pages_enable_post_updates_page_cover', 1);
                    $page->move_cover_file($_FILES['filedata']['tmp_name']);

                    $resp->set('image_url', $page->get_cover_url());
                    $resp->success(TRUE);
                } else {
                    $resp->error(__('No file uploaded.', 'pageso'));
                    $resp->success(FALSE);
                }
            } else {
                $resp->success(FALSE);
                $resp->error(__('You do not have enough permissions.', 'pageso'));
            }
        }
    }

    /*
     * POST
     * Called from AjaxHandler when a cover photo repositoin request is performed
     */
    public function cover_reposition(PeepSoAjaxResponse $resp)
    {
        $page_id = $this->_input->int('page_id');

        // SQL safe, WP sanitizes it
        if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'page-cover')) {
            $resp->success(FALSE);
            $resp->error(__('Request could not be verified.', 'pageso'));
        } else {
            $page_user = new PeepSoPageUser($page_id);
            $page = new PeepSoPage($page_id);

            // can-manage_content is called on PeepSoPageUser
            if($page_user->can('manage_content')) {

                $x = $this->_input->int('x', 0);
                $y = $this->_input->int('y', 0);

                update_post_meta($page_id, 'peepso_cover_position_x', $x);
                update_post_meta($page_id, 'peepso_cover_position_y', $y);

                $resp->notice(__('Changes saved.', 'pageso'));
                $resp->success(TRUE);
            } else {
                $resp->success(FALSE);
                $resp->error(__('You do not have enough permissions.', 'pageso'));
            }
        }
    }

    /**
     * POST
     * Rotates a page's cover photo
     */
    public function cover_rotate(PeepSoAjaxResponse $resp)
    {
        $page_id = $this->_input->int('page_id');
        $page_user = new PeepSoPageUser($page_id);
        $page = new PeepSoPage($page_id);

        if (! ($page_user->can('manage_content') && wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'page-cover')) ) {
            $resp->success(FALSE);
            return;
        }

        $direction = $this->_input->value('direction', 'cw', FALSE);
        $angle = 'ccw' === $direction ? 90 : 270;

        $old_cover_hash = get_post_meta($page->id, 'peepso_page_cover_hash', TRUE);
        if (!$old_cover_hash) {
            $old_cover_hash = '';
        }

        $new_cover_hash = substr(md5(time()), 0, 10);

        // Full-size cover image.
        $filename = $old_cover_hash . '-cover.jpg';
        $filepath = $page->get_image_dir();
        $file = $filepath . $filename;

        if (!file_exists($file)) {
            $resp->success(FALSE);
            return;
        }

        $si = new PeepSoSimpleImage();
        $si->load($file);
        $si->rotate($angle);

        // Save in the new filename to avoid cache.
        $file = str_replace($old_cover_hash . '-cover', $new_cover_hash . '-cover', $file);
        $si->save($file, $si->image_type, 100);

        // Delete old file and use the new one.
        $page->delete_cover_photo($old_cover_hash);
        update_post_meta($page->id, 'page_cover_photo', $page->get_image_url() . $new_cover_hash . '-cover.jpg');
        update_post_meta($page->id, 'peepso_page_cover_hash', $new_cover_hash);

        $resp->set('image_url', $page->get_cover_url());
        $resp->success(TRUE);
    }

    /**
     * POST
     * Deletes a page's cover photo
     */
    public function cover_delete(PeepSoAjaxResponse $resp)
    {
        $page_id = $this->_input->int('page_id');
        $page_user = new PeepSoPageUser($page_id);
        $page = new PeepSoPage($page_id);

        // SQL safe, WP sanitizes it
        if ($page_user->can('manage_content') && wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'page-cover')) {
            $resp->success($page->delete_cover_photo());
        } else {
            $resp->success(FALSE);
        }
    }

}
