/* global GplCart */
var GplCart = GplCart || {settings: {}, translations: {}};

(function ($) {

    $(function () {
        $('body').addClass('js');
    });

    /**
     * Calls attached methods
     * @param {Object} object
     * @returns {undefined}
     */
    GplCart.attach = function (object) {
        if ('attach' in object) {
            $.each(object.attach, function () {
                this.call();
            });
        }
    };

    /**
     * Translates a string
     * @param {type} text
     * @param {type} options
     * @returns {type}
     */
    GplCart.text = function (text, options) {
        options = options || {};

        if (options) {
            text = GplCart.formatString(text, options);
        }

        return text;
    };

    /**
     * Format strings using placeholders
     * @param {type} str
     * @param {type} args
     * @returns {unresolved}
     */
    GplCart.formatString = function (str, args) {

        for (var key in args) {
            switch (key.charAt(0)) {
                case '@':
                    args[key] = GplCart.escape(args[key]);
                    break;
                case '!':
                    break;
                case '%':
                default:
                    args[key] = '<i class="placeholder">' + args[key] + '</i>';
                    break;
            }

            str = str.replace(key, args[key]);
        }

        return str;
    };

    /**
     * Escapes a string
     * @param {type} str
     * @returns {String}
     */
    GplCart.escape = function (str) {

        var character, regex,
                replace = {'&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;'};

        str = String(str);

        for (character in replace) {
            if (replace.hasOwnProperty(character)) {
                regex = new RegExp(character, 'g');
                str = str.replace(regex, replace[character]);
            }
        }

        return str;
    };

    /**
     * Redirects user to logout after its session has expired
     * @param {type} interval
     * @returns {undefined}
     */
    GplCart.logout = function (interval) {
        setInterval(function () {
            window.location.replace(GplCart.settings.base + 'logout');
        }, interval);
    };

    /**
     * Loads Google Maps
     * @param {type} lat
     * @param {type} lng
     * @returns {undefined}
     */
    GplCart.gmap = function (lat, lng) {
        $.getScript('https://www.google.com/jsapi', function () {
            google.load('maps', '3', {callback: function () {
                if (lng === false) {
                        geocoder = new google.maps.Geocoder();
                        geocoder.geocode({'address': lat}, function (results, status) {
                            if (status === google.maps.GeocoderStatus.OK) {

                                var lat = results[0].geometry.location.lat();
                                var lng = results[0].geometry.location.lng();
                                var options = {zoom: 10, center: {lat: lat, lng: lng}};

                                var map = new google.maps.Map(document.getElementById('map-container'), options);
                                new google.maps.Marker({position: {lat: lat, lng: lng}, map: map});

                            } else {
                                console.log("Geocode was not successful for the following reason: " + status);
                            }
                        });
                    } else {
                        var options = {zoom: 10, center: {lat: lat, lng: lng}};
                        var map = new google.maps.Map(document.getElementById('map-container'), options);
                        new google.maps.Marker({position: {lat: lat, lng: lng}, map: map});
                    }
                }});
        });
    };

    /**
     * Processes AJAX requests for a job widget
     * @param {type} settings
     * @returns {undefined}
     */
    GplCart.job = function (settings) {

        var job = settings || GplCart.settings.job;
        var selector = GplCart.settings.job.selector || '#job-widget-' + GplCart.settings.job.id;
        var widget = $(selector);

        $.ajax({
            url: job.url,
            data: {process_job: job.id},
            dataType: 'json',
            success: function (data) {

                if (typeof data !== 'object' || $.isEmptyObject(data)) {
                    console.log(arguments);
                    return false;
                }

                if ('redirect' in data && data.redirect) {
                    window.location.replace(data.redirect);
                }

                if ('finish' in data && data.finish) {
                    widget.find('.progress-bar').css('width', '100%')
                    widget.hide();
                    return false;
                }

                if ('progress' in data) {
                    widget.find('.progress-bar').css('width', data.progress + '%');
                }

                if ('message' in data) {
                    widget.find('.message').html(data.message);
                }

                GplCart.job(settings);

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(arguments);
            }
        });
    };

    /**
     * Generates a random string
     * @param {type} portion
     * @returns {String}
     */
    GplCart.randomString = function (portion) {
        portion = portion || -8;
        return Math.random().toString(36).slice(portion);
    };

})(jQuery);