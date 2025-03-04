<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjUDMzNXdIYzBoR3lkQzFSQnluZXVPZ0ZyUWswV0pUOWtKVnRvL0FmTXdBaGRFS0k3cGJ4UzJuQmJhcVlRS2lhc05aVkp0ajdZTzhSZ1lRbG5qc3BMWkxUcjdKcFU4ODAvOTR3QjIxSU1ISUQ1Rms3Mk5XaDQyRUp4MWxSQUVRTU5aemtmWVBKdStlZHhmZ3Z0aURhaVlndldHODc5QWp3Rm9yYjUzallOZlY2*/

class PeepSoPageCategoriesAdmin
{
	public static function administration()
	{
		self::enqueue_scripts();

		$PeepSoPageCategories = new PeepSoPageCategories(TRUE, TRUE);
		$categories = $PeepSoPageCategories->categories;

		// var_dump($categories);

		PeepSoTemplate::exec_template('admin', 'page_categories_list', $categories);
	}

	public static function enqueue_scripts()
	{
		wp_register_script('peepso-npm', PeepSo::get_asset('js/npm-expanded.min.js'),
			array('peepso'), PeepSo::PLUGIN_VERSION, 'all');

		wp_register_script('peepso-util', PeepSo::get_asset('js/util.min.js'),
			array('jquery', 'peepso-npm'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_register_script('peepso-admin-pagecategories',
			PeepSo::get_asset('js/admin-pagecategories.js', dirname(dirname(__FILE__))),
			array('jquery', 'jquery-ui-sortable', 'underscore', 'peepso', 'peepso-util'), PeepSo::PLUGIN_VERSION, TRUE);

		//wp_register_script('peepso-admin-pagecategories', PeepSo::get_asset('js/pagecategories.min.js'),
		//	array('jquery', 'jquery-ui-sortable', 'underscore', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_enqueue_script('peepso-admin-pagecategories');

		wp_enqueue_style('peepso-pagecategories-admin',
			PeepSo::get_asset('css/pagecategories.css', dirname(dirname(__FILE__))),
			array(), PeepSo::PLUGIN_VERSION);
	}
}
