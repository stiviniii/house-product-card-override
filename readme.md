# 🏠 House Product Card Override

![WordPress Version](https://img.shields.io/badge/WordPress-6.4+-blue.svg?logo=wordpress)
![WooCommerce Version](https://img.shields.io/badge/WooCommerce-3.0+-purple.svg?logo=woocommerce)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-8892BF.svg?logo=php)
![License](https://img.shields.io/badge/license-GPL--2.0+-green.svg)

A lightweight WordPress plugin that seamlessly overrides the default WooCommerce product loop UI globally across your store. It replaces the default WooCommerce grids with the beautiful, modern product card design popularized by our **House Products Carousel Block**.

---

## ✨ Features

- **🛍️ Global Loop Override**: Automatically replaces the default WooCommerce product cards on your Shop page, Category pages, and related product sections.
- **🖼️ Image Effects**: Secondary gallery image hover reveals. 
- **🏷️ Smart Badges**: Includes WooCommerce Sale flashes and custom "Best Seller" tags (auto-applied when product is tagged `best-seller`).
- **⚡ Quick Buy**: Adds a convenient "+ Quick Buy" overlay button to streamline the checkout process.
- **📐 Specification Rows**: Displays key property data (floors, bedrooms, bathrooms, area, dimensions) out-of-the-box using Advanced Custom Fields (ACF) or Secure Custom Fields (SCF).
- **🎨 Modern Aesthetic**: Responsive grid layouts, beautiful box shadows, and hover animations built right in.

## 🛠️ Requirements

- **WordPress** 6.4 or higher
- **WooCommerce** 3.0 or higher
- **Secure Custom Fields (SCF)** or **Advanced Custom Fields (ACF)** for specification fields

## 📋 Displaying House Specifications

If you want the product cards to display architectural/real-estate specifications, create the following custom fields (using SCF or ACF) and assign them to WooCommerce products:

- `floors` — Number of floors
- `bedrooms` — Number of bedrooms
- `bathrooms` — Number of bathrooms
- `width` — Property width (displayed with "m" suffix)
- `length` — Property length (displayed with "m" suffix)
- `area` — Property area (displayed with "m²" suffix)

*(Note: If ACF/SCF is not installed or fields are empty, the specification row gracefully hides itself.)*

## 🚀 Installation

1. **Download/Clone** this repository and place the `house-product-card-override` folder into your `/wp-content/plugins/` directory.
2. **Activate** the plugin through the 'Plugins' menu in your WordPress dashboard.
3. Visit your **Shop page** - your products will instantly reflect the new modern card design!

## 🤝 Compatibility 

This plugin plays nicely alongside the **House Products Carousel Block**. You can use the block on specific pages, while this override plugin seamlessly powers your catalog and archive pages with the same consistent design language.
