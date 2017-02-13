<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
/**
 * To adjust a setting uncomment it by removing the leading #
 * WARNING! Invalid code can break down your site!
 */
$config = array();

# $config['account_order_limit']                  = 10; // Max number of orders per page in accout
# $config['admin_autocomplete_limit']             = 10; // Max number of autocomplete suggestions for admin
# $config['image_style_admin']                    = 2; // Image style for admin UI
# $config['admin_list_limit']                     = 20; // Max number of items for admin UI
# $config['autocomplete_limit']                   = 10; // Max number of autocomplete suggestions for customers
# $config['dashboard_limit']                      = 10; // Max number of items in dashboard blocks
# $config['theme_mobile']                         = 'mobile'; // Default module ID of mobile theme
# $config['theme_backend']                        = 'backend'; // Default module ID of backend theme
# $config['wishlist_limit']                       = 20; // Max number of wishlist items for anonymous
# $config['wishlist_limit_<role ID>']             = 20; // Max number of allowed wishlist items per <role ID>
# $config['cart_cookie_lifespan']                 = 31536000; // Lifetime of cookie that keeps anonymous cart, in seconds
# $config['cart_login_merge']                     = 0; // Whether to merge old and current cart items on checkout login
# $config['cart_preview_limit']                   = 5; // Max number of cart items to display in cart preview
# $config['cart_sku_limit']                       = 10; // Max number of cart items per SKU that customer may have
# $config['cart_item_limit']                      = 20; // Max total number of cart items that customer may have
# $config['category_alias_pattern']               = '%t.html'; // Pattern to generate category alias
# $config['category_alias_placeholder']           = array('%t' => 'title'); // Replacement rule to generate category alias
# $config['category_image_dirname']               = 'category'; // Default folder for uploaded category images
# $config['cron_interval']                        = 86400; // Interval between cron executions, in seconds
# $config['cron_key']                             = ''; // Cron secret key
# $config['csv_delimiter']                        = ","; // CSV field delimiter
# $config['csv_delimiter_multiple']               = "|"; // Character to separate multiple values in CSV
# $config['csv_delimiter_key_value']              = ":"; // Character to separate key => value items in CSV
# $config['currency']                             = 'USD'; // Default store currency
# $config['currency_cookie_lifespan']             = 31536000; // Lifetime of cookie that keeps the current currency, in seconds
# $config['date_prefix']                          = 'd.m.Y'; // Default time format - hours
# $config['date_suffix']                          = ' H:i'; // Default time format - minutes
# $config['error_level']                          = 2; // Default error reporting level
# $config['error_live_report']                    = 0; // Whether to inform about PHP errors on every page
# $config['export_limit']                         = 50; // Max number of CSV rows to parse per one export iteration
# $config['file_upload_translit']                 = 1; // Whether to transliterate names of uploaded files
# $config['history_lifespan']                     = 2628000; // Max number of seconds to keep records in "history" table
# $config['image_cache_lifetime']                 = 31536000; // Max number of seconds to keep in browser cache processed images
# $config['import_limit']                         = 10; // Max number of CSV rows to parse per one import iteration
# $config['language']                             = ''; // Default store language
# $config['marketplace_sort']                     = 'views'; // Default sorting parameter for marketplace modules
# $config['marketplace_order']                    = 'desc'; // Default sorting order for marketplace modules
# $config['no_image']                             = 'image/misc/no-image.png'; // Path to placeholder image
# $config['order_status']                         = 'pending'; // Default order status
# $config['order_status_initial']                 = 'pending'; // Default status for new orders
# $config['order_status_canceled']                = 'canceled'; // Default status for canceled orders
# $config['order_update_notify_customer']         = 1; // Whether to send notification to customer on order status change
# $config['order_log_limit']                      = 10; // Max order log records to display for admin
# $config['page_alias_pattern']                   = '%t.html'; // Pattern to generate page alias
# $config['page_alias_placeholder']               = array('%t' => 'title'); // Replacement rule to generate page alias
# $config['page_image_dirname']                   = 'page'; // Default folder for uploaded page images
# $config['product_alias_pattern']                = '%t.html'; // Pattern to generate product alias
# $config['product_alias_placeholder']            = array('%t' => 'title'); // Replacement rule to generate product alias
# $config['comparison_cookie_lifespan']           = 604800; // Max number of seconds to keeps products to compare in cookie
# $config['comparison_limit']                     = 10; // Max number of products to compare
# $config['product_height']                       = 0; // Default product height (dimension)
# $config['product_length']                       = 0; // Default product length (dimension)
# $config['product_weight']                       = 0; // Default product weight (dimension)
# $config['product_width']                        = 0; // Default product width (dimension)
# $config['product_image_dirname']                = 'product'; // Default folder for uploaded product images
# $config['recent_cookie_lifespan']               = 31536000; // Max number of seconds to keeps recently viewed products in cookie
# $config['recent_limit']                         = 12; // Max number of recently viewed products
# $config['related_limit']                        = 12; // Max number of related products to show
# $config['product_sku_pattern']                  = 'PRODUCT-%i'; // Pattern to generate product SKU
# $config['product_sku_placeholder']              = array('%i' => 'product_id'); // Replacement rule to generate product SKU
# $config['product_subtract']                     = 0; // Default state of "Subtract" option
# $config['product_volume_unit']                  = 'mm'; // Default volume unit for products
# $config['product_weight_unit']                  = 'g'; // Default weight unit for products
# $config['rating_editable']                      = 1; //Whether to allow to edit product ratings
# $config['rating_enabled']                       = 1; // Whether to allow product ratings
# $config['rating_unvote']                        = 1; // Whether to allow to delete product ratings
# $config['review_deletable']                     = 1; // Whether to allow to delete product reviews
# $config['review_editable']                      = 1; //Whether to allow to edit product reviews
# $config['review_enabled']                       = 1; // Whether to allow product reviews
# $config['review_max_length']                    = 1000; // Max number of characters in product review
# $config['review_min_length']                    = 10; // Min number of characters in product review
# $config['review_limit']                         = 10; // Max number of reviews to show on product pages
# $config['review_status']                        = 1; // Default status for review added by a customer
# $config['report_log_lifespan']                  = 86400; // Max number of seconds to keep records in "log" table
# $config['store']                                = 1; // Database ID of default store
# $config['summary_delimiter']                    = '<!--summary-->'; // Character(s) to separate summary and full text
# $config['timezone']                             = 'Europe/London'; // Default store timezone
# $config['user_address_limit']                   = 4; // Max number of addresses for logged in user
# $config['user_address_limit_anonymous']         = 1; // Max number of addresses for anonymous user
# $config['user_cookie_name']                     = 'user_id'; // Name of cookie that keeps cart user ID
# $config['user_password_max_length']             = 255; // Max number of password characters
# $config['user_password_min_length']             = 8; // Min number of password characters
# $config['user_registration_email_admin']        = 1; // Whether to send email to admin when an account is registered
# $config['user_registration_email_customer']     = 1; // Whether to send email to customer when his account is registered
# $config['user_registration_login']              = 1; // Whether to log in registered user immediately
# $config['user_registration_status']             = 1; // Default account status upon registration
# $config['user_reset_password_lifespan']         = 86400; // Max number of seconds before password reset link will expire
# $config['user_superadmin']                      = 1; // Default database ID for superadmin
# $config['order_complete_message']               = 'Thank you for your order! Order ID: <a href="!url">!order_id</a>, status: !status'; // Default message to show when order is completed by logged in user
# $config['order_complete_message_anonymous']     = 'Thank you for your order! Order ID: !order_id, status: !status';          // Default message to show when order is completed by anonymous
# $config['cli_disabled']                         = 0; // Whether comman line support enabled
# $config['filter_superadmin']                    = 0; // Filter ID for superadmin. Defaults to disabled, i.e raw output
# $config['filter_1_status']                      = 1; // Status of "Minimal" filter (ID 1)
# $config['filter_1_role_id']                     = 1; // Role ID for "Minimal" filter (ID 1)
# $config['filter_1_config']                      = array(); // Array of HTML Purifier's options for "Minimal" filter (ID 1)
# $config['filter_2_status']                      = 1; // Status of "Advanced" filter (ID 2)
# $config['filter_2_role_id']                     = 1; // Role ID for "Advanced" filter (ID 2)
# $config['filter_2_config']                      = array(); // Array of HTML Purifier's options for "Advanced" filter (ID 2)
# $config['filter_3_status']                      = 1; // Status of "Maximum" filter (ID 3)
# $config['filter_3_role_id']                     = 1; // Role ID for "Maximum" filter (ID 3)
# $config['filter_3_config']                      = array(); // Array of HTML Purifier's options for "Maximum" filter (ID 3)
# $config['compress_js']                          = 0; // Whether to aggregate JS files
# $config['compress_css']                         = 0; // Whether to aggregate and compress CSS files
# $config['gapi_browser_key']                     = ''; // Google API browser key
# $config['email_subject_order_created_admin']    = ''; // Text of E-mail subject sent after order has been created to an admin
# $config['email_message_order_created_admin']    = ''; // Text of E-mail message sent after order has been created to an admin
# $config['email_subject_order_created_customer'] = ''; // Text of E-mail subject sent after order has been created to a customer
# $config['email_message_order_created_customer'] = ''; // Text of E-mail message sent after order has been created to a customer
# $config['email_subject_order_updated_customer'] = ''; // Text of E-mail subject sent after order has been updated to a customer
# $config['email_message_order_updated_customer'] = ''; // Text of E-mail message sent after order has been updated to a customer

/**
 * End of configurable settings
 * The settings below are appended automatically during installation.
 */
