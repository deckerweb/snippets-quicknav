# Snippets QuickNav (for the _Code Snippets_ Plugin)

![Snippets QuickNav plugin in action](https://raw.githubusercontent.com/deckerweb/snippets-quicknav/master/assets-github/snippets-quicknav-screenshot.png)

The **Snippets QuickNav** plugin adds a quick-access navigator (aka QuickNav) to the WordPress Admin Bar (Toolbar). It allows easy access to your Code Snippets listed by Active, Inactive, Snippet Type or Tag. Safe Mode is supported. Comes with inspiring links to snippet libraries.

#### Video Overview - Short Plugin Demo:
[![Code Snippets Quick-Access from Your WordPress Admin Bar – Perfect Time Saver – Free Add-On](https://img.youtube.com/vi/IhsvJghVHwc/0.jpg)](https://www.youtube.com/watch?v=IhsvJghVHwc)

#### Video On How to Import Snippet Video (Instead of Plugin):
[![Code Snippets Quick-Access from Your WordPress Admin Bar – Perfect Time Saver – Free Add-On](https://img.youtube.com/vi/TuQEDsn9GMk/0.jpg)](https://www.youtube.com/watch?v=TuQEDsn9GMk)

* Contributors: [David Decker](https://github.com/deckerweb), [contributors](https://github.com/deckerweb/snippets-quicknav/graphs/contributors)
* Tags: code snippets, quicknav, admin bar, toolbar, site builder, administrators
* Requires at least: 6.7
* Requires PHP: 7.4
* Stable tag: [main](https://github.com/deckerweb/snippets-quicknav/releases/latest)
* Donate link: https://paypal.me/deckerweb
* License: GPL v2 or later

---

[Support Project](#support-the-project) | [Installation](#installation) | [Updates](#updates) | [Description](#description) | [FAQ](#frequently-asked-questions) | [Custom Tweaks](#custom-tweaks-via-constants) | [Changelog](#changelog) | [Plugin's Backstory](#plugins-backstory) | [Plugin Scope / Disclaimer](#plugin-scope--disclaimer)

---

## Support the Project 

If you find this project helpful, consider showing your support by buying me a coffee! Your contribution helps me keep developing and improving this plugin.

Enjoying the plugin? Feel free to treat me to a cup of coffee ☕🙂 through the following options:

- [![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/W7W81BNTZE)
- [Buy me a coffee](https://buymeacoffee.com/daveshine)
- [PayPal donation](https://paypal.me/deckerweb)
- [Join my **newsletter** for DECKERWEB WordPress Plugins](https://eepurl.com/gbAUUn)

---

## Installation 

#### **Quick Install – as Plugin**
1. **Download ZIP:** [**snippets-quicknav.zip**](https://github.com/deckerweb/snippets-quicknav/releases/latest/download/snippets-quicknav.zip)
2. Upload via WordPress Plugins > Add New > Upload Plugin
3. Once activated, you’ll see the **Snippets** menu item in the Admin Bar.

#### **Alternative: Use as Code Snippet**
1. Below, download the appropriate snippet version
2. activate or deactivate in your snippets plugin

[**Download .json**](https://github.com/deckerweb/snippets-quicknav/releases/latest/download/ddw-snippets-quicknav.code-snippets.json) version for _Code Snippets_ (free & Pro) just use the "Import" page.

--> Please decide for one of both alternatives!

#### Minimum Requirements 
* WordPress version 6.7 or higher
* PHP version 7.4 or higher (better 8.3+)
* MySQL version 8.0 or higher / OR MariaDB 10.1 or higher
* Administrator user with capability `manage_options` and `activate_plugins`

#### Tested Compatibility
- **Latest Code Snippets free & Pro**: 3.6.8 / 3.6.9 (including with Multisite)
- **WordPress**: 6.7.2 / 6.8 Beta (including Multisite)
- **PHP**: 8.0 – 8.3

---

## Updates 

#### For Plugin Version:

1) Alternative 1: Just download a new [ZIP file](https://github.com/deckerweb/snippets-quicknav/releases/latest/download/snippets-quicknav.zip) (see above), upload and override existing version. Done.

2) Alternative 2: Use the (free) [**_Git Updater_ plugin**](https://git-updater.com/) and get updates automatically.

3) Alternative 3: Upcoming! – In future I will built-in our own deckerweb updater. This is currently being worked on for my plugins. Stay tuned!

#### For Code Snippet Version:

Just manually: Download the latest Snippet version (see above) and import it in _Code Snippets_. – You can delete the old snippet; then just activate the new one. Done.

---

## Description 

### How this Plugin Works 

1. **Your Code Snippets in the Admin Bar**: various listings – Active snippets, Inactive Snippets, by content/function type (PHP, HTML, CSS, JS, Cloud stuff), by tags
2. **Settings**: Direct links to relevant sections.
3. **Additional Links**:
	- _Snippets_: Code snippet libraries for WordPress by various authors, including the official Code Snippets Cloud
	- _Plugin ecosystem_: Links to resources like the Code Snippets website, Docs, Learning, Emergency fixes etc., plus Facebook group.
	- _About_: Includes links to the plugin author.
4. Support for Code Snippets Pro plugin version (premium); additional snippet types, settings, links etc.
5. Support for Code Snippets own "Safe Mode" (constant & filter function) – extra notice in Admin Bar
6. Support for WordPress own "Script Debug" constant - extra notice in Admin Bar
7. Plugin supports Multisite behavior (and settings) of Code Snippets plugin itself and adapts the Toolbar links by context, if needed
8. Third-party plugin support/integration (currently: _Multisite Toolbar Additions_ by myself 😉 / _DevKitPro_ by DPlugins / _System Dashboard_ by Bowo / _Variable Inspector_ by Bowo / _Debug Log Manager_ by Bowo)
9. Plugin installation mode:
	- a) As regular plugin (also supports Multisite network-wide activation)
	- b) As code snippet - directly in _Code Snippets_ itself! 👏
10. Custom tweaks via constants: enable or disable various additional features or tweaks – just as simple code snippets, see below --- this keeps the plugin/snippet simple and lightweight (you can check the config in your WP install via: _Tools > Site Health > Info_ – there look for the row: _Snippets QuickNav (Plugin)_)
11. Show the Admin Bar also in Block Editor full screen mode.

---

## Frequently Asked Questions 

### How can I change / tweak things?
Please see here under [**Custom Tweaks via Constants**](#custom-tweaks-via-constants) what is possible!

### Why is this functionality not baked into Code Snippets itself?
I don't know. Not everything needs to be built-in. That's what plugins are for: those who _need_ this functionality can install and use them. Or better, [just use it as Code Snippet](#installation) in _Code Snippet_ itself. Done :-)

### Why did you create this plugin?
Because I needed (and wanted!) it myself for the sites I maintain. [Read the backstory here ...](#plugins-backstory)

### Why is this plugin not on wordpress.org plugin repository?
Because the restrictions there for plugin authors are becoming more and more. It would be possible, yes, but I don't want that anymore. The same for limited support forums for plugin authors on .org. I have decided to leave this whole thing behind me.

---

## Custom Tweaks via Constants 

### Default capability (aka permission)
The intended usage of this plugin is for Administrator users only. Therefore the default capability to see the new Admin Bar node is set to `activate_plugins`. You can change this via the constant `SNQN_VIEW_CAPABILITY` – define that via `wp-config.php` or via Code Snippet plugin:
```
define( 'SNQN_VIEW_CAPABILITY', 'activate_plugins' );
```

### Restrict to defined user IDs only (since v1.2.0)
You can define an array of user IDs (can also be only _one_ ID) and that way restrict showing the Snippets Admin Bar item only for those users. Define that via `wp-config.php` or via Code Snippet plugin:
```
define( 'SNQN_ENABLED_USERS', [ 1, 500, 867 ] );
```
This would enable only for the users with the IDs 1, 500 and 867. Note the square brackets around, and no single quotes, just the ID numbers.

For example you are one of many admin users (role `administrator`) but _only you_ want to show it _for yourself_. Given you have user ID 1:
```
define( 'SNQN_ENABLED_USERS', [ 1 ] );
```
That way only you can see it, the other admins can't!

### Name of main menu item
The default is just "Snippets" – catchy and short. However, if you don't enjoy "Snippets" you can tweak that also via the constant `SNQN_NAME_IN_ADMINBAR` – define that also via `wp-config.php` or via Code Snippet plugin:
```
define( 'SNQN_NAME_IN_ADMINBAR', 'Codes' );
```

### Snippets count – addition to main menu item:
![With Counter -- Snippets QuickNav plugin](https://raw.githubusercontent.com/deckerweb/snippets-quicknav/master/assets-github/with-counter.png)
```
define( 'SNQN_COUNTER', 'yes' );
```

### Default icon of main menu item 
![Icon Alternatives -- Snippets QuickNav plugin](https://raw.githubusercontent.com/deckerweb/snippets-quicknav/master/assets-github/icon-alternatives.png)
The "snip" icon – aka the scissor logo icon – is awesome and really historic. However, you can use two other alternatives: 1) The Code Snippets company logo (a bit red-ish / blue-ish) or 2) a more neutral "code" logo from Remix Icon (free and open source licensed!). You can also tweak that via a constant in `wp-config.php` or via Code Snippets plugin:
```
define( 'SNQN_ICON', 'red_blue' );  // Code Snippets company logo
```
```
define( 'SNQN_ICON', 'remix' );  // code icon by Remix Icon
```

### Disable code snippets library items
Removes the "Find Snippets" section
```
define( 'SNQN_DISABLE_LIBRARY', 'yes' );
```

### Disable footer items (Links & About)
Removes the "Links" & "About" sections
```
define( 'SNQN_DISABLE_FOOTER', 'yes' );
```

### **Enable** "Expert Mode"
Nothing really fancy, just some additional links for coders:
- _Site Health Info_ (WP Core)
- Plugin: _DevKit Pro_ by DPlugins
- Plugin: _System Dashboard_ by Bowo
- Plugin: _Variable Inspector_ by Bowo
- Plugin: _Debug Log Manager_ by Bowo
```
define( 'SNQN_EXPERT_MODE', TRUE );
```
Note: Support for _some_ additional stuff in that mode may come in future.

### **Fair Play:** Enable "free CS free" – to remove some promo stuff ...
If you want the Pro promotions go away, use the following snippet ... or just purchase the Pro version to support the developers. Thank you in advance! (And thanks to the Code Snippets team for making this awesome tool for us site builders and developers!)
```
define( 'SNQN_FREE_CS_FREE', 'yes' );
```
- This removes the pro content types in the free version
- Removes the upgrade button, and upgrade submenu (in left-hand Admin menu)
- Removes Banner / survey on Code Snippet admin pages

---

## Changelog 

**The Releases**

### 🎉 v1.2.0 – 2025-04-05
* New: Optionally only enable for defined user IDs _(new custom tweak)_
* New: Installable and updateable via [Git Updater plugin](https://git-updater.com/)
* Improved: Admin Bar CSS for Block / Site Editor fullscreen mode
* Fix: PHP warning on frontend
* Fix: Minor styling issues for top-level item
* Update: `.pot` file, plus packaged German translations, now including new `l10n.php` files!

### 🎉 v1.1.0 – 2025-03-25
* New: Show Admin Bar also in Block Editor full screen mode
* New: Support for _DevKit Pro_ plugin by DPlugins
* New: Support for _System Dashboard_ plugin by Bowo
* New: Add info to Site Health Debug, useful for our constants for custom tweaking
* Improved: Disable promo stuff only for free version (not globally)
* Improved: Additional checks if _Code Snippets_ plugin is not active
* Plugin: Add meta links on WP Plugins page
* Update: `.pot` file, plus packaged German translations

### 🎉 v1.0.0 – 2025-03-21
* Initial release
* Includes support for _Code Snippets_ **free** AND **Pro** version
* Includes support for WordPress Multisite installs and respects Code Snippets free behavior (and settings) in there
* Includes `.pot` file, plus packaged German translations

---

## Plugin's Backstory 

_I needed this plugin (Snippets QuickNav) myself so I developed it. Since Code Snippets was first released in 2012 I am using it and loving it. On some sites I have up to 20 or 30 snippets, small stuff mostly, but sometimes bigger also. For a long time, I have wanted a way to get faster to specific snippets to maintain those (for whatever reason). Since I have long history of Admin Bar (Toolbar) plugins I thought that would be another one I could make. In the last few weeks I felt the need to finally code something. So I came up with this little helper plugin / "snippet". And, scratching my own itch is also always something enjoyable. My hope is, that you will enjoy it as well (the finished plugin)._

–– David Decker, plugin developer, in March of 2025

---

## Plugin Scope / Disclaimer 

This plugin comes as is.

_Disclaimer 1:_ So far I will support the plugin for breaking errors to keep it working. Otherwise support will be very limited. Also, it will NEVER be released to WordPress.org Plugin Repository for a lot of reasons (ah, thanks, Matt!).

_Disclaimer 2:_ All of the above might change. I do all this stuff only in my spare time.

_Most of all:_ Blessed (snippet) coding, and have fun building great sites!!! 😉

---

Official "scissor" and _Code Snippets_ plugin/company logo icons: © Code Snippets Pro

Icons used in Admin Bar items: [© Remix Icon](https://remixicon.com/)

Icons used in promo graphics: [© Remix Icon](https://remixicon.com/)

Readme & Plugin Copyright: © 2025, David Decker – DECKERWEB.de