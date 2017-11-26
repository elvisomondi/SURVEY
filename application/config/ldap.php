<?php

$serverId=0;
// Define the server DNS name or IP Address
$ldap_server[$serverId]['server'] = "ldap.mycompany.org";

// Define the TCP port on which the LDAP server is listenning
$ldap_server[$serverId]['port'] = "636";

$ldap_server[$serverId]['protoversion'] = "ldapv2";

// Define the encryption method to use
$ldap_server[$serverId]['encrypt'] = "ldaps";

// Define the referral option
// 'false' is recommended for ActiveDirectory servers
$ldap_server[$serverId]['referrals'] = false;

// Define the encoding used by the Ldap directory
$ldap_server[$serverId]['binddn']	=	"uid=mybinduser,dc=mycompany,dc=org";
$ldap_server[$serverId]['bindpw']	=	"AsecretPassword";


$query_id=0;

// First define the serverId on which you want to run the query
$ldap_queries[$query_id]['ldapServerId'] = 0;

// Give a name that will appear on the user interface
$ldap_queries[$query_id]['name'] = 'Staff with an enabled account';

// Define the ldap base used for user searches
$ldap_queries[$query_id]['userbase'] = 'ou=staff,dc=mycompany,dc=org';

$ldap_queries[$query_id]['userfilter'] = '(&(objectClass=inetOrgPerson)(my-fake-accountstatus-attribute=enabled))';

$ldap_queries[$query_id]['userscope'] = 'sub';

$ldap_queries[$query_id]['firstname_attr'] = 'givenname';

$ldap_queries[$query_id]['lastname_attr'] = 'sn';

$ldap_queries[$query_id]['email_attr'] = 'mail';


$ldap_queries[$query_id]['token_attr'] = ''; // Leave empty for Auto Token generation bu phpsv
$ldap_queries[$query_id]['language'] = '';
$ldap_queries[$query_id]['attr1'] = '';
$ldap_queries[$query_id]['attr2'] = '';

$query_id++;
$ldap_queries[$query_id]['ldapServerId'] = 0;
$ldap_queries[$query_id]['name'] = 'Administrator group';
// Define a group filter (base, filter, scope)
// Note that in AD, user groups are defined in the foloowing base:
// CN=Users,DC=WindowsDomainName,DC=mycompany,DC=org
$ldap_queries[$query_id]['groupbase'] = 'ou=groups,dc=mycompany,dc=org';
$ldap_queries[$query_id]['groupfilter'] = '(&(objectClass=groupOfNames)(cn=AdministratorGroup))';
$ldap_queries[$query_id]['groupscope'] = 'sub';
// Define which group's attribute is used to get users' Ids
$ldap_queries[$query_id]['groupmemberattr'] = 'member';
// Define if the groupmemberattr contains users's DNs or NOT
$ldap_queries[$query_id]['groupmemberisdn'] = true;

$ldap_queries[$query_id]['userbase'] = 'ou=users,dc=mycompany,dc=org';
$ldap_queries[$query_id]['userfilter'] = '(my-fake-accountstatus-attribute=enabled)';
$ldap_queries[$query_id]['userscope'] = 'sub';

$ldap_queries[$query_id]['firstname_attr'] = 'givenname';
$ldap_queries[$query_id]['lastname_attr'] = 'sn';
$ldap_queries[$query_id]['email_attr'] = 'mail';
$ldap_queries[$query_id]['token_attr'] = ''; // Leave empty for Auto Token generation bu phpsv
$ldap_queries[$query_id]['language'] = '';
$ldap_queries[$query_id]['attr1'] = '';
$ldap_queries[$query_id]['attr2'] = '';


// This query is an example of a group search in which group members are UIDs
// an additionnal user filter is applied to a already found users
$query_id++;
$ldap_queries[$query_id]['ldapServerId'] = 0;
$ldap_queries[$query_id]['name'] = 'Admins via POSIXGroups';
$ldap_queries[$query_id]['groupbase'] = 'ou=group,dc=mycompany,dc=org';
$ldap_queries[$query_id]['groupfilter'] = '(&(cn=admins)(objectclass=posixgroup))';
$ldap_queries[$query_id]['groupscope'] = 'sub';
// Define which attribute within the group entry contains users' IDs
$ldap_queries[$query_id]['groupmemberattr'] = 'memberuid';
// Declare that groupmemberattr contains users' IDs and not DNs
$ldap_queries[$query_id]['groupmemberisdn'] = FALSE;
// Give the name of the attribute in the user entry that matches the
// 'groupmemberattr' value
$ldap_queries[$query_id]['useridattr'] = 'uid';
// Give the base DN used to search the users based on the users' IDs
$ldap_queries[$query_id]['userbase'] = 'ou=people,dc=mycompany,dc=org';
// Optionnally give an additionnal filter to filter users
$ldap_queries[$query_id]['userfilter'] = '(objectclass=*)';
$ldap_queries[$query_id]['userscope'] = 'sub';

$ldap_queries[$query_id]['firstname_attr'] = 'givenname';
$ldap_queries[$query_id]['lastname_attr'] = 'sn';
$ldap_queries[$query_id]['email_attr'] = 'mail';
$ldap_queries[$query_id]['token_attr'] = ''; // Leave empty for Auto Token generation bu phpsv
$ldap_queries[$query_id]['language'] = '';
$ldap_queries[$query_id]['attr1'] = '';
$ldap_queries[$query_id]['attr2'] = '';


return array('ldap_server' => $ldap_server, 'ldap_queries' => $ldap_queries);
?>
