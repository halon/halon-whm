#!/usr/local/cpanel/3rdparty/bin/perl
 
use Data::Dumper    ();
use Cpanel::Wrap    ();
use Data::Dumper    ();

 BEGIN {
    unshift @INC, '/usr/local/cpanel';
 }

sub file_wrapper {
    my $data = shift;

    my $result = Cpanel::Wrap::send_cpwrapd_request(
        'namespace' => 'Halon',
        'module'    => 'getadmin',
        'function'  => $data,
    ); 
 
    if ( $result->{'error'} ) {
        return "Error code $result->{'exit_code'} returned: $result->{'data'}";
    }
    elsif ( ref( $result->{'data'} ) ) {
        return Data::Dumper::Dumper( $result->{'data'} );
    }
    elsif ( defined( $result->{'data'} ) ) {
        return $result->{'data'};
    }
    return 'cpwrapd request failed: ' . $result->{'statusmsg'};
}
$parameter = $ARGV[0];
if( $#ARGV > 0 ){
    $data = $ARGV[1];
}
my $response = file_wrapper($parameter, $data);

print STDOUT "$response";
