package Cpanel::API::Halon;
 
use strict;
 
our $VERSION = '1.0';
 
# Your comments about this custom module.
 
# Cpanel Dependencies
use Cpanel                   ();
use Cpanel::API              ();
use Cpanel::Locale           ();
use Cpanel::Logger           ();

# Other dependencies go here.
# Defaults go here.
# Constants go here.
 
# Globals
my $logger;
my $locale;
 
# Caches go here.
 
# Functions go here.
 

sub getConfiguration {
    my ( $args, $result ) = @_;

    my $out = Cpanel::Wrap::send_cpwrapd_request(
        'namespace' => 'Halon',
        'module'    => 'getadmin',
        'function'  => 'getConfiguration'
    );

    if ($out->{'status'} == 1) {
        $result->data($out->{'data'});
        return 1;
    } else {
        $result->data('');
        my $str = $out->{'statusmsg'};

        $str =~ s/[^a-zA-Z0-9\s]*//g;

        $result->error($str);
        return 0;
    }
}

sub getAccessHash {
    my ( $args, $result ) = @_;

    my $out = Cpanel::Wrap::send_cpwrapd_request(
        'namespace' => 'Halon',
        'module'    => 'getadmin',
        'function'  => 'getAccessHash'
    );

    if ($out->{'status'} == 1) {
        $result->data($out->{'data'});
        return 1;
    } else {
        $result->data('');
        my $str = $out->{'statusmsg'};

        $str =~ s/[^a-zA-Z0-9\s]*//g;

        $result->error($str);
        return 0;
    }
}

1;