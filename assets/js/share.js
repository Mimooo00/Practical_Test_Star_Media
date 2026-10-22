(function () {
    'use strict';

    var config = window.SHARE_TRACKER || {};
    var lastPlatform = '';
    var lastTrackedAt = 0;

    function sendTrack(platform, page) {
        var now = Date.now();

        if (platform === lastPlatform && now - lastTrackedAt < 800) {
            return;
        }

        lastPlatform = platform;
        lastTrackedAt = now;

        if (!config.trackUrl || !page || !page.url) {
            return;
        }

        var payload = JSON.stringify({
            platform: platform,
            url: page.url,
            title: page.title || document.title
        });
        var body = new Blob([payload], { type: 'application/json' });

        try {
            if (navigator.sendBeacon && navigator.sendBeacon(config.trackUrl, body)) {
                return;
            }
        } catch (error) {
            // Fall through to fetch.
        }

        if (window.fetch) {
            window.fetch(config.trackUrl, {
                method: 'POST',
                body: body,
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                keepalive: true
            }).catch(function () {
                // Tracking must never block sharing.
            });
        }
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('[data-share-platform]');

        if (!link) {
            return;
        }

        var platform = link.getAttribute('data-share-platform') || '';
        var mode = link.getAttribute('data-share-mode') || 'popup';
        var href = link.getAttribute('href') || '';

        if (!platform || !href) {
            return;
        }

        event.preventDefault();

        if (mode === 'email') {
            sendTrack(platform, config.page);
            window.location.href = href;
            return;
        }

        var width = Number(config.popupWidth) || 600;
        var height = Number(config.popupHeight) || 620;
        var left = Math.max(0, Math.round((window.screen.width - width) / 2));
        var top = Math.max(0, Math.round((window.screen.height - height) / 2));
        var features = 'width=' + width
            + ',height=' + height
            + ',left=' + left
            + ',top=' + top
            + ',menubar=no,toolbar=no,location=yes,status=no,resizable=yes,scrollbars=yes';

        var popup = window.open(href, 'star-share-' + platform, features);

        if (popup) {
            try {
                popup.opener = null;
            } catch (error) {
                // Some browsers restrict opener access; sharing still works.
            }
        }

        sendTrack(platform, config.page);

        if (!popup) {
            window.location.href = href;
        }
    });
})();
