```
# PaddlePulse

PaddlePulse is a WordPress plugin designed to integrate Paddle's payment gateway with WordPress sites. This plugin enables seamless handling of payments, subscriptions, and webhook notifications, with a focus on maintainability and scalability.

## Code Formatting

To ensure consistency and readability in the codebase, please follow these formatting guidelines:

- **PHP:** Follow the [WordPress PHP coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- **JavaScript:** Use [ESLint](https://eslint.org/) with the recommended rules.
- **CSS:** Adhere to [Tailwind CSS conventions](https://tailwindcss.com/docs) for styling.
- **General:** Use 4 spaces for indentation and ensure that all code is well-commented.

## Getting Started

1. **Download or Clone the Plugin:**
    *   Download the latest release ZIP file and upload it via WordPress admin (Plugins > Add New > Upload Plugin).
    *   Alternatively, clone the repository into your `wp-content/plugins/` directory:
        ```bash
        git clone https://github.com/yourusername/PaddlePulse.git
        ```
2. **Activate the Plugin:**
    *   Navigate to **Plugins** in your WordPress admin dashboard and activate PaddlePulse.

== User Guide ==

This guide will help you configure and use the PaddlePulse plugin.

=== 1. Obtaining Your Paddle Credentials ===

To use PaddlePulse, you'll need the following from your Paddle dashboard:

*   **Seller ID (Vendor ID):** Find this in your Paddle Dashboard under Developer Tools > Authentication.
*   **API Key:** Generate an API key from Developer Tools > Authentication. Ensure it has the necessary permissions for the operations you intend to use (e.g., product information, subscription management).
*   **Webhook Secret Key:** Find or set this up in Developer Tools > Webhooks (or Alerts > Setup Webhooks). This key is crucial for verifying that webhook notifications are genuinely from Paddle. You will need to configure a webhook URL in Paddle (see step 3).

=== 2. Configuring the Plugin ===

1.  Navigate to **PaddlePulse > Settings** in your WordPress admin dashboard.
2.  Enter your **Paddle API Key**.
3.  Enter your **Paddle Seller ID**.
4.  Enter your **Paddle Webhook Secret Key**.
5.  Choose the **Environment**:
    *   **Sandbox:** For testing purposes with your Paddle sandbox account. Ensure your API Key and Seller ID are from your Paddle Sandbox environment.
    *   **Live:** For processing real transactions with your live Paddle account. Ensure your API Key and Seller ID are from your Paddle Live environment.
6.  Click **Save Changes**.

=== 3. Setting Up Webhooks in Paddle ===

Webhooks are essential for Paddle to communicate real-time events (like successful payments, subscription updates, cancellations, etc.) to your WordPress site. This allows your site to stay in sync with Paddle.

1.  In your Paddle Dashboard, go to Developer Tools > Webhooks (or Alerts > Setup Webhooks).
2.  Click on "Add Webhook" or similar option to create a new webhook endpoint.
3.  For the "URL for receiving webhook alerts" (or similar field), enter the following URL:
    `YOUR_WEBSITE_URL/?paddle-webhook=true`
    (Replace `YOUR_WEBSITE_URL` with the actual URL of your website, e.g., `https://example.com/?paddle-webhook=true`)
4.  Ensure the webhook secret key you configured in the plugin settings (Step 2.4) **exactly matches** the one you set for this webhook endpoint in your Paddle dashboard. This is vital for security.
5.  Select the events you want Paddle to send to your site. It's generally recommended to subscribe to all events related to subscriptions, payments, and customer updates to ensure comprehensive data synchronization. Key events include:
    *   Subscription Created
    *   Subscription Updated
    *   Subscription Cancelled
    *   Payment Succeeded
    *   Payment Refunded
    *   (And any other events relevant to your integration)

=== 4. Testing Your Connection ===

1.  After saving your API Key, Seller ID, and Environment settings in the PaddlePulse settings page, click the **Test Connection** button.
2.  A message will appear at the bottom of the form indicating whether the connection to the Paddle API was successful or if there was an error.
3.  If the connection fails:
    *   Double-check that your API Key and Seller ID are correct for the selected Environment (Sandbox/Live).
    *   Ensure there are no leading/trailing spaces in the copied credentials.
    *   If `WP_DEBUG` is enabled, check the WordPress debug log (usually `wp-content/debug.log`) for more detailed error messages from the API call.

=== 5. Understanding Logging ===

If `WP_DEBUG` is enabled in your WordPress `wp-config.php` file, PaddlePulse will log important events and errors to the WordPress debug log. This log file is typically located at `wp-content/debug.log`. Logged information includes:

*   **API Communication:** Errors encountered when trying to communicate with the Paddle API (e.g., during the connection test).
*   **Webhook Processing:**
    *   Successful webhook verification and processing (a preview of the payload is logged).
    *   Failures in webhook processing, such as missing secret key, missing signature header, empty payload, malformed signature, timestamp validation failure, or signature mismatch.
*   **Connection Test:** Details of connection test attempts and their outcomes.

This logging is invaluable for troubleshooting issues with your Paddle integration.

## Features

The following features are currently implemented in PaddlePulse:

- [x] **Basic Integration**
  - [x] Add Paddle API integration (communication layer for API requests)
  - [x] Implement admin settings page (for API keys, environment selection, webhook secret)
  - [x] Add Tailwind CSS styling to admin UI (for a modern look and feel)

- [x] **Connection Testing**
  - [x] Implement connection test feature (verifies API key and Seller ID against selected environment)
  - [x] Add test connection button and feedback in admin UI

- [x] **Sandbox and Live Environments**
  - [x] Add functionality to switch between sandbox and live environments
  - [x] Implement secure API key and seller ID handling (using WordPress options with sanitization)

- [x] **Advanced Features**
  - [x] Handle Paddle webhooks for event handling (endpoint creation, signature validation, basic logging of payload)
  - [x] Provide detailed error reporting and logging (centralized `Paddle_Utils::log()` utility for WP_DEBUG mode)
  - [x] Add customer support integration (links to email and Paddle documentation in admin panel)

- [ ] **Further Enhancements (Future Scope)**
  - [ ] Detailed processing of specific webhook events (e.g., creating local user accounts, updating subscription statuses in DB).
  - [ ] Frontend integration for checkout (e.g., shortcodes, blocks).
  - [ ] More comprehensive error handling and user feedback for webhook issues.

- [ ] **Documentation**
  - [x] Complete plugin documentation (this readme.txt)
  - [x] Create user guide and setup instructions (included in this readme.txt)

- [ ] **Testing and Validation**
  - [ ] Perform thorough testing on various WordPress versions
  - [ ] Validate plugin performance and security
  - [ ] Ensure compatibility with popular themes and plugins

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. **Fork the Repository** and create a new branch for your changes.
2. **Make Your Changes** and ensure they adhere to the code formatting guidelines.
3. **Submit a Pull Request** with a detailed description of your changes.

## License

This project is licensed under the MIT License.

## Contact

For any questions or issues, please contact [Your Name/Plugin Support](mailto:you@example.com).
(Note: Please replace `you@example.com` with your actual support email address if this plugin is distributed.)

```