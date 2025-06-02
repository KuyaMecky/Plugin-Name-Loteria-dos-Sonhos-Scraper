# Loteria dos Sonhos Scraper Plugin

A custom WordPress plugin for scraping and displaying results from the Brazilian lottery site **Loteria dos Sonhos**. Includes advanced features, multiple shortcodes, and caching for optimal performance.

---

## 🚀 Key Features

- **Multiple Shortcodes** for different pages and use cases
- **Smart Content Detection** using multiple selectors
- **Comprehensive Cleanup**: Removes ads, navigation, social buttons, forms, and unwanted elements
- **Image & Link Handling**: Converts relative URLs to absolute, opens external links in new tabs
- **Caching System**: 5-minute cache for faster loading and reduced server load
- **Error Handling**: User-friendly error messages if scraping fails
- **Custom Styling**: Built-in CSS for responsive, clean display
- **Admin Interface**: Settings page listing all available shortcodes

---

## 🧩 Available Shortcodes

| Shortcode                            | Description                              |
|---------------------------------------|------------------------------------------|
| `[loteria_dos_sonhos_home]`           | Scrapes and displays the main homepage   |
| `[loteria_dos_sonhos_results]`        | Scrapes and displays the results page    |
| `[loteria_dos_sonhos_live]`           | Scrapes and displays live results        |
| `[loteria_dos_sonhos_yesterday]`      | Scrapes and displays yesterday's results |
| `[loteria_dos_sonhos_home_cached]`    | Cached version of the homepage           |
| `[loteria_dos_sonhos_results_cached]` | Cached version of the results page       |
| `[loteria_dos_sonhos_palpite]`        | Scrapes the Palpite LDS page             |
| `[loteria_dos_sonhos_palpite_cached]` | Cached version of the Palpite page       |

---

## 📝 Usage

- Insert any shortcode into your post or page to display the corresponding lottery content.
- For best performance, use the cached versions (e.g., `[loteria_dos_sonhos_palpite_cached]`).
- The Palpite content uses the same styling and link handling as other shortcodes.

---

## ⚙️ Installation

1. **Save** the plugin file as `loteria-dos-sonhos-scraper.php`.
2. **Upload** it to your `/wp-content/plugins/` directory.
3. **Activate** the plugin in your WordPress admin dashboard.
4. **Use** the shortcodes in your posts or pages.
5. **Configure** settings under **Settings > Loteria dos Sonhos**.

---

## 🆚 Improvements Over Original

- Supports multiple pages (not just one)
- Enhanced error handling with user-friendly messages
- Efficient caching for improved performance
- More robust element removal
- Responsive design with built-in CSS
- Easy management via admin interface

---

## 🌎 Localization

- Tailored for the Brazilian lottery site structure
- Handles Portuguese-language content and formatting

---

## 📢 Notes

- Ensure your server allows outgoing HTTP requests for scraping.
- For support or updates, check the plugin settings page.

---

Enjoy fast, reliable, and well-formatted lottery results on your WordPress site!