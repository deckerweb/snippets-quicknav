# Plugins versus Code Snippets – What is Best?

When I see titles of YouTube Videos like „Reduce the number of plugins, use code snippets instead“ I feel bad. While there could be some truth to that expression it could also lead to the same issues as with „some“ plugins: Bad can lead to slowing down your site and security issues – no matter where the code comes from.

I use code snippets in small functionality plugins for over 15 years already, and since the release of the first „Code Snippets“ plugin version 2012 I use it on a lot of sites and I love it! But everything has their place, Plugins AND Snippets. Here I just collected some pros and cons / risks for each. So you can decide for yourself – but with knowledge and wisdom:

## That Always Applies:
* Code has to live somewhere and to be loaded. Period. In general it doesn’t matter if loaded from plugins folder, from the Theme / Child Theme files, from database, or a generated snippet file (living in a subfolder of /uploads/ for example)
* Normally, code has to be maintained and sometimes updated (security! / WP versions! / PHP versions!)
* Bad, unsecure code in a snippet can be very dangerous to find / debug! – the same is true for bad, unsecure code in a plugin or a Theme’s functions.php file!
* Plugins AND Snippets both have their pros and cons, you cannot say which one is „better“ – each have their place! However, it is important to know some of the risks to make a better, informed decision for your individual use case and project.

### Please NOTE:
Some of the pros / advantages list here could be risks or disadvantages easily and also in reverse. It just depends from which angle you are looking at. Sometimes it could be an advantage if the client / admin cannot tweak stuff easily, on another project it could be a disadvantage.

---- 
 
## Pros for Plugins:
* Can receive automatic updates!
* Can load translation files (WordPress language packs) so plugin interface / frontend-relevant strings can be internationalized —\> therefore can be better used with multilingual plugins
* Better handling on Multisite when plugin is network activated
* Can be used as (or made into) an MU Plugin, which stands for must-use (activated automatically and always!)
* can be handled via WP-CLI the „WordPress Terminal command-line“ project
* Conditions are baked-in into the code (by the developer) —\> could be an advantage, as clients have no easy way to change something
* can contain more than one file (PHP)
* can load numerous scripts, styles etc. (in snippet you have to make that individually)
* WordPress has built-in basic error checks which help to not activate plugins with obvious fatal errors
* At least, wordpress.org repository have now improved checklist that has to be fullfilled by every new plugin / uploaded version — in comparison most snippets get never checked, or even updated …
* The plugin code (the code itself, the „files“) does not live in the database; that could be an advantage if database gets hacked
* Users have at least the chance (in most cases) to know who the author / developer is and therefore have at least a chance to get support or give feedback

## Cons / Risks for Plugins:
* A lot of plugins these days are bloated with useless settings – in the long run
* Promotional stuff / upsells is completely abused and often times half (or whole!) of WP-Admin is taken over which is a BAD user experience!
* Still, a lot of plugins load their stuff everywhere, everytime, not all, but still way too many!
* Plugin activation / deactivation often times takes longer as with snippets … at least that is the feeling most users have
* There’s the conception / misconception that „too many plugins“ is bad for site (slowing down etc.). While one hand that COULD be true, of course, you can on the other hand have a hundred (I mean litterally 100) plugins active with no slow down at all: it all comes down to good code. So every plugin needs to be looked at individually.
* Conditions are baked-in into the code (by the developer) —\> could be a disadvantage as loading etc. cannot be changed easily (only with code snippets; or none at all)

---- 

## Pros for Snippets:
* Easier to handle and manage for a lot of users (activate / deactivate; conditions)
* Visual condition managers (in most snippet managers)
* Can have own organizational structure (title, description, tags and more)
* Perfect for smaller stuff (snippets with 1 to 50 lines of code for example)
* Code Snippet managers can be the last resort for Administrators / Site Builders on a client site where they get no access to a Theme’s / Child Theme’s functions.php file or via SFTP to add or „edit“ custom code
* When snippet lives in database: can be transferred easily within a DB backup
* Most Snippet Managers have Safe Mode built-in
* Most Snippet Managers have better error checking when trying to activate code that has syntax errors or semantic / logical errors –\> in my experience this is often way better than default WordPress checks (for plugins)

## Cons / Risks for Snippets:
* Cannot receive automatic updates!
* Cannot have translations – at least not with WordPress language packs
* Handling with Multisite can be very difficult or impossible – NOTE: Only the „Code Snippets“ plugin (even their free version!) has special network-wide snippets which is a great solution for this „issue“
* In edge cases the Code Snippet manager can affect the loading hierarchy of the snippet code (Manager loads first, then starts the executing of the snippets code) – in such cases a Plugin / MU Plugin can be the better option
* Most snippets floating around are badly prefixed, if any at all (that way „polluting“ the global code sphere or be in conflict with other plugins, the Theme / Child Theme or even other snippets)
* Most snippets floating around have no or too few security checks, no sanitizing or escaping – this could be used easily for hackers as the open door …
* Code Snippets are sold as „no-code“ solutions to average users which can be really dangerous!
* Risk is completely on the user: Most users really don’t know what they do when copying, inserting, activating a snippet …
* Most snippets have no known author – therefore users cannot get support or at least give feedback