<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaERCVjJ5MGlGT0pVMHRpOTV1YVJpV1JuY04reWFPVkN4VnlRVnMrdCtPTU16WGhaak9jVWM0WUl1ajhhMnBDc04xQlI1UnlsOUp4UTN5bXFBeTB6MHdGcTBUUlRzV3RBcFdwVHJOaXNZME9VNG9abm5SZ3p3Z1Z3MzgzV1JiSlNEWkFGVS9PbDVxVE9SSko5bHpoQzEv*/

class PeepSo3_REST_V1_Endpoint_File_Download extends PeepSo3_REST_V1_Endpoint {

    private $current_user;
    private $id;
    private $files_model;

    public function __construct() {

        parent::__construct();

        $this->current_user = get_current_user_id();
        $this->id = $this->input->int('id', 0); // the file id of profile being viewed
        $this->files_model = new PeepSoFilesModel();
    }

    public function read() {
        if ($this->id) {
            $post = get_post($this->id);
            $file = get_attached_file($post->ID);
            $wp_filetype = wp_check_filetype($file);
            $file_mime = $this->ext_to_mime($wp_filetype['ext']);

            //@TODO: Implementation permission for each add-ons
            $accesible = apply_filters('peepso_file_download_authorized', true, $post, $this->current_user);
            if (!$accesible) {
                return [
                    'error' => 'unauthorized'
                ];
            }

            // List of content types to be opened directly from the browser.
            $open_inline = ['application/pdf'];
            $file_disposition = in_array($file_mime, $open_inline) ? 'inline' : 'attachment';

            nocache_headers();
            header("Content-type: $file_mime");
            header('Content-Disposition: ' . $file_disposition . '; filename="' . $post->post_title . '"');
            readfile($file);
            exit;
        }

        return [
            'error' => 'file_not_downloaded'
        ];
    }

    protected function can_read() {
        //@TODO: Access validation
        return TRUE;
    }

    protected function ext_to_mime($ext) {
        $mime_map = [
            'pdf' => 'application/pdf',
        ];

        return isset($mime_map[$ext]) ? $mime_map[$ext] : $ext;
    }

}
