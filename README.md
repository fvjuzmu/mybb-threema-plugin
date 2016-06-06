# MyBB Notifications via Threema
With this plugin you can enable MyBB to send Notifications about new Posts to your users Threema-ID.

## Requirements
- MyBB v1.8.*
- Threema Gateway account with Custom Threema-ID (Basic) (https://gateway.threema.ch) [this will not work with the End-To-End Option)
- Threema Gateway Credits
- Optional: libsodium PHP extension (https://gateway.threema.ch/de/developer/sdk-php) 

## Installation
- Download the zip of the [latest stable version](https://github.com/fvjuzmu/mybb-threema-plugin/releases/latest)
- Unzip the folder *inc* from this zip into your forum root folder
- Give the forum the permission to write into the *inc/plugins/vendor/threema_keystore.txt* file. Thats the same permission as the upload folder in your forum root folder.
- Install and activate the plugin in the admin panel of your forum
- Add a *Custom Profile Field* and name it Something like "Threema-ID"

## Setup
- Now you can find *Threema settings* in the admin panel under *Configuration->Settings*
- There you have to enter at least your custom Threema-ID, your secret key (which you both got from the Threema Gateway page) and the ID of the *Custom Profile Field*
- Tell your users to enter there Threema-ID into there Profile

Thats all. Every user who has entered his Threema-ID into his Profile will now receive notifications about new posts in his Threema app.

## NOTICE !
**The plugin ignores ALL user permissions!!! It will only filter out blocked users at the moment.**
Until then you can blacklist some forums by entering ther ID into the blacklist field in the plugin settings.

## FAQ
**Q: Where can I find the ID of the *Custom Profile Field*?**

A: Thats a bit tricky, check out this screenshot [find_custom_field_id.png](https://github.com/fvjuzmu/mybb-threema-plugin/blob/master/find_custom_field_id.png)

**Q: Why is the plugin sending all messages in german language**

A: Because the author is from germany and at the moment this is the only supported language. Sorry. However you can simply customize it by your selfe. Just edit these two lines: [56](https://github.com/fvjuzmu/mybb-threema-plugin/blob/master/inc/plugins/threema.php#L58), [82](https://github.com/fvjuzmu/mybb-threema-plugin/blob/master/inc/plugins/threema.php#L84)

## License information
This plugin ships with:
- [rugk/Salt](https://github.com/rugk/Salt/tree/85a379a750ff9b513f92ee104dab68b00418aaa8)
- [rugk/threema-msgapi-sdk-php](https://github.com/rugk/threema-msgapi-sdk-php/tree/v1.1.7)
