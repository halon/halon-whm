#!/usr/bin/perl

use lib '/usr/local/cpanel';
use Cpanel::DataStore;

my $app = {
	url => '/webmail/paper_lantern/sp-enduser-cpanel/index.live.php',
	displayname => 'Halon Anti-spam',
	icon => '/webmail/paper_lantern/sp-enduser-cpanel/HalonWebmailIcon.png',
};

print "Installing to webmail\n";

Cpanel::DataStore::store_ref('/var/cpanel/webmail/webmail_sp-enduser-cpanel.yaml', $app) || die("Could not write webmail registration file");

print "Plugin installed ok\n";
