<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPalE5RlJUeE81UTZML1ZNdksvUW0xdnpQYnc0ZldhZU9sVDRHTFE5Q2tjcEcwbTNMWnJ0aWpPQ2VqWGs0Sm8vem5vUmhDQUk1elpLRlk0bElZYU1WdjVha1dDdjBLWDNOeEZuazl2SnNMa012UWNJTGd2bzJ3T0JoVXhKaWxhU2h3PQ==*/

$accept = '';

$uploadFileTypes = trim(strtolower(PeepSo::get_option_new('fileuploads_allowed_filetype')));
if ($uploadFileTypes) {
    $uploadFileTypes = preg_split("/\s+/", $uploadFileTypes);
    if (count($uploadFileTypes)) {
        $uploadFileTypes = '.' . implode(',.', $uploadFileTypes);
        $accept = 'accept="' . $uploadFileTypes . '"';
    }
}

?><i class="ps-chat__window-input-addon gcis gci-file ps-js-file-trigger" style="right:68px"></i>
<input type="file" class="ps-chat-window-input-uploader fileupload ps-js-file-file" name="filedata[]"
    <?php echo $accept ?> style="display:none" />
