=== Connect Contact Form 7 to Salesforce ===
Contributors: procoders
Tags: salesforce, contact form 7, crm, connection
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://procoders.tech/

Seamlessly integrate Contact Form 7 with Salesforce to automate your lead management process.

== Description ==

Connect Contact Form 7 seamlessly with Salesforce using the Salesforce & Contact Form 7 Integration plugin by ProCoders. This powerful tool automates lead management, ensuring every form submission is efficiently synced with your Salesforce account. With secure and instant data transfer, you can effortlessly manage your leads.

**Third-Party Service Integration**:
This plugin integrates with Salesforce, a third-party service, to manage and sync data from Contact Form 7 submissions. By using this plugin, data collected through Contact Form 7 will be sent to Salesforce for processing and storage. Please be aware that by using this plugin, you agree to the terms of service and privacy policy of Salesforce.
Specifically, our plugin sends requests to the following URLs:
*** REST API Endpoint ***
- Production: https://yourInstance.salesforce.com/services/data/v51.0/
- Sandbox: https://test.salesforce.com/services/data/v51.0/
*** OAuth 2.0 Token Endpoint ***
- Production: https://login.salesforce.com/services/oauth2/token
- Sandbox: https://test.salesforce.com/services/oauth2/token

Please read Salesforce Privacy Policy carefully to understand what data is collected and how it is used.
Salesforce Privacy Policy: https://www.salesforce.com/company/legal/privacy/
Salesforce Terms of Service: https://www.salesforce.com/company/legal/sfdc-website-terms-of-service/

**Features include:**
- Effortless Integration: Connect Contact Form 7 with Salesforce Leads.
- Automated Syncing: Enjoy automated syncing of Contact Form 7 submissions directly to your Salesforce account.
- Intuitive Mapping: Easily map form fields to Salesforce properties for streamlined data management.
- Secure Communication: Utilize the latest Salesforce API for secure and reliable data transfer, keeping your information up to date and protected.

== Installation ==

1. Download the plugin from WordPress.org and unzip it.
2. Upload the 'connect-cf7-to-salesforce' folder to your '/wp-content/plugins/' directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to the plugin settings page and follow the on-screen instructions to connect your Salesforce account.

**For WordPress Multisite:**
1. Upload and install the plugin zip file via 'Network Admin' -> 'Plugins' -> 'Add New'.
2. Do not network-activate. Instead, activate the plugin on a per-site basis for precise control.

== Screenshots ==

1. On the settings page, you can auth to your Salesforce account, as well and configure email notifications for API errors.
2. On this page, we see a list of available CF7 forms, with forms actively integrated with Salesforce highlighted in green and inactive ones in red. To modify the settings of a form, click on the pencil icon.
3. On this page, you can activate the integration, and specify which fields should correspond to CF7 fields.

== Frequently Asked Questions ==
= Could you answer to some question I have about your plugin? =
For more detailed assistance or any additional queries, please feel free to contact us through
our website: https://procoders.tech/contacts/
= Is Form 7 Salesforce integration easy with your plugin? =
Yes, our plugin ensures seamless integration between Contact Form 7 and Salesforce. Designed to be user-friendly and streamlined, it allows you to sync form submissions with your Salesforce account effortlessly.
= How do I obtain a Salesforce API key? =
Your personal API key can be found under Settings > Personal preferences > API in your Salesforce account.
= How can I contribute to the plugin? =
We appreciate your interest in contributing! Whether it's reporting bugs, suggesting features, or proposing enhancements, please reach out to us at hello@procoders.tech. We value community input and are committed to improving our products.
= What is Contact Form 7? =
Contact Form 7 is a versatile WordPress plugin for creating and managing various types of contact forms on your website. It offers extensive features and integrations, making it a preferred solution for collecting user data and facilitating communication.
= Is Contact Form 7 GDPR compliant? =
Yes, Contact Form 7 is designed to be GDPR compliant, offering features and settings to help website owners adhere to GDPR requirements.
= Is Salesforce a CRM or marketing tool? =
Salesforce is a comprehensive platform that combines CRM capabilities with marketing, sales, and customer service functionalities, making it a versatile solution for businesses.
== Changelog ==
= 1.0.0 =
- Initial release. Offers seamless integration between Contact Form 7 and Salesforce for efficient lead management.

== Upgrade Notice ==

= 1.0.0 =
Salesforce is a comprehensive platform that offers both CRM (Customer Relationship Management) and marketing tools. While it started as a CRM solution.
Blockquote:
> Powerful tool automates lead management, ensuring every form submission is efficiently synced with your Salesforce account.