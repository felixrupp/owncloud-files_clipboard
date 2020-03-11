Description
-----------
Minimal clipboard feature for the files application. Allows to copy and move files between directories.

![](https://raw.githubusercontent.com/felixrupp/owncloud-files_clipboard/master/appinfo/screenshot.gif)

Installation via Marketplace
----------------------------
The easiest way installing this app is via the ownCloud [marketplace](https://marketplace.owncloud.com). 
To do so, enter the browser of your ownCloud instance, select the marketplace app, search for the Files Clipboard app and click install. 
Please note, that your app directory must be writable by your instance. Post installing, you have to enable the app in your ownCloud settings page to use it. 

Because this app is signed, no further actions are required. 

Manual Installation
-------------------
Extract the [release](https://github.com/felixrupp/owncloud-files_clipboard/releases/latest) in your `apps` directory and rename it to `files_clipboard`. Post extracting, grant the correct rights and permissions and enable the app either with an occ command or in the settings page in your browser. 

Because this method is installing an unsigned app, you have to add a config parameter to your config.php file of your instance to avoid complains about a missing signature .

```
  'integrity.ignore.missing.app.signature' =>
    [
     0 => 'files_clipboard',
    ],
```

License
-------
[GNU Affero General Public License](http://www.gnu.org/licenses/agpl-3.0.html)


Authors
-------

Current Version, since 1.0.0:
* Felix Rupp - [github.com/felixrupp](https://github.com/felixrupp)

Older Versions:
* leizh - [github.com/leizh](https://github.com/leizh/)
