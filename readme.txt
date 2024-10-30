=== MB8WP ===
Contributors: btctech
Tags: altcoin, blockchain, btc, bitcoin
Requires at least: 4.9.8
Tested up to: 4.9.8
Requires PHP: 5.6
Stable tag: 1.2
Host your own Alt-Coin wallet services and for PoS coins start your own staking pool.

== Description ==
Start your own Alt-Coin Wallet service and with PoS coins you can start your own staking pool too. Connect your WordPress to your Alt-Coin\'s RPC API quickly and with easy setup.

Admin settings allow you to turn staking pool on/off and choose how much of the staking rewards to share with your staking pool users. Or you can keep any staking rewards to yourself!

Settings allow to auto cash-out any staking rewards to external wallet of your choice or you can give 100% staking share to your pool users.

Turning OFF the staking features will turn the plugin into a simple wallet service where your users can send/receive funds and view recent transactions on the blockchain.

As our first official plugin we will offer full support where we can.

If you have bug reports or suggestions for new features please see our support centre and open a new ticket at http://btctech.co.uk/

== Installation ==
Once activated the plugin goto Admin > MB8 API and run the first time setup with your Alt-Coin\'s RPC Connection details.

Note: You must be familiar with your RPC configuration in your Alt-Coin.conf file.

A page with the SLUG /mb8-wp/ is created as your CRON job page, please add a cron job for every minute to run this page i.e
* * * * * WGET http://yoursite.com/mb8-wp/

== Screenshots ==
1. Main wallet page shortcode [mb8_wallet]
== Changelog ==

= 1.1 =
* Fixed admin refresh link when updating settings.
* Changed password field to hide password.
* Added default admin settings to DB.
* Fixed function that counts sub accounts for stats.

= 1.2 =
* Fixed bitcoin calulations using bc. Important FIX.
== Upgrade Notice ==
 
= 1.2 =
Important fixes to altcoin calculations on staking and sending payouts.
