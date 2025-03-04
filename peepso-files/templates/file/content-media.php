<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaWlFc2d6MEVxWE51VDBocTlreWxoTlZCK0tlUVR0b2s3d2MxS05nMjY2Sm94NG05V21LR2tDai9Za0VTUGg0SGVVbU9VaDloaGN5b1ZxR3pJYktRb0xQRTNRS2orU2RWY2VXdGd1alRqSWNzb0NIOE5EWFBFNndMMk1IT216OStLYll2N2hENjZMT29XMTBYZjNZV3pr*/

if ($files) {
    foreach ($files as $file) {
        $data = PeepSoFileUploads::prepare_for_display($file);

        PeepSoTemplate::exec_template('file', 'single-file', $data);
    }
}