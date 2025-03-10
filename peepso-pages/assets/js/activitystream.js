import $ from 'jquery';
import { hooks, observer } from 'peepso';
import { activity } from 'peepsodata';

const pagesData = window.peepsopagesdata || {};

const IS_PAGE_STREAM = +pagesData.page_id;
const IS_PERMALINK = +activity.is_permalink;
const NO_PIN_OUTSIDE_PAGE = +pagesData.pin_page_only;
const NO_PINNED_STYLE_OUTSIDE_PAGE = +pagesData.pin_page_only_no_pinned_style;
const CAN_PIN_POSTS = pagesData.peepsoPageUser && +pagesData.peepsoPageUser.can_pin_posts;

if (IS_PAGE_STREAM) {
	observer.addFilter('postbox_can_pin', () => CAN_PIN_POSTS);
}

if (NO_PIN_OUTSIDE_PAGE && !IS_PAGE_STREAM && !IS_PERMALINK) {
	let stashedPosts = [];

	// Do not show pinned page posts at the top of non-page stream.
	observer.addFilter(
		'peepso_activity',
		($posts, args = {}) => {
			let inLoop = true;
			if (args && 'undefined' !== typeof args.inLoop) {
				inLoop = !!args.inLoop;
			}

			$posts = $posts.map(function () {
				let $post = $(this),
					mappedPost = this,
					$header,
					timestamp;

				// Update and stash pinned page post.
				if ($post.hasClass('ps-js-activity-pinned')) {
					if (!$post.data('pending-post')) {
						$header = $post.find('.ps-js-post-header');
						// Check if it is a page post.
						if ($header.find('[data-ps=page-post-indicator]').length) {
							timestamp = +$header
								.find('.ps-post__date[data-timestamp]')
								.data('timestamp');
							if (timestamp) {
								$post.addClass('ps-js-activity-pinned-notop');

								// Remove pinned style if necessary.
								if (NO_PINNED_STYLE_OUTSIDE_PAGE) {
									$post.removeClass('ps-post--pinned');
								}

								// Only stash if posts are loaded during activity stream loop,
								// so that it can be retrieved back on subsequent loops later on.
								if (inLoop) {
									mappedPost = null;
									stashedPosts.push({ post: this, timestamp: timestamp });
									stashedPosts = _.sortBy(stashedPosts, function (stashed) {
										return -stashed.timestamp;
									});
								}
							}
						}
					}
				}

				// Put stashed pinned page posts to the original location as if its not pinned.
				else if (stashedPosts.length && $post.hasClass('ps-js-activity')) {
					$header = $post.find('.ps-js-post-header');
					timestamp = +$post.find('.ps-post__date[data-timestamp]').data('timestamp');
					if (timestamp) {
						stashedPosts = $.map(stashedPosts, function (stashed) {
							if (stashed.timestamp > timestamp) {
								if (!_.isArray(mappedPost)) {
									mappedPost = [mappedPost];
								}
								mappedPost.splice(mappedPost.length - 1, 0, stashed.post);
								return null;
							}
							return stashed;
						});
					}
				}

				return mappedPost;
			});

			return $posts;
		},
		10,
		2
	);

	// Clear the pending post cache on every filter change.
	observer.addFilter(
		'show_more_posts',
		params => {
			if (+params.page === 1) {
				stashedPosts = [];
			}
			return params;
		},
		10,
		1
	);

	// Return any pending posts HTML if available.
	observer.addFilter(
		'activitystream_pending_html',
		html => {
			let pendingHtml = '';
			stashedPosts.forEach(stashed => {
				let $wrapper = $('<div />').append(stashed.post);
				// Add a marker so the post will not be stashed again by "peepso_activity" filter above.
				$wrapper.find('.ps-js-activity-pinned').attr('data-pending-post', 'page');
				pendingHtml += $wrapper.html();
			});

			return html + pendingHtml;
		},
		10,
		1
	);
}
