<?php

$ldap_host = "ldaps://172.16.0.207";
$ldap_port = 636;
$ldap_user = "consulta_ri@invisd.local";
$ldap_pass = 'jTF8er^J$e9DNcmFh@de';
$ldap_conn = ldap_connect($ldap_host, $ldap_port);

if (!$ldap_conn) {
    die("Could not connect to LDAP server");
}

ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);

if (ldap_bind($ldap_conn, $ldap_user, $ldap_pass)) {
    echo "LDAP bind successful.";
} else {
    echo "LDAP bind failed: " . ldap_error($ldap_conn);
}

ldap_close($ldap_conn);

?>
