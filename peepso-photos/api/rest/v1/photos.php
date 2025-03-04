<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaXFqZHRYSmprcmhjZ05obWxQYkhpTUdQVlFmTzhYb0pUNXlsOUlhNkI0WTZ4SjVEZnpyZlNiaGw0d2llS0JSdmhLVWQ2K055S1ByRHN0bXFLK0RiMzhIeHQ2RzltN3B3VnE0Z3Q2RmJHbHlTOTVlWDFFWmx5MDZwZ1FtUkd4alUwPQ==*/

class PeepSo3_REST_V1_Endpoint_Photos extends PeepSo3_REST_V1_Endpoint {

    private $page;
    private $limit;

    public function __construct() {

        parent::__construct();

        $this->page = $this->input->int('page', 1);
        $this->limit = $this->input->int('limit', 1);
    }

    public function read() {
        $offset = ($this->page - 1) * $this->limit;

        if ($this->page < 1) {
            $offset = 0;
        }

        $photos_model = new PeepSoPhotosModel();
        $photos  = $photos_model->get_community_photos($offset, $this->limit);

        if (count($photos)) {
            $message = 'success';
        } else {
            $message = __('No photo', 'picso');
        }

        return [
            'photos' => $photos,
            'message' => $message
        ];
    }

    protected function can_read() {
        return TRUE;
    }

}
