=== MC Web Notes ===
Contributors: miguelcalderon
Tags: 
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 4.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Allows logged in users to annotate site pages and posts.

= Docs & Support =
Documentation not ready yet, focused on adding features for now.

= Features: =

* Allows logged in users to annotate site pages and posts.

== Changelog ==

= 0.3.3 =
* Switched back to ReactJS.

= 0.3.1 =
* Refactored, added tests and switched to InfernoJS UI.

= 0.2.2 =
* Added internationalization, only English for now.
* Deleting a note will also delete its context thumbnail now.
* Added metaboxes with current edited post / page web notes.

== Roadmap ==

= Features =
* Admin panel.
* Theming & branding.
* User roles: reviewer.
* Real time chat, calls and videocalls via PubNub integration.
* Integrations: Slack, Bitbucket, Github.
* Notifications: email.
* Note content: voice recording, video recording, drawings, snapshots.
* Realtime notification of user editing page or post (via Heartbeat?).

= Technical =
* More tests.
* Switch to WP API instead of admin-ajax.php (or support both).
* Add autoprefixer.
* Use contentEditable (maybe with lib) instead of textarea for notes text.
* Switch thunk for saga.