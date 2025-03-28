# Custom Tweaks (via Constants):

## Default capability (aka permission)
The intended usage of this plugin is for Administrator users only. Therefore the default capability to see the new Admin Bar node is set to `activate_plugins`. You can change this via the constant `SNQN_VIEW_CAPABILITY` – define that via `wp-config.php` or via Code Snippet plugin:
```
define( 'SNQN_VIEW_CAPABILITY', 'activate_plugins' );
```

## Restrict to defined user IDs only (since v1.2.0)
You can define an array of user IDs (can also be only _one_ ID) and that way restrict showing the Snippets Admin Bar item only for those users. Define that via `wp-config.php` or via Code Snippet plugin:
```
define( 'SNQN_ENABLED_USERS', [ 1, 500, 867 ] );
```
This would enable only for the users with the IDs 1, 500 and 867. Note the square brackets, and no single quotes, just the ID numbers.

For example you are one of many admin users (role `administrator`) but _only you_ want to show it _for yourself_. Given you have user ID 1:
```
define( 'SNQN_ENABLED_USERS', [ 1 ] );
```
That way only you can see it, the other admins can't!

## Name of main menu item
The default is just "Snippets" – catchy and short. However, if you don't enjoy "Snippets" you can tweak that also via the constant `SNQN_NAME_IN_ADMINBAR` – define that also via `wp-config.php` or via Code Snippet plugin:
```
define( 'SNQN_NAME_IN_ADMINBAR', 'Codes' );
```

## Snippets count – addition to main menu item:
![With Counter -- Snippets QuickNav plugin](https://raw.githubusercontent.com/deckerweb/snippets-quicknav/master/assets-github/with-counter.png)
```
define( 'SNQN_COUNTER', 'yes' );
```

## Default icon of main menu item 
![Icon Alternatives -- Snippets QuickNav plugin](https://raw.githubusercontent.com/deckerweb/snippets-quicknav/master/assets-github/icon-alternatives.png)
The "snip" icon – aka the scissor logo icon – is awesome and really historic. However, you can use two other alternatives: 1) The Code Snippets company logo (a bit red-ish / blue-ish) or 2) a more neutral "code" logo from Remix Icon (free and open source licensed!). You can also tweak that via a constant in `wp-config.php` or via Code Snippets plugin:
```
define( 'SNQN_ICON', 'red_blue' );  // Code Snippets company logo
```
```
define( 'SNQN_ICON', 'remix' );  // code icon by Remix Icon
```

## Disable code snippets library items
Removes the "Find Snippets" section
```
define( 'SNQN_DISABLE_LIBRARY', 'yes' );
```

## Disable footer items (Links & About)
Removes the "Links" & "About" sections
```
define( 'SNQN_DISABLE_FOOTER', 'yes' );
```

## **Enable** "Expert Mode"
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

## **Fair Play:** Enable "free CS free" – to remove some promo stuff ...
If you want the Pro promotions go away, use the following snippet ... or just purchase the Pro version to support the developers. Thank you in advance! (And thanks to the Code Snippets team for making this awesome tool for us site builders and developers!)
```
define( 'SNQN_FREE_CS_FREE', 'yes' );
```
- This removes the pro content types in the free version
- Removes the upgrade button, and upgrade submenu (in left-hand Admin menu)
- Removes Banner / survey on Code Snippet admin pages