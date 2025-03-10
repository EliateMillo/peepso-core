<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPaEZYM1RBdWZHV25CT3gyU1MvU1ZCZFNvOTloZExXMzZDSkR0clpsR0xxb1U4OEllSVVYK3JMWEh3OXUzWENuZExRRTFNdUlZc0UvWDNzdkhrdFhzWGQ5RUFjalk3TWp5djgwSGpsdUk5VGJXQXFOMm53bmFFMHdOY250ZW41djZPdDViZU5uNHZRc1MvMEV2MDJXMENJ*/

if (class_exists('PeepSoMaintenanceFactory')) {

    class PeepSoMaintenanceFiles extends PeepSoMaintenanceFactory
    {
        public static function deleteTemporaryFiles()
        {
			$uploaded_files = PeepSo3_Utilities_String::maybe_json_decode(PeepSo3_Mayfly::get('uploaded_files'), TRUE);

			$count = 0;
			if (is_array($uploaded_files) && count($uploaded_files) > 0) {
				foreach ($uploaded_files as $key => $file) {
					$index = explode('-', $key);

					// check if timestamp is older than 1 hour
					if (strtotime('+1 hour', $index[0]) <= current_time('timestamp')) {
						$count++;
						// remove from mayfly
						unset($uploaded_files[$key]);
						// delete the file
						@unlink($file);
					}
				}

				// update maylfy
                PeepSoFileUploads::update_uploaded_files_in_mayfly($uploaded_files);
			}

			return $count;
        }
    }
}
