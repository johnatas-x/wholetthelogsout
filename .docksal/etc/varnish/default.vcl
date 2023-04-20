vcl 4.1;

import std;
import directors;

backend default {
    .host = "web";
    .port = "80";
    .probe = {
    # When on Maintenance mode backend is considered healthy
    .url = "/robots.txt";
        .timeout = 1s;
        .interval = 5s;
        .window = 5;
        .threshold = 3;
    }
}

# Called at the beginning of a request
# https://varnish-cache.org/docs/6.0/users-guide/vcl-built-in-subs.html#vcl-recv
sub vcl_recv {

    # If the backend is healthy set grace to 120s instead of 30 days, @see default value in vcl_backend_response
    if (std.healthy(req.backend_hint)) {
        set req.grace = 120s;
    }

    # -- Custom configuration --
    # httpchk haproxy
    if (req.url == "/varnishcheck") {
        return(synth(751, "OK!"));
    }

    set req.http.X-Forwarded-For = regsub(req.http.X-Forwarded-For, "[, ].*$", "");
    unset req.http.Authorization;

    # -- Default configuration --
    # Announce support for Edge Side Includes by setting the Surrogate-Capability header
    set req.http.Surrogate-Capability = "Varnish=ESI/1.0";

    # Remove empty query string parameters
    # e.g.: www.example.com/index.html?
    if (req.url ~ "\?$") {
        set req.url = regsub(req.url, "\?$", "");
    }

    # Remove port number from host header
    set req.http.Host = regsub(req.http.Host, ":[0-9]+", "");

    # Sorts query string parameters alphabetically for cache normalization purposes.
    set req.url = std.querysort(req.url);

    # Remove the proxy header to mitigate the httpoxy vulnerability
    # See https://httpoxy.org/
    unset req.http.proxy;

    # Add X-Forwarded-Proto header when using https
    if (!req.http.X-Forwarded-Proto && (std.port(server.ip) == 443)) {
        set req.http.X-Forwarded-Proto = "https";
    }

    # To enable URIBAN functionality for images, enable the varnish_image_purge module.
    if (req.method == "URIBAN") {
        ban("req.url == " + req.url);
        # Throw a synthetic page so the request won't go to the backend.
        return (synth(200, "Ban added."));
    }

    # Ban logic to remove multiple objects from the cache at once. Tailored to Drupal's cache invalidation mechanism
    if (req.method == "BAN") {
        # Check against the ACLs.
        #if(!std.ip(req.http.X-Forwarded-For,"0.0.0.0") ~ purge) {
        #    return(synth(405, "BAN not allowed for this IP address"));
        #}

        # Logic for the ban, using the Cache-Tags header. For more info
        # see https://github.com/geerlingguy/drupal-vm/issues/397.
        # Note the above issue shows a comma-delimited list for tags, when they
        # must be pipe-separated.
        if (req.http.Cache-Tags) {
          # Escape any pipes in the original header, as a pipe is a valid character
          # for a cache tag.
          set req.http.Cache-Tags = regsuball(req.http.Cache-Tags, "\|", "\\|");

          # Switch spaces to a regular expresson "or".
          set req.http.Cache-Tags = regsuball(req.http.Cache-Tags, " ", "\|");
        }

        # Throw a synthetic page so the request won't go to the backend.
        return (synth(200, "Ban added."));
    }

    # Purge logic to remove objects from the cache (Drupal purge module prefers BAN)
    if (req.method == "PURGE") {
        #if(!std.ip(req.http.X-Forwarded-For,"0.0.0.0") ~ purge) {
        #    return(synth(405,"PURGE not allowed for this IP address"));
        #}
        return(purge);
    }

    # Only handle relevant HTTP request methods
    if (
        req.method != "GET" &&
        req.method != "URIBAN" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "PATCH" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "DELETE"
    ) {
        return (pipe);
    }

    # Remove tracking query string parameters used by analytics tools
    if (req.url ~ "(\?|&)(utm_source|utm_medium|utm_campaign|utm_content|gclid|cx|ie|cof|siteurl)=") {
        set req.url = regsuball(req.url, "&(utm_source|utm_medium|utm_campaign|utm_content|gclid|cx|ie|cof|siteurl)=([A-z0-9_\-\.%25]+)", "");
        set req.url = regsuball(req.url, "\?(utm_source|utm_medium|utm_campaign|utm_content|gclid|cx|ie|cof|siteurl)=([A-z0-9_\-\.%25]+)", "?");
        set req.url = regsub(req.url, "\?&", "?");
        set req.url = regsub(req.url, "\?$", "");
    }

    # Only cache GET and HEAD requests
    if ((req.method != "GET" && req.method != "HEAD") || req.http.Authorization) {
        return (pass);
    }

    # Mark static files with the X-Static-File header, and remove any cookies
    # X-Static-File is also used in vcl_backend_response to identify static files
    # Excluding private files
    if (req.url ~ "^[^?]*\.(7z|avi|bmp|bz2|css|csv|doc|docx|eot|flac|flv|gif|gz|ico|jpeg|jpg|js|less|mka|mkv|mov|mp3|mp4|mpeg|mpg|odt|ogg|ogm|opus|otf|pdf|png|ppt|pptx|rar|rtf|svg|svgz|swf|tar|tbz|tgz|ttf|txt|txz|wav|webm|webp|woff|woff2|xls|xlsx|xml|xz|zip)(\?.*)?$" && req.url !~ "/system/files") {
        set req.http.X-Static-File = "true";
        unset req.http.Cookie;
        return(hash);
    }

	# Don't cache the following pages
    if (req.url ~ "^/status.php$" ||
        req.url ~ "^/update.php$" ||
        req.url ~ "^/cron.php$" ||
        req.url ~ "^/admin$" ||
        req.url ~ "^/admin/.*$" ||
        req.url ~ "^/flag/.*$" ||
        req.url ~ "^.*/ajax/.*$" ||
        req.url ~ "^.*/ahah/.*$") {
        return (pass);
    }

	# Remove all cookies except the session & NO_CACHE cookies
    if (req.http.Cookie) {
        set req.http.Cookie = ";" + req.http.Cookie;
        set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
        set req.http.Cookie = regsuball(req.http.Cookie, ";(S?SESS[a-z0-9]+|NO_CACHE)=", "; \1=");
        set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
        set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

        if (req.http.cookie ~ "^\s*$") {
            unset req.http.cookie;
        } else {
            return(pass);
        }
    }
    return (hash);
}

sub vcl_hash {
    # Create cache variations depending on the request protocol
    hash_data(req.http.X-Forwarded-Proto);
}

sub vcl_backend_response {
    # Inject URL & Host header into the object for asynchronous banning purposes
    set beresp.http.x-url = bereq.url;
    set beresp.http.x-host = bereq.http.host;

    # Serve stale content for 30 days after object expiration for safety reason.
    # Perform asynchronous revalidation while stale content is served
    # BUT this will be override to 120s if the backend is healthy, @see vcl_recv
    set beresp.grace = 30d;

    # Stop cache when a backend fetch returns an 5xx error
    if (beresp.status >= 500 && bereq.is_bgfetch) {
        return (abandon);
    }

    # Cache 404 responses for 2 mn.
    if ( beresp.status == 404 ) {
        set beresp.ttl = 120s;
        return (deliver);
    }

    # Don't cache 400+ responses for more than 10s
    if ( beresp.status >= 400 ) {
        set beresp.ttl = 10s;
    }

    # If we dont get a Cache-Control header from healthy backend responses.
    # we default to 1h cache for all objects
    if (!beresp.http.Cache-Control && beresp.status >= 200 && beresp.status < 400) {
        set beresp.ttl = 1h;
    }

    # Enforce default max-age to 1 hour whatever is set in Drupal (/admin/config/development/performance)
    if (beresp.http.Cache-Control ~ "max-age=") {
        unset beresp.http.Cache-Control;
        unset beresp.http.expires;
        # Cache-control send to the browser.
        set beresp.http.Cache-Control = "public, max-age=3600";
    }

    # If the file is marked as static
    # Enforce Browser cache control policy to 1 year whatever is set in Drupal (/admin/config/development/performance)
    if (bereq.http.X-Static-File == "true") {
        unset beresp.http.Set-Cookie;
        set beresp.ttl = 1y;
        set beresp.http.Cache-Control = "public, max-age=315360000";
    }

    # Parse Edge Side Include tags when the Surrogate-Control header contains ESI/1.0
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }
    return (deliver);
}

sub vcl_deliver {
    # -- Custom configuration --
    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
        set resp.http.X-Cache-TTL-Remaining = obj.ttl;
        #set resp.http.X-Cache-grace = obj.grace;
    } else {
        set resp.http.X-Cache = "MISS";
    }

    set resp.http.X-Cache-Hits = obj.hits;
    set resp.http.X-Served-By = server.hostname;
    set resp.http.X-Varnish-Ip   = server.ip;
    set resp.http.X-Varnish-Port = std.port(server.ip);

    unset resp.http.X-Powered-By;
    unset resp.http.Server;
    unset resp.http.X-Varnish;
    unset resp.http.Via;
    unset resp.http.Link;
    unset resp.http.x-url;
    unset resp.http.x-host;
    unset resp.http.X-Static-File;
    unset resp.http.X-Generator;
    unset resp.http.purge-cache-tags;
    unset resp.http.Cache-Tags;
    unset resp.http.x-drupal-dynamic-cache;

    return (deliver);
}

sub vcl_synth {
    set resp.http.Content-Type = "text/html; charset=utf-8";
    set resp.http.Retry-After = "5";

    if (resp.status == 751) {
        set resp.status = 200;
    }

    synthetic( {"<!DOCTYPE html>
        <html>
          <head>
            <title>"} + resp.status + " " + resp.reason + {"</title>
          </head>
          <body>
            <h1>"} + resp.status + " " + resp.reason + {"</h1>
            <p>"} + resp.reason + {"</p>
            <h3>Guru Meditation:</h3>
            <p>XID: "} + req.xid + {"</p>
            <hr>
            <p>Varnish cache server</p>
          </body>
        </html>
    "});

    return (deliver);
}

#sub sec_handler {
#    set req.http.X-AWH-WAF-Response = "We don't like your kind around here";
#    return (synth(801, "Rejected"));
#}
