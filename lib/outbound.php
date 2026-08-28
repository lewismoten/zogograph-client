<?php
declare(strict_types=1);

/* Small, dependency-free checks shared by every configurable outbound HTTP
 * client. A service URL is input, even when it came from an administrator. */

function outboundPublicAddress(string $address): bool
{
    return filter_var($address, FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

/** @return list<string> every current address for a host, or an empty list */
function outboundHostAddresses(string $host): array
{
    $host = trim($host, '[]');
    if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
        return [$host];
    }
    $records = function_exists('dns_get_record') ? array_merge(
        array_column(dns_get_record($host, DNS_A) ?: [], 'ip'),
        array_column(dns_get_record($host, DNS_AAAA) ?: [], 'ipv6')
    ) : (gethostbynamel($host) ?: []);
    return array_values(array_unique(array_filter($records, 'is_string')));
}

function outboundLoopbackHost(string $host): bool
{
    $host = strtolower(trim($host, '[]'));
    return $host === 'localhost' || $host === '::1' || str_starts_with($host, '127.');
}
